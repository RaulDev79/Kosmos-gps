<?php

namespace App\Livewire;

// Importamos las clases necesarias
use Livewire\Component;           // Clase base de Livewire
use Livewire\WithPagination;      // Trait para agregar paginación automática
use Livewire\WithoutUrlPagination; // Trait para paginación sin parámetros en URL
use App\Models\Vehiculo;           // Modelo de Vehículo

class Vehiculos extends Component
{
    // =========================================================================
    // TRAITS (Características reutilizables)
    // =========================================================================
    // WithPagination: Proporciona el método paginate() y mantiene el estado de la página
    // WithoutUrlPagination: La paginación NO se guarda en la URL (más limpio)
    use WithPagination, WithoutUrlPagination;

    // =========================================================================
    // PROPIEDADES PÚBLICAS (Sincronizadas con la vista automáticamente)
    // =========================================================================
    
    /**
     * @var string Búsqueda en tiempo real (wire:model.live)
     * Al escribir en el input de búsqueda, esta propiedad se actualiza
     * y automáticamente re-renderiza la tabla
     */
    public $search = '';
    
    /**
     * @var string Filtro por estado del vehículo
     * Valores posibles: 'todos', 'activo', 'mantenimiento', 'inactivo'
     */
    public $estadoFilter = 'todos';
    
    /**
     * @var string Campo por el cual se ordena la tabla
     * Por defecto ordenamos por placa
     */
    public $sortBy = 'placa';
    
    /**
     * @var string Dirección del ordenamiento: 'asc' (ascendente) o 'desc' (descendente)
     */
    public $sortDirection = 'asc';
    
    /**
     * @var int Cantidad de registros a mostrar por página
     */
    public $perPage = 10;
    
    // =========================================================================
    // PROPIEDADES DEL MODAL (Crear/Editar)
    // =========================================================================
    
    /**
     * @var bool Controla si el modal está abierto o cerrado
     * Si es true, se muestra el modal en la vista
     */
    public $modalOpen = false;
    
    /**
     * @var int|null ID del vehículo que se está editando
     * Si es null, estamos creando un nuevo vehículo
     */
    public $editingId = null;
    
    /**
     * @var array Datos del formulario (se bindean con wire:model en la vista)
     * Cada clave corresponde a un campo de la tabla vehiculos
     */
    public $form = [
        'placa' => '',              // Placa única del vehículo
        'marca' => '',              // Marca (Mercedes, Volvo, etc)
        'modelo' => '',             // Modelo específico
        'anio' => '',               // Año de fabricación
        'vin' => '',                // Número de chasis (Vehicle Identification Number)
        'numero_motor' => '',       // Número de motor
        'tipo' => '',               // camion, furgoneta, autobus, camioneta
        'estado' => 'activo',       // activo, mantenimiento, inactivo
        'fecha_compra' => '',       // Fecha de adquisición
        'kilometraje_actual' => 0,  // Kilómetros recorridos actuales
    ];

    // =========================================================================
    // PROPIEDADES PARA CONFIRMACIÓN DE ELIMINACIÓN
    // =========================================================================
    
    /**
     * @var int|null ID del vehículo que se quiere eliminar
     */
    public $deletingId = null;
    
    /**
     * @var bool Controla si el modal de confirmación está abierto
     */
    public $confirmDeleteModal = false;

    // =========================================================================
    // MÉTODOS DE CICLO DE VIDA DE LIVEWIRE
    // =========================================================================
    
    /**
     * mount() - Se ejecuta UNA SOLA vez al cargar el componente
     * Es como el constructor de Livewire
     * 
     * Aquí inicializamos el formulario vacío
     */
    public function mount()
    {
        $this->resetForm();
    }

    // =========================================================================
    // MÉTODOS PRINCIPALES DEL CRUD
    // =========================================================================
    
    /**
     * resetForm() - Reinicia el formulario a su estado inicial
     * 
     * Se usa cuando:
     * - Se cierra el modal
     * - Se hace clic en "Nuevo Vehículo"
     * - Después de guardar exitosamente
     */
    public function resetForm()
    {
        $this->form = [
            'placa' => '',
            'marca' => '',
            'modelo' => '',
            'anio' => '',
            'vin' => '',
            'numero_motor' => '',
            'tipo' => '',
            'estado' => 'activo',
            'fecha_compra' => '',
            'kilometraje_actual' => 0,
        ];
        $this->editingId = null;  // Aseguramos que no estamos en modo edición
    }
    
    /**
     * sortBy() - Cambia el campo de ordenamiento de la tabla
     * 
     * Lógica:
     * - Si ya estamos ordenando por el mismo campo, invertimos la dirección (asc↔desc)
     * - Si es un campo nuevo, ordenamos ascendente por defecto
     * 
     * @param string $field El nombre del campo por el cual ordenar
     */
    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            // Mismo campo: invertir dirección
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            // Nuevo campo: establecer como campo de orden y dirección ascendente
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    /**
     * create() - Prepara el formulario para crear un nuevo vehículo
     * 
     * Lo que hace:
     * 1. Reinicia el formulario a valores vacíos
     * 2. Abre el modal
     */
    public function create()
    {
        $this->resetForm();      // Formulario limpio
        $this->modalOpen = true; // Mostrar modal
    }

    /**
     * edit() - Carga los datos de un vehículo existente para editar
     * 
     * Flujo:
     * 1. Busca el vehículo por ID (lanza excepción si no existe)
     * 2. Copia los datos al formulario
     * 3. Guarda el ID para saber que estamos editando
     * 4. Abre el modal
     * 
     * @param int $id ID del vehículo a editar
     */
    public function edit($id)
    {
        $vehiculo = Vehiculo::findOrFail($id);   // Buscar o error 404
        $this->form = $vehiculo->toArray();      // Convertir modelo a array
        $this->editingId = $id;                  // Guardar ID para la actualización
        $this->modalOpen = true;                 // Mostrar modal
    }

    /**
     * confirmDelete() - Muestra el modal de confirmación para eliminar
     * 
     * @param int $id ID del vehículo a eliminar
     */
    public function confirmDelete($id)
    {
        $this->deletingId = $id;
        $this->confirmDeleteModal = true;
    }

    /**
     * deleteConfirmed() - Elimina el vehículo después de confirmar
     * 
     * Consideraciones importantes:
     * - Verificamos si el vehículo tiene viajes asociados
     * - Si tiene viajes, NO se puede eliminar (integridad referencial)
     * - Muestra notificación toast con el resultado
     */
    public function deleteConfirmed()
    {
        $vehiculo = Vehiculo::findOrFail($this->deletingId);
        
        // Verificar si el vehículo tiene viajes relacionados
        // viajes() es la relación definida en el modelo Vehiculo
        if ($vehiculo->viajes()->count() > 0) {
            // ✅ Notificación de error vía toast
            $this->dispatch('show-custom-toast', type: 'error', message: 'No se puede eliminar el vehículo porque tiene viajes asociados.');
            $this->confirmDeleteModal = false;
            $this->deletingId = null;
            return;  // Salir del método sin eliminar
        }
        
        // Si no tiene viajes, proceder con la eliminación
        $vehiculo->delete();
        
        // ✅ Notificación de éxito vía toast
        $this->dispatch('show-custom-toast', type: 'success', message: 'Vehículo eliminado correctamente.');
        
        $this->confirmDeleteModal = false;
        $this->deletingId = null;
    }

    /**
     * save() - Guarda (crea o actualiza) un vehículo
     * 
     * Este es el método más importante del CRUD
     * 
     * Pasos:
     * 1. Validar los datos del formulario
     * 2. Si hay editingId → ACTUALIZAR vehículo existente
     * 3. Si no hay editingId → CREAR nuevo vehículo
     * 4. Cerrar modal y reiniciar formulario
     * 5. Mostrar mensaje de éxito (vía toast)
     */
    public function save()
    {
        // =============================================================
        // VALIDACIÓN DE DATOS
        // =============================================================
        // Reglas de validación:
        // - required: campo obligatorio
        // - string: debe ser texto
        // - max: longitud máxima
        // - unique: valor único en la tabla (excepto si se está editando)
        // - integer: número entero
        // - min/max: rangos numéricos
        // - in: valores permitidos (lista blanca)
        // - date: formato de fecha válido
        // =============================================================
        
        $this->validate([
            // placa: obligatoria, texto, máx 10 caracteres, ÚNICA en la tabla vehiculos
            // La regla unique ignora el registro actual cuando se edita (gracias a $this->editingId)
            'form.placa' => 'required|string|max:10|unique:vehiculos,placa,' . $this->editingId,
            
            'form.marca' => 'required|string|max:50',
            'form.modelo' => 'required|string|max:50',
            
            // anio: entre 1900 y el año siguiente al actual (ej: 2026 es válido)
            'form.anio' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            
            'form.vin' => 'required|string|max:17',
            'form.numero_motor' => 'required|string|max:30',
            
            // tipo: solo estos 4 valores permitidos
            'form.tipo' => 'required|in:camion,furgoneta,autobus,camioneta',
            
            // estado: solo estos 3 valores
            'form.estado' => 'required|in:activo,mantenimiento,inactivo',
            
            'form.fecha_compra' => 'required|date',
            'form.kilometraje_actual' => 'required|integer|min:0',
        ]);

        // =============================================================
        // GUARDADO (CREATE o UPDATE)
        // =============================================================
        
        if ($this->editingId) {
            // MODO EDICIÓN: Actualizar registro existente
            $vehiculo = Vehiculo::findOrFail($this->editingId);
            $vehiculo->update($this->form);  // update() solo modifica los campos enviados
            // ✅ Notificación de éxito vía toast
            $this->dispatch('show-custom-toast', type: 'success', message: 'Vehículo actualizado correctamente.');
        } else {
            // MODO CREACIÓN: Insertar nuevo registro
            Vehiculo::create($this->form);   // create() inserta una nueva fila
            // ✅ Notificación de éxito vía toast
            $this->dispatch('show-custom-toast', type: 'success', message: 'Vehículo creado correctamente.');
        }

        // =============================================================
        // LIMPIEZA POST-GUARDADO
        // =============================================================
        $this->modalOpen = false;   // Cerrar modal
        $this->resetForm();         // Limpiar formulario
        // Nota: No necesitamos resetear la paginación porque WithPagination lo maneja
    }

    // =========================================================================
    // MÉTODO RENDER (CORAZÓN DEL COMPONENTE)
    // =========================================================================
    
    /**
     * render() - Construye la consulta y retorna la vista
     * 
     * Este método se ejecuta CADA VEZ que cambia alguna propiedad pública
     * (gracias a la reactividad de Livewire)
     * 
     * Flujo de la consulta:
     * 1. Iniciamos con Vehiculo::query()
     * 2. Aplicamos filtros condicionales (when)
     * 3. Aplicamos ordenamiento
     * 4. Paginamos los resultados
     * 5. Pasamos los datos a la vista
     * 
     * @return \Illuminate\View\View
     */
    public function render()
    {
        // Construcción de la consulta usando Eloquent ORM
        $vehiculos = Vehiculo::query()
            
            // =============================================================
            // FILTRO DE BÚSQUEDA (cuando hay texto en $search)
            // =============================================================
            ->when($this->search, function ($query) {
                // Buscar en tres campos: placa, marca o modelo
                // Usamos LIKE con comodines % para búsqueda parcial
                $query->where('placa', 'like', '%' . $this->search . '%')
                    ->orWhere('marca', 'like', '%' . $this->search . '%')
                    ->orWhere('modelo', 'like', '%' . $this->search . '%');
            })
            
            // =============================================================
            // FILTRO POR ESTADO (cuando NO es 'todos')
            // =============================================================
            ->when($this->estadoFilter !== 'todos', function ($query) {
                // Filtro exacto por el valor del estado
                $query->where('estado', $this->estadoFilter);
            })
            
            // =============================================================
            // ORDENAMIENTO
            // =============================================================
            // orderBy(campo, dirección)
            // Ejemplo: orderBy('placa', 'asc')
            ->orderBy($this->sortBy, $this->sortDirection)
            
            // =============================================================
            // PAGINACIÓN
            // =============================================================
            // paginate() es proporcionado por el trait WithPagination
            // Muestra $perPage registros por página
            ->paginate($this->perPage);

        // Retornamos la vista con los datos
        // La variable $vehiculos estará disponible en la vista como $vehiculos
        return view('livewire.vehiculos', [
            'vehiculos' => $vehiculos,
        ]);
    }
}
