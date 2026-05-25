<div class="space-y-4">

    <flux:heading size="xl">Combustible</flux:heading>
    <flux:subheading>Listado de registros de combustible</flux:subheading>
    <flux:separator />

    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

        <div class="w-full md:max-w-sm">
            <flux:input
                type="search"
                placeholder="{{ __('Buscar por vehículo, conductor, estación o factura...') }}"
                wire:model.live="search"
            />
        </div>

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
                :variant="$estadoFilter === 'conductor_asignado' ? 'primary' : 'ghost'"
                wire:click="$set('estadoFilter', 'conductor_asignado')"
            >
                Conductor
            </flux:button>
            <flux:button
                size="sm"
                :variant="$estadoFilter === 'sin_conductor' ? 'primary' : 'ghost'"
                wire:click="$set('estadoFilter', 'sin_conductor')"
            >
                Sin Conductor
            </flux:button>
        </div>

        <div>
            <flux:button wire:click="create" variant="primary">
                + Nuevo Registro
            </flux:button>
        </div>

    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr class="border-b border-zinc-200 dark:border-zinc-700">
                    <th scope="col" class="px-4 py-3 text-left text-sm font-medium cursor-pointer" wire:click="sortBy('fecha')">
                        <div class="flex items-center gap-1">
                            {{ __('Fecha') }}
                            @if ($sortBy === 'fecha')
                                <span class="text-xs">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </div>
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-sm font-medium">
                        {{ __('Vehículo') }}
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-sm font-medium">
                        {{ __('Conductor') }}
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-sm font-medium cursor-pointer" wire:click="sortBy('litros')">
                        <div class="flex items-center gap-1">
                            {{ __('Litros') }}
                            @if ($sortBy === 'litros')
                                <span class="text-xs">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </div>
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-sm font-medium cursor-pointer" wire:click="sortBy('costo_total')">
                        <div class="flex items-center gap-1">
                            {{ __('Costo') }}
                            @if ($sortBy === 'costo_total')
                                <span class="text-xs">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </div>
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-sm font-medium cursor-pointer" wire:click="sortBy('kilometraje')">
                        <div class="flex items-center gap-1">
                            {{ __('Kilometraje') }}
                            @if ($sortBy === 'kilometraje')
                                <span class="text-xs">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </div>
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-sm font-medium">
                        {{ __('Estación') }}
                    </th>
                    <th scope="col" class="px-4 py-3 text-right text-sm font-medium">
                        {{ __('Acciones') }}
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse ($registros as $registro)
                    <tr class="border-b border-zinc-200 dark:border-zinc-700" wire:key="registro-{{ $registro->id }}">
                        <td class="px-4 py-2">{{ optional($registro->fecha)->format('d/m/Y') }}</td>
                        <td class="px-4 py-2 font-medium">
                            {{ $registro->vehiculo?->placa }}
                            @if($registro->vehiculo?->marca)
                                <span class="text-gray-500">({{ $registro->vehiculo->marca }})</span>
                            @endif
                        </td>
                        <td class="px-4 py-2">{{ $registro->conductor?->nombre ?: 'N/A' }}</td>
                        <td class="px-4 py-2">{{ number_format((float) $registro->litros, 2, '.', ',') }} L</td>
                        <td class="px-4 py-2">${{ number_format((float) $registro->costo_total, 2, '.', ',') }}</td>
                        <td class="px-4 py-2">{{ number_format($registro->kilometraje, 0, ',', '.') }} km</td>
                        <td class="px-4 py-2">{{ $registro->estacion_servicio ?: 'N/A' }}</td>
                        <td class="px-4 py-2">
                            <div class="flex justify-end gap-2">
                                <flux:dropdown>
                                    <flux:button icon:trailing="chevron-down" size="sm">Acciones</flux:button>
                                    <flux:menu>
                                        <flux:menu.item wire:click="edit({{ $registro->id }})">Editar</flux:menu.item>
                                        <flux:menu.item wire:click="confirmDelete({{ $registro->id }})">Eliminar</flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="border-b border-zinc-200 dark:border-zinc-700">
                        <td colspan="8" class="px-4 py-6 text-center text-sm text-gray-500">
                            {{ __('No hay registros de combustible.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $registros->links() }}
    </div>

    @if($modalOpen)
    <flux:modal name="combustible-modal" class="md:w-3xl" wire:model="modalOpen">
        <div class="space-y-4 p-4">
            <div class="flex justify-between items-center">
                <flux:heading size="lg">{{ $editingId ? 'Editar Registro' : 'Nuevo Registro de Combustible' }}</flux:heading>
            </div>

            <flux:separator />

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:label>Vehículo *</flux:label>
                    <flux:select wire:model="form.vehiculo_id" required>
                        <option value="">Seleccionar...</option>
                        @foreach ($vehiculos as $vehiculo)
                            <option value="{{ $vehiculo->id }}">{{ $vehiculo->placa }} - {{ $vehiculo->marca }} {{ $vehiculo->modelo }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="form.vehiculo_id" />
                </div>

                <div>
                    <flux:label>Conductor *</flux:label>
                    <flux:select wire:model="form.conductor_id" required>
                        <option value="">Seleccionar...</option>
                        @foreach ($conductores as $conductor)
                            <option value="{{ $conductor->id }}">{{ $conductor->nombre }} - {{ $conductor->numero_licencia }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="form.conductor_id" />
                </div>

                <div>
                    <flux:label>Fecha *</flux:label>
                    <flux:input type="date" wire:model="form.fecha" required />
                    <flux:error name="form.fecha" />
                </div>

                <div>
                    <flux:label>Kilometraje *</flux:label>
                    <flux:input type="number" wire:model="form.kilometraje" placeholder="0" required />
                    <flux:error name="form.kilometraje" />
                </div>

                <div>
                    <flux:label>Litros *</flux:label>
                    <flux:input type="number" step="0.01" wire:model="form.litros" placeholder="0.00" required />
                    <flux:error name="form.litros" />
                </div>

                <div>
                    <flux:label>Costo Total *</flux:label>
                    <flux:input type="number" step="0.01" wire:model="form.costo_total" placeholder="0.00" required />
                    <flux:error name="form.costo_total" />
                </div>

                <div>
                    <flux:label>Estación de Servicio *</flux:label>
                    <flux:input wire:model="form.estacion_servicio" placeholder="Nombre de la estación" required />
                    <flux:error name="form.estacion_servicio" />
                </div>

                <div>
                    <flux:label>Factura *</flux:label>
                    <flux:input wire:model="form.factura" placeholder="FAC-C-0001" required />
                    <flux:error name="form.factura" />
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
                        ¿Estás seguro de que deseas eliminar este registro de combustible?<br>
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

    <x-toast-notifications />
</div>
