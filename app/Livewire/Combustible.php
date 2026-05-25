<?php

namespace App\Livewire;

use App\Models\Conductor;
use App\Models\RegistroCombustible;
use App\Models\Vehiculo;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;

class Combustible extends Component
{
    // =========================================================================
    // TRAITS
    // =========================================================================
    use WithPagination, WithoutUrlPagination;

    // =========================================================================
    // PROPIEDADES PÚBLICAS DE LISTADO
    // =========================================================================
    public $search = '';
    public $estadoFilter = 'todos';
    public $sortBy = 'fecha';
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
        'conductor_id' => '',
        'fecha' => '',
        'litros' => '',
        'costo_total' => '',
        'kilometraje' => 0,
        'estacion_servicio' => '',
        'factura' => '',
    ];

    // =========================================================================
    // ELIMINACIÓN
    // =========================================================================
    public $deletingId = null;
    public $confirmDeleteModal = false;

    /**
     * mount() - Inicializa el estado base del formulario
     */
    public function mount()
    {
        $this->resetForm();
    }

    /**
     * resetForm() - Limpia el formulario y desactiva el modo edición
     */
    public function resetForm()
    {
        $this->form = [
            'vehiculo_id' => '',
            'conductor_id' => '',
            'fecha' => '',
            'litros' => '',
            'costo_total' => '',
            'kilometraje' => 0,
            'estacion_servicio' => '',
            'factura' => '',
        ];

        $this->editingId = null;
    }

    /**
     * sortBy() - Administra el campo y dirección de ordenamiento
     *
     * @param string $field Campo seleccionado desde la cabecera de la tabla
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
     * create() - Abre el modal para registrar una nueva carga de combustible
     */
    public function create()
    {
        $this->resetForm();
        $this->modalOpen = true;
    }

    /**
     * edit() - Carga un registro existente en el formulario
     *
     * @param int $id ID del registro de combustible a editar
     */
    public function edit($id)
    {
        $registro = RegistroCombustible::findOrFail($id);

        $this->form = [
            'vehiculo_id' => (string) $registro->vehiculo_id,
            'conductor_id' => $registro->conductor_id ? (string) $registro->conductor_id : '',
            'fecha' => optional($registro->fecha)->format('Y-m-d'),
            'litros' => $registro->litros,
            'costo_total' => $registro->costo_total,
            'kilometraje' => $registro->kilometraje,
            'estacion_servicio' => $registro->estacion_servicio ?? '',
            'factura' => $registro->factura ?? '',
        ];

        $this->editingId = $id;
        $this->modalOpen = true;
    }

    /**
     * confirmDelete() - Activa el modal de confirmación de eliminación
     *
     * @param int $id ID del registro a eliminar
     */
    public function confirmDelete($id)
    {
        $this->deletingId = $id;
        $this->confirmDeleteModal = true;
    }

    /**
     * deleteConfirmed() - Elimina el registro de combustible seleccionado
     */
    public function deleteConfirmed()
    {
        $registro = RegistroCombustible::findOrFail($this->deletingId);
        $registro->delete();

        $this->dispatch('show-custom-toast', type: 'success', message: 'Registro de combustible eliminado correctamente.');

        $this->confirmDeleteModal = false;
        $this->deletingId = null;
    }

    /**
     * save() - Crea o actualiza un registro de combustible
     *
     * Consideraciones:
     * - litros debe ser mayor a cero
     * - el kilometraje se usa también para sincronizar el vehículo
     */
    public function save()
    {
        $validated = $this->validate([
            'form.vehiculo_id' => 'required|exists:vehiculos,id',
            'form.conductor_id' => 'required|exists:conductores,id',
            'form.fecha' => 'required|date',
            'form.litros' => 'required|numeric|min:0.01',
            'form.costo_total' => 'required|numeric|min:0',
            'form.kilometraje' => 'required|integer|min:0',
            'form.estacion_servicio' => 'required|string|max:100',
            'form.factura' => 'required|string|max:100',
        ]);

        $data = $validated['form'];

        if ($this->editingId) {
            $registro = RegistroCombustible::findOrFail($this->editingId);
            $registro->update($data);
            $this->dispatch('show-custom-toast', type: 'success', message: 'Registro de combustible actualizado correctamente.');
        } else {
            $registro = RegistroCombustible::create($data);
            $this->dispatch('show-custom-toast', type: 'success', message: 'Registro de combustible creado correctamente.');
        }

        $this->syncVehiculoKilometraje($registro);

        $this->modalOpen = false;
        $this->resetForm();
    }

    /**
     * syncVehiculoKilometraje() - Actualiza el kilometraje actual del vehículo
     *
     * Si el registro reporta un kilometraje superior al actual, el vehículo
     * conserva ese mayor valor como referencia operativa.
     */
    protected function syncVehiculoKilometraje(RegistroCombustible $registro): void
    {
        $vehiculo = Vehiculo::find($registro->vehiculo_id);

        if ($vehiculo && $registro->kilometraje > $vehiculo->kilometraje_actual) {
            $vehiculo->update([
                'kilometraje_actual' => $registro->kilometraje,
            ]);
        }
    }

    /**
     * render() - Construye la consulta del listado y retorna la vista
     *
     * Incluye relaciones, búsqueda por múltiples criterios,
     * filtros por conductor asignado y catálogos para el modal.
     */
    public function render()
    {
        $registros = RegistroCombustible::query()
            ->with(['vehiculo', 'conductor'])
            ->when($this->search, function ($query) {
                // Se agrupan los criterios para combinar campos propios y relaciones
                $query->where(function ($subQuery) {
                    $subQuery->where('estacion_servicio', 'like', '%' . $this->search . '%')
                        ->orWhere('factura', 'like', '%' . $this->search . '%')
                        ->orWhereHas('vehiculo', function ($vehiculos) {
                            $vehiculos->where('placa', 'like', '%' . $this->search . '%')
                                ->orWhere('marca', 'like', '%' . $this->search . '%')
                                ->orWhere('modelo', 'like', '%' . $this->search . '%');
                        })
                        ->orWhereHas('conductor', function ($conductores) {
                            $conductores->where('nombre', 'like', '%' . $this->search . '%')
                                ->orWhere('numero_licencia', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->estadoFilter === 'conductor_asignado', function ($query) {
                // Registros vinculados a algún conductor
                $query->whereNotNull('conductor_id');
            })
            ->when($this->estadoFilter === 'sin_conductor', function ($query) {
                // Registros que no tienen conductor asociado
                $query->whereNull('conductor_id');
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);

        // Los selectores del modal necesitan los catálogos base
        return view('livewire.combustible', [
            'registros' => $registros,
            'vehiculos' => Vehiculo::orderBy('placa')->get(),
            'conductores' => Conductor::orderBy('nombre')->get(),
        ]);
    }
}
