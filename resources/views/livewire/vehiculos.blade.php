<div class="space-y-4">

    <flux:heading size="xl">Vehículos</flux:heading>
    <flux:subheading>Listado de vehículos de la flota</flux:subheading>
    <flux:separator />

    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

        {{-- 🔍 Búsqueda --}}
        <div class="w-full md:max-w-sm">
            <flux:input
                type="search"
                placeholder="{{ __('Buscar por placa, marca o modelo...') }}"
                wire:model.live="search"
            />
        </div>

        {{-- 🔁 Filtro de estado --}}
        <div class="flex items-center gap-2">
            <flux:button
                size="sm"
                :variant="$estadoFilter === 'todos' ? 'primary' : 'ghost'"
                wire:click="$set('estadoFilter', 'todos')"
            >
                Todos
            </flux:button>
            <flux:button
                size="sm"
                :variant="$estadoFilter === 'activo' ? 'primary' : 'ghost'"
                wire:click="$set('estadoFilter', 'activo')"
            >
                Activos
            </flux:button>
            <flux:button
                size="sm"
                :variant="$estadoFilter === 'mantenimiento' ? 'primary' : 'ghost'"
                wire:click="$set('estadoFilter', 'mantenimiento')"
            >
                En Mantenimiento
            </flux:button>
            <flux:button
                size="sm"
                :variant="$estadoFilter === 'inactivo' ? 'primary' : 'ghost'"
                wire:click="$set('estadoFilter', 'inactivo')"
            >
                Inactivos
            </flux:button>
        </div>

        {{-- ➕ Botón Nuevo --}}
        <div>
            <flux:button wire:click="create" variant="primary">
                + Nuevo Vehículo
            </flux:button>
        </div>

    </div>

    {{-- Tabla --}}
    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr class="border-b border-zinc-200 dark:border-zinc-700">
                    <th scope="col" class="px-4 py-3 text-left text-sm font-medium cursor-pointer" wire:click="sortBy('placa')">
                        <div class="flex items-center gap-1">
                            {{ __('Placa') }}
                            @if ($sortBy === 'placa')
                                <span class="text-xs">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </div>
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-sm font-medium cursor-pointer" wire:click="sortBy('marca')">
                        <div class="flex items-center gap-1">
                            {{ __('Marca') }}
                            @if ($sortBy === 'marca')
                                <span class="text-xs">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </div>
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-sm font-medium cursor-pointer" wire:click="sortBy('modelo')">
                        <div class="flex items-center gap-1">
                            {{ __('Modelo') }}
                            @if ($sortBy === 'modelo')
                                <span class="text-xs">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </div>
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-sm font-medium cursor-pointer" wire:click="sortBy('anio')">
                        <div class="flex items-center gap-1">
                            {{ __('Año') }}
                            @if ($sortBy === 'anio')
                                <span class="text-xs">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </div>
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-sm font-medium cursor-pointer" wire:click="sortBy('tipo')">
                        <div class="flex items-center gap-1">
                            {{ __('Tipo') }}
                            @if ($sortBy === 'tipo')
                                <span class="text-xs">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </div>
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-sm font-medium cursor-pointer" wire:click="sortBy('kilometraje_actual')">
                        <div class="flex items-center gap-1">
                            {{ __('Kilometraje') }}
                            @if ($sortBy === 'kilometraje_actual')
                                <span class="text-xs">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </div>
                    </th>
                    <th scope="col" class="px-4 py-3 text-center text-sm font-medium cursor-pointer" wire:click="sortBy('estado')">
                        <div class="flex justify-center items-center gap-1">
                            {{ __('Estado') }}
                            @if ($sortBy === 'estado')
                                <span class="text-xs">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </div>
                    </th>
                    <th scope="col" class="px-4 py-3 text-right text-sm font-medium">
                        {{ __('Acciones') }}
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse ($vehiculos as $vehiculo)
                    <tr class="border-b border-zinc-200 dark:border-zinc-700" wire:key="vehiculo-{{ $vehiculo->id }}">
                        <td class="px-4 py-2 font-medium">{{ $vehiculo->placa }}</td>
                        <td class="px-4 py-2">{{ $vehiculo->marca }}</td>
                        <td class="px-4 py-2">{{ $vehiculo->modelo }}</td>
                        <td class="px-4 py-2">{{ $vehiculo->anio }}</td>
                        <td class="px-4 py-2">{{ ucfirst($vehiculo->tipo) }}</td>
                        <td class="px-4 py-2">{{ number_format($vehiculo->kilometraje_actual, 0, ',', '.') }} km</td>
                        <td class="px-4 py-2 text-center">
                            @php
                                $color = $vehiculo->estado === 'activo' ? 'green' : ($vehiculo->estado === 'mantenimiento' ? 'yellow' : 'red');
                            @endphp
                            <flux:badge size="sm" :color="$color">
                                {{ ucfirst($vehiculo->estado) }}
                            </flux:badge>
                        </td>
                        <td class="px-4 py-2">
                            <div class="flex justify-end gap-2">
                                <flux:dropdown>
                                    <flux:button icon:trailing="chevron-down" size="sm">Acciones</flux:button>
                                    <flux:menu>
                                        <flux:menu.item wire:click="edit({{ $vehiculo->id }})">Editar</flux:menu.item>
                                        <flux:menu.item wire:click="confirmDelete({{ $vehiculo->id }})">Eliminar</flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="border-b border-zinc-200 dark:border-zinc-700">
                        <td colspan="8" class="px-4 py-6 text-center text-sm text-gray-500">
                            {{ __('No hay vehículos registrados.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación --}}
    <div class="mt-4">
        {{ $vehiculos->links() }}
    </div>

    {{-- Modal para Crear/Editar Vehículo --}}
    @if($modalOpen)
    <flux:modal name="vehiculo-modal" class="md:w-2xl" wire:model="modalOpen">
        <div class="space-y-4 p-4">
            <div class="flex justify-between items-center">
                <flux:heading size="lg">{{ $editingId ? 'Editar Vehículo' : 'Nuevo Vehículo' }}</flux:heading>
            </div>
            
            <flux:separator />
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:label>Placa *</flux:label>
                    <flux:input wire:model="form.placa" placeholder="ABC-123" required />
                    <flux:error name="form.placa" />
                </div>
                
                <div>
                    <flux:label>Marca *</flux:label>
                    <flux:input wire:model="form.marca" placeholder="Mercedes-Benz, Volvo, etc." required />
                    <flux:error name="form.marca" />
                </div>
                
                <div>
                    <flux:label>Modelo *</flux:label>
                    <flux:input wire:model="form.modelo" placeholder="FH, Actros, etc." required />
                    <flux:error name="form.modelo" />
                </div>
                
                <div>
                    <flux:label>Año *</flux:label>
                    <flux:input type="number" wire:model="form.anio" placeholder="2020" required />
                    <flux:error name="form.anio" />
                </div>
                
                <div>
                    <flux:label>Tipo *</flux:label>
                    <flux:select wire:model="form.tipo" required>
                        <option value="">Seleccionar...</option>
                        <option value="camion">Camión</option>
                        <option value="furgoneta">Furgoneta</option>
                        <option value="autobus">Autobús</option>
                        <option value="camioneta">Camioneta</option>
                    </flux:select>
                    <flux:error name="form.tipo" />
                </div>
                
                <div>
                    <flux:label>Estado *</flux:label>
                    <flux:select wire:model="form.estado" required>
                        <option value="activo">Activo</option>
                        <option value="mantenimiento">Mantenimiento</option>
                        <option value="inactivo">Inactivo</option>
                    </flux:select>
                    <flux:error name="form.estado" />
                </div>
                
                <div>
                    <flux:label>Número de Chasis (VIN) *</flux:label>
                    <flux:input wire:model="form.vin" placeholder="Número de chasis" required />
                    <flux:error name="form.vin" />
                </div>
                
                <div>
                    <flux:label>Número de Motor *</flux:label>
                    <flux:input wire:model="form.numero_motor" placeholder="Número de motor" required />
                    <flux:error name="form.numero_motor" />
                </div>
                
                <div>
                    <flux:label>Fecha de Compra *</flux:label>
                    <flux:input type="date" wire:model="form.fecha_compra" required />
                    <flux:error name="form.fecha_compra" />
                </div>
                
                <div>
                    <flux:label>Kilometraje Actual *</flux:label>
                    <flux:input type="number" wire:model="form.kilometraje_actual" placeholder="0" required />
                    <flux:error name="form.kilometraje_actual" />
                </div>
            </div>
            
            <flux:separator />
            
            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" wire:click="$set('modalOpen', false)">Cancelar</flux:button>
                <flux:button variant="primary" wire:click="save">Guardar</flux:button>
            </div>
        </div>
    </flux:modal>
    @endif

    {{-- Modal de confirmación para eliminar --}}
    @if($confirmDeleteModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ open: true }">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-black/50"></div>
            
            <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md">
                <div class="p-6">
                    <div class="flex items-center justify-center mb-4">
                        <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                    </div>
                    
                    <h3 class="text-lg font-semibold text-center mb-2">Confirmar Eliminación</h3>
                    <p class="text-gray-500 text-center mb-6">
                        ¿Estás seguro de que deseas eliminar este vehículo?<br>
                        Esta acción no se puede deshacer.
                    </p>
                    
                    <div class="flex justify-center gap-3">
                        <button wire:click="$set('confirmDeleteModal', false)" 
                                class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-100">
                            Cancelar
                        </button>
                        <button wire:click="deleteConfirmed" 
                                class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">
                            Sí, Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Componente Toast --}}
    <x-toast-notifications />
</div>
