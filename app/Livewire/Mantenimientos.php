<?php

namespace App\Livewire;

use App\Models\Mantenimiento;
use App\Models\Vehiculo;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;

class Mantenimientos extends Component
{
    // =========================================================================
    // TRAITS
    // =========================================================================
    use WithPagination, WithoutUrlPagination;

    // =========================================================================
    // PROPIEDADES PUBLICAS DE LISTADO
    // =========================================================================
    public $search = '';
    public $estadoFilter = 'todos';
    public $sortBy = 'fecha_programada';
    public $sortDirection = 'desc';
    public $perPage = 10;

    // =========================================================================
    // PROPIEDADES DEL MODAL
    // =========================================================================
    public $modalOpen = false;
    public $editingId = null;

    // =========================================================================
    // FORMULARIO
    // =========================================================================
    public $form = [
        'vehiculo_id' => '',
        'fecha_programada' => '',
        'fecha_realizada' => '',
        'tipo' => 'preventivo',
        'costo' => '',
        'taller' => '',
        'descripcion' => '',
        'kilometraje_mantenimiento' => 0,
        'factura' => '',
    ];

    // =========================================================================
    // ELIMINACIÓN
    // =========================================================================
    public $deletingId = null;
    public $confirmDeleteModal = false;

    /**
     * mount() - Inicializa el estado base del componente
     */
    public function mount()
    {
        $this->resetForm();
    }

    /**
     * resetForm() - Limpia el formulario y sale del modo edición
     */
    public function resetForm()
    {
        $this->form = [
            'vehiculo_id' => '',
            'fecha_programada' => '',
            'fecha_realizada' => '',
            'tipo' => 'preventivo',
            'costo' => '',
            'taller' => '',
            'descripcion' => '',
            'kilometraje_mantenimiento' => 0,
            'factura' => '',
        ];

        $this->editingId = null;
    }

    /**
     * sortBy() - Controla el ordenamiento del listado
     *
     * @param string $field Campo por el cual ordenar
     */
    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    /**
     * create() - Prepara el modal para un nuevo mantenimiento
     */
    public function create()
    {
        $this->resetForm();
        $this->modalOpen = true;
    }

    /**
     * edit() - Carga un mantenimiento en formato apto para inputs date
     *
     * @param int $id ID del mantenimiento a editar
     */
    public function edit($id)
    {
        $mantenimiento = Mantenimiento::findOrFail($id);

        $this->form = [
            'vehiculo_id' => (string) $mantenimiento->vehiculo_id,
            'fecha_programada' => optional($mantenimiento->fecha_programada)->format('Y-m-d'),
            'fecha_realizada' => optional($mantenimiento->fecha_realizada)->format('Y-m-d'),
            'tipo' => $mantenimiento->tipo,
            'costo' => $mantenimiento->costo,
            'taller' => $mantenimiento->taller,
            'descripcion' => $mantenimiento->descripcion,
            'kilometraje_mantenimiento' => $mantenimiento->kilometraje_mantenimiento,
            'factura' => $mantenimiento->factura ?? '',
        ];

        $this->editingId = $id;
        $this->modalOpen = true;
    }

    /**
     * confirmDelete() - Abre el modal de confirmación de eliminación
     *
     * @param int $id ID del mantenimiento seleccionado
     */
    public function confirmDelete($id)
    {
        $this->deletingId = $id;
        $this->confirmDeleteModal = true;
    }

    /**
     * deleteConfirmed() - Elimina el mantenimiento seleccionado
     */
    public function deleteConfirmed()
    {
        $mantenimiento = Mantenimiento::findOrFail($this->deletingId);
        $mantenimiento->delete();

        $this->dispatch('show-custom-toast', type: 'success', message: 'Mantenimiento eliminado correctamente.');

        $this->confirmDeleteModal = false;
        $this->deletingId = null;
    }

    /**
     * save() - Crea o actualiza un mantenimiento
     *
     * Reglas principales:
     * - El vehículo es obligatorio
     * - La fecha realizada no puede ser anterior a la programada
     * - costo y kilometraje deben ser válidos
     */
    public function save()
    {
        $validated = $this->validate([
            'form.vehiculo_id' => 'required|exists:vehiculos,id',
            'form.fecha_programada' => 'required|date',
            'form.fecha_realizada' => 'required|date|after_or_equal:form.fecha_programada',
            'form.tipo' => 'required|in:preventivo,correctivo',
            'form.costo' => 'required|numeric|min:0',
            'form.taller' => 'required|string|max:100',
            'form.descripcion' => 'required|string',
            'form.kilometraje_mantenimiento' => 'required|integer|min:0',
            'form.factura' => 'required|string|max:100',
        ]);

        $data = $validated['form'];

        if ($this->editingId) {
            $mantenimiento = Mantenimiento::findOrFail($this->editingId);
            $mantenimiento->update($data);
            $this->dispatch('show-custom-toast', type: 'success', message: 'Mantenimiento actualizado correctamente.');
        } else {
            $mantenimiento = Mantenimiento::create($data);
            $this->dispatch('show-custom-toast', type: 'success', message: 'Mantenimiento creado correctamente.');
        }

        $this->syncVehiculoKilometraje($mantenimiento);

        $this->modalOpen = false;
        $this->resetForm();
    }

    /**
     * syncVehiculoKilometraje() - Actualiza el kilometraje actual del vehículo
     *
     * Si el mantenimiento registra un kilometraje superior al actual,
     * se actualiza el vehículo para conservar el mayor valor conocido.
     */
    protected function syncVehiculoKilometraje(Mantenimiento $mantenimiento): void
    {
        $vehiculo = Vehiculo::find($mantenimiento->vehiculo_id);

        if ($vehiculo && $mantenimiento->kilometraje_mantenimiento > $vehiculo->kilometraje_actual) {
            $vehiculo->update([
                'kilometraje_actual' => $mantenimiento->kilometraje_mantenimiento,
            ]);
        }
    }

    /**
     * render() - Construye la consulta del listado de mantenimientos
     *
     * La búsqueda considera taller, descripción, factura y datos del vehículo.
     * El filtro por estado se deriva de la existencia de fecha_realizada.
     */
    public function render()
    {
        $mantenimientos = Mantenimiento::query()
            ->with('vehiculo')
            ->when($this->search, function ($query) {
                // Se agrupan los criterios para que el OR no rompa el resto de filtros
                $query->where(function ($subQuery) {
                    $subQuery->where('taller', 'like', '%' . $this->search . '%')
                        ->orWhere('descripcion', 'like', '%' . $this->search . '%')
                        ->orWhere('factura', 'like', '%' . $this->search . '%')
                        ->orWhereHas('vehiculo', function ($vehiculos) {
                            $vehiculos->where('placa', 'like', '%' . $this->search . '%')
                                ->orWhere('marca', 'like', '%' . $this->search . '%')
                                ->orWhere('modelo', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->estadoFilter === 'pendientes', function ($query) {
                // Pendiente: aún no tiene fecha de realización
                $query->whereNull('fecha_realizada');
            })
            ->when($this->estadoFilter === 'realizados', function ($query) {
                // Realizado: ya cuenta con fecha de realización
                $query->whereNotNull('fecha_realizada');
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);

        // También se envía el catálogo de vehículos para el selector del modal
        return view('livewire.mantenimientos', [
            'mantenimientos' => $mantenimientos,
            'vehiculos' => Vehiculo::orderBy('placa')->get(),
        ]);
    }
}
