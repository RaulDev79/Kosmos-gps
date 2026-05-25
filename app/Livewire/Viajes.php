<?php

namespace App\Livewire;

use App\Models\Conductor;
use App\Models\Vehiculo;
use App\Models\Viaje;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;

class Viajes extends Component
{
    // =========================================================================
    // TRAITS
    // =========================================================================
    // WithPagination: habilita paginación del listado
    // WithoutUrlPagination: evita reflejar la página en la URL
    use WithPagination, WithoutUrlPagination;

    // =========================================================================
    // PROPIEDADES PÚBLICAS DE LISTADO
    // =========================================================================
    public $search = '';
    public $estadoFilter = 'todos';
    public $sortBy = 'fecha_hora_inicio';
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
    // Reúne los campos del viaje usados por wire:model
    public $form = [
        'vehiculo_id' => '',
        'conductor_id' => '',
        'fecha_hora_inicio' => '',
        'fecha_hora_fin' => '',
        'kilometraje_inicio' => 0,
        'kilometraje_fin' => '',
        'proposito' => '',
        'estado' => 'planificado',
    ];

    // =========================================================================
    // ELIMINACIÓN
    // =========================================================================
    public $deletingId = null;
    public $confirmDeleteModal = false;

    /**
     * mount() - Inicializa el componente
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
            'conductor_id' => '',
            'fecha_hora_inicio' => '',
            'fecha_hora_fin' => '',
            'kilometraje_inicio' => 0,
            'kilometraje_fin' => '',
            'proposito' => '',
            'estado' => 'planificado',
        ];

        $this->editingId = null;
    }

    /**
     * sortBy() - Administra el ordenamiento dinámico del listado
     *
     * @param string $field Campo seleccionado para ordenar
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
     * create() - Abre el modal en modo creación
     */
    public function create()
    {
        $this->resetForm();
        $this->modalOpen = true;
    }

    /**
     * edit() - Carga un viaje existente en formato compatible con la vista
     *
     * Las fechas se transforman al formato esperado por datetime-local.
     *
     * @param int $id ID del viaje a editar
     */
    public function edit($id)
    {
        $viaje = Viaje::findOrFail($id);

        $this->form = [
            'vehiculo_id' => (string) $viaje->vehiculo_id,
            'conductor_id' => (string) $viaje->conductor_id,
            'fecha_hora_inicio' => optional($viaje->fecha_hora_inicio)->format('Y-m-d\TH:i'),
            'fecha_hora_fin' => optional($viaje->fecha_hora_fin)->format('Y-m-d\TH:i'),
            'kilometraje_inicio' => $viaje->kilometraje_inicio,
            'kilometraje_fin' => $viaje->kilometraje_fin ?? '',
            'proposito' => $viaje->proposito,
            'estado' => $viaje->estado,
        ];

        $this->editingId = $id;
        $this->modalOpen = true;
    }

    /**
     * confirmDelete() - Abre el modal de confirmación
     *
     * @param int $id ID del viaje seleccionado
     */
    public function confirmDelete($id)
    {
        $this->deletingId = $id;
        $this->confirmDeleteModal = true;
    }

    /**
     * deleteConfirmed() - Elimina físicamente el viaje
     */
    public function deleteConfirmed()
    {
        $viaje = Viaje::findOrFail($this->deletingId);
        $viaje->delete();

        $this->dispatch('show-custom-toast', type: 'success', message: 'Viaje eliminado correctamente.');

        $this->confirmDeleteModal = false;
        $this->deletingId = null;
    }

    /**
     * save() - Crea o actualiza un viaje
     *
     * Reglas relevantes:
     * - Vehículo y conductor son obligatorios
     * - El fin no puede ser anterior al inicio
     * - El kilometraje final no puede ser menor al inicial
     * - Si el viaje está completado, fecha y kilometraje final pasan a ser obligatorios
     */
    public function save()
    {
        $validated = $this->validate([
            'form.vehiculo_id' => 'required|exists:vehiculos,id',
            'form.conductor_id' => 'required|exists:conductores,id',
            'form.fecha_hora_inicio' => 'required|date',
            'form.fecha_hora_fin' => 'required|date|after_or_equal:form.fecha_hora_inicio',
            'form.kilometraje_inicio' => 'required|integer|min:0',
            'form.kilometraje_fin' => 'required|integer|gte:form.kilometraje_inicio',
            'form.proposito' => 'required|string',
            'form.estado' => 'required|in:planificado,en_curso,completado,cancelado',
        ]);

        $data = $validated['form'];

        if ($this->editingId) {
            $viaje = Viaje::findOrFail($this->editingId);
            $viaje->update($data);
            $this->dispatch('show-custom-toast', type: 'success', message: 'Viaje actualizado correctamente.');
        } else {
            $viaje = Viaje::create($data);
            $this->dispatch('show-custom-toast', type: 'success', message: 'Viaje creado correctamente.');
        }

        $this->syncVehiculoKilometraje($viaje);

        $this->modalOpen = false;
        $this->resetForm();
    }

    /**
     * syncVehiculoKilometraje() - Actualiza el kilometraje del vehículo
     *
     * Solo aplica si el viaje fue completado y su kilometraje final
     * supera el kilometraje actual almacenado en el vehículo.
     */
    protected function syncVehiculoKilometraje(Viaje $viaje): void
    {
        if ($viaje->estado !== 'completado' || is_null($viaje->kilometraje_fin)) {
            return;
        }

        $vehiculo = Vehiculo::find($viaje->vehiculo_id);

        if ($vehiculo && $viaje->kilometraje_fin > $vehiculo->kilometraje_actual) {
            $vehiculo->update([
                'kilometraje_actual' => $viaje->kilometraje_fin,
            ]);
        }
    }

    /**
     * render() - Construye la consulta principal del listado
     *
     * Incluye:
     * - relaciones necesarias para la tabla
     * - búsqueda por propósito, vehículo y conductor
     * - filtro por estado
     * - ordenamiento y paginación
     *
     * Además envía catálogos de vehículos y conductores al modal.
     */
    public function render()
    {
        $viajes = Viaje::query()
            ->with(['vehiculo', 'conductor'])
            ->when($this->search, function ($query) {
                // La búsqueda se encapsula en un where agrupado para combinar varios criterios
                $query->where(function ($subQuery) {
                    $subQuery->where('proposito', 'like', '%' . $this->search . '%')
                        ->orWhereHas('vehiculo', function ($vehiculos) {
                            $vehiculos->where('placa', 'like', '%' . $this->search . '%')
                                ->orWhere('marca', 'like', '%' . $this->search . '%');
                        })
                        ->orWhereHas('conductor', function ($conductores) {
                            $conductores->where('nombre', 'like', '%' . $this->search . '%')
                                ->orWhere('numero_licencia', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->estadoFilter !== 'todos', function ($query) {
                    $query->where('estado', $this->estadoFilter);
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);

        // Los catálogos se usan para poblar los selectores del formulario
        return view('livewire.viajes', [
            'viajes' => $viajes,
            'vehiculos' => Vehiculo::orderBy('placa')->get(),
            'conductores' => Conductor::orderBy('nombre')->get(),
        ]);
    }
}
