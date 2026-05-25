<?php

namespace App\Livewire;

use App\Models\Conductor;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;

class Conductores extends Component
{
    // =========================================================================
    // TRAITS
    // =========================================================================
    // WithPagination: habilita paginación en el componente
    // WithoutUrlPagination: evita guardar la página actual en la URL
    use WithPagination, WithoutUrlPagination;

    // =========================================================================
    // PROPIEDADES PÚBLICAS DE LISTADO
    // =========================================================================
    // search: texto de búsqueda reactiva
    // estadoFilter: filtro por estado del conductor
    // sortBy / sortDirection: ordenamiento de la tabla
    // perPage: cantidad de registros por página
    public $search = '';
    public $estadoFilter = 'todos';
    public $sortBy = 'nombre';
    public $sortDirection = 'asc';
    public $perPage = 10;

    // =========================================================================
    // PROPIEDADES DEL MODAL DE FORMULARIO
    // =========================================================================
    // modalOpen: controla visibilidad del modal crear/editar
    // editingId: si tiene valor, el formulario está en modo edición
    public $modalOpen = false;
    public $editingId = null;

    // =========================================================================
    // FORMULARIO
    // =========================================================================
    // form: concentra los campos bindeados desde la vista
    public $form = [
        'nombre' => '',
        'numero_licencia' => '',
        'vencimiento_licencia' => '',
        'telefono' => '',
        'fecha_contratacion' => '',
        'estado' => 'activo',
    ];

    // =========================================================================
    // PROPIEDADES DE ELIMINACIÓN
    // =========================================================================
    // deletingId: conductor seleccionado para eliminar
    // confirmDeleteModal: controla el modal de confirmación
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
     * resetForm() - Reinicia el formulario a valores por defecto
     *
     * Se usa al abrir creación, al cerrar flujo de guardado
     * y cuando se quiere limpiar el estado de edición.
     */
    public function resetForm()
    {
        $this->form = [
            'nombre' => '',
            'numero_licencia' => '',
            'vencimiento_licencia' => '',
            'telefono' => '',
            'fecha_contratacion' => '',
            'estado' => 'activo',
        ];

        $this->editingId = null;
    }

    /**
     * sortBy() - Cambia el campo de ordenamiento o invierte la dirección
     *
     * @param string $field Campo por el que se ordenará la tabla
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
     * create() - Prepara el formulario para registrar un nuevo conductor
     */
    public function create()
    {
        $this->resetForm();
        $this->modalOpen = true;
    }

    /**
     * edit() - Carga un conductor existente dentro del formulario
     *
     * @param int $id ID del conductor a editar
     */
    public function edit($id)
    {
        $conductor = Conductor::findOrFail($id);
        $this->form = $conductor->toArray();
        $this->editingId = $id;
        $this->modalOpen = true;
    }

    /**
     * confirmDelete() - Abre el modal de confirmación de eliminación
     *
     * @param int $id ID del conductor a eliminar
     */
    public function confirmDelete($id)
    {
        $this->deletingId = $id;
        $this->confirmDeleteModal = true;
    }

    /**
     * deleteConfirmed() - Elimina el conductor si no tiene dependencias
     *
     * Reglas actuales:
     * - No se puede eliminar si tiene viajes asociados
     * - No se puede eliminar si tiene registros de combustible asociados
     */
    public function deleteConfirmed()
    {
        $conductor = Conductor::findOrFail($this->deletingId);

        if ($conductor->viajes()->count() > 0) {
            // Se protege la integridad funcional del historial de viajes
            $this->dispatch('show-custom-toast', type: 'error', message: 'No se puede eliminar el conductor porque tiene viajes asociados.');
            $this->confirmDeleteModal = false;
            $this->deletingId = null;
            return;
        }

        if ($conductor->registrosCombustible()->count() > 0) {
            // También se protege el historial de combustible relacionado
            $this->dispatch('show-custom-toast', type: 'error', message: 'No se puede eliminar el conductor porque tiene registros de combustible asociados.');
            $this->confirmDeleteModal = false;
            $this->deletingId = null;
            return;
        }

        $conductor->delete();

        $this->dispatch('show-custom-toast', type: 'success', message: 'Conductor eliminado correctamente.');

        $this->confirmDeleteModal = false;
        $this->deletingId = null;
    }

    /**
     * save() - Crea o actualiza un conductor
     *
     * Flujo:
     * 1. Validar datos del formulario
     * 2. Actualizar si existe editingId
     * 3. Crear si no existe editingId
     * 4. Cerrar modal, limpiar formulario y mostrar toast
     */
    public function save()
    {
        $this->validate([
            'form.nombre' => 'required|string|max:255',
            'form.numero_licencia' => 'required|string|max:20|unique:conductores,numero_licencia,' . $this->editingId,
            'form.vencimiento_licencia' => 'required|date',
            'form.telefono' => 'required|string|max:20',
            'form.fecha_contratacion' => 'required|date',
            'form.estado' => 'required|in:activo,suspendido,inactivo',
        ]);

        if ($this->editingId) {
            $conductor = Conductor::findOrFail($this->editingId);
            $conductor->update($this->form);
            $this->dispatch('show-custom-toast', type: 'success', message: 'Conductor actualizado correctamente.');
        } else {
            Conductor::create($this->form);
            $this->dispatch('show-custom-toast', type: 'success', message: 'Conductor creado correctamente.');
        }

        $this->modalOpen = false;
        $this->resetForm();
    }

    /**
     * render() - Construye la consulta del listado y retorna la vista
     *
     * Incluye búsqueda, filtro por estado, ordenamiento y paginación.
     */
    public function render()
    {
        $conductores = Conductor::query()
            ->when($this->search, function ($query) {
                // La búsqueda se aplica sobre nombre, licencia y teléfono
                $query->where('nombre', 'like', '%' . $this->search . '%')
                    ->orWhere('numero_licencia', 'like', '%' . $this->search . '%')
                    ->orWhere('telefono', 'like', '%' . $this->search . '%');
            })
            ->when($this->estadoFilter !== 'todos', function ($query) {
                // Si el filtro no es "todos", se restringe por estado exacto
                $query->where('estado', $this->estadoFilter);
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.conductores', [
            'conductores' => $conductores,
        ]);
    }
}
