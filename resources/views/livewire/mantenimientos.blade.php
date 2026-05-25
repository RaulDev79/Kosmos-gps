<div class="space-y-4">

    <flux:heading size="xl">Mantenimientos</flux:heading>
    <flux:subheading>Listado de mantenimientos de la flota</flux:subheading>
    <flux:separator />

    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

        <div class="w-full md:max-w-sm">
            <flux:input
                type="search"
                placeholder="{{ __('Buscar por vehículo, taller, factura o descripción...') }}"
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
                :variant="$estadoFilter === 'pendientes' ? 'primary' : 'ghost'"
                wire:click="$set('estadoFilter', 'pendientes')"
            >
                Pendientes
            </flux:button>
            <flux:button
                size="sm"
                :variant="$estadoFilter === 'realizados' ? 'primary' : 'ghost'"
                wire:click="$set('estadoFilter', 'realizados')"
            >
                Realizados
            </flux:button>
        </div>

        <div>
            <flux:button wire:click="create" variant="primary">
                + Nuevo Mantenimiento
            </flux:button>
        </div>

    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr class="border-b border-zinc-200 dark:border-zinc-700">
                    <th scope="col" class="px-4 py-3 text-left text-sm font-medium cursor-pointer" wire:click="sortBy('fecha_programada')">
                        <div class="flex items-center gap-1">
                            {{ __('Programada') }}
                            @if ($sortBy === 'fecha_programada')
                                <span class="text-xs">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </div>
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-sm font-medium cursor-pointer" wire:click="sortBy('fecha_realizada')">
                        <div class="flex items-center gap-1">
                            {{ __('Realizada') }}
                            @if ($sortBy === 'fecha_realizada')
                                <span class="text-xs">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </div>
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-sm font-medium">
                        {{ __('Vehículo') }}
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-sm font-medium cursor-pointer" wire:click="sortBy('tipo')">
                        <div class="flex items-center gap-1">
                            {{ __('Tipo') }}
                            @if ($sortBy === 'tipo')
                                <span class="text-xs">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </div>
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-sm font-medium cursor-pointer" wire:click="sortBy('costo')">
                        <div class="flex items-center gap-1">
                            {{ __('Costo') }}
                            @if ($sortBy === 'costo')
                                <span class="text-xs">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </div>
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-sm font-medium cursor-pointer" wire:click="sortBy('kilometraje_mantenimiento')">
                        <div class="flex items-center gap-1">
                            {{ __('Kilometraje') }}
                            @if ($sortBy === 'kilometraje_mantenimiento')
                                <span class="text-xs">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </div>
                    </th>
                    <th scope="col" class="px-4 py-3 text-center text-sm font-medium">
                        {{ __('Estado') }}
                    </th>
                    <th scope="col" class="px-4 py-3 text-right text-sm font-medium">
                        {{ __('Acciones') }}
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse ($mantenimientos as $mantenimiento)
                    <tr class="border-b border-zinc-200 dark:border-zinc-700" wire:key="mantenimiento-{{ $mantenimiento->id }}">
                        <td class="px-4 py-2">{{ optional($mantenimiento->fecha_programada)->format('d/m/Y') }}</td>
                        <td class="px-4 py-2">{{ optional($mantenimiento->fecha_realizada)->format('d/m/Y') ?: 'N/A' }}</td>
                        <td class="px-4 py-2 font-medium">
                            {{ $mantenimiento->vehiculo?->placa }}
                            @if($mantenimiento->vehiculo?->marca)
                                <span class="text-gray-500">({{ $mantenimiento->vehiculo->marca }})</span>
                            @endif
                        </td>
                        <td class="px-4 py-2">{{ ucfirst($mantenimiento->tipo) }}</td>
                        <td class="px-4 py-2">${{ number_format((float) $mantenimiento->costo, 2, '.', ',') }}</td>
                        <td class="px-4 py-2">{{ number_format($mantenimiento->kilometraje_mantenimiento, 0, ',', '.') }} km</td>
                        <td class="px-4 py-2 text-center">
                            @php
                                $color = $mantenimiento->fecha_realizada ? 'green' : 'yellow';
                                $label = $mantenimiento->fecha_realizada ? 'Realizado' : 'Pendiente';
                            @endphp
                            <flux:badge size="sm" :color="$color">
                                {{ $label }}
                            </flux:badge>
                        </td>
                        <td class="px-4 py-2">
                            <div class="flex justify-end gap-2">
                                <flux:dropdown>
                                    <flux:button icon:trailing="chevron-down" size="sm">Acciones</flux:button>
                                    <flux:menu>
                                        <flux:menu.item wire:click="edit({{ $mantenimiento->id }})">Editar</flux:menu.item>
                                        <flux:menu.item wire:click="confirmDelete({{ $mantenimiento->id }})">Eliminar</flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="border-b border-zinc-200 dark:border-zinc-700">
                        <td colspan="8" class="px-4 py-6 text-center text-sm text-gray-500">
                            {{ __('No hay mantenimientos registrados.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $mantenimientos->links() }}
    </div>

    @if($modalOpen)
    <flux:modal name="mantenimiento-modal" class="md:w-3xl" wire:model="modalOpen">
        <div class="space-y-4 p-4">
            <div class="flex justify-between items-center">
                <flux:heading size="lg">{{ $editingId ? 'Editar Mantenimiento' : 'Nuevo Mantenimiento' }}</flux:heading>
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
                    <flux:label>Tipo *</flux:label>
                    <flux:select wire:model="form.tipo" required>
                        <option value="preventivo">Preventivo</option>
                        <option value="correctivo">Correctivo</option>
                    </flux:select>
                    <flux:error name="form.tipo" />
                </div>

                <div>
                    <flux:label>Fecha Programada *</flux:label>
                    <flux:input type="date" wire:model="form.fecha_programada" required />
                    <flux:error name="form.fecha_programada" />
                </div>

                <div>
                    <flux:label>Fecha Realizada *</flux:label>
                    <flux:input type="date" wire:model="form.fecha_realizada" required />
                    <flux:error name="form.fecha_realizada" />
                </div>

                <div>
                    <flux:label>Costo *</flux:label>
                    <flux:input type="number" step="0.01" wire:model="form.costo" placeholder="0.00" required />
                    <flux:error name="form.costo" />
                </div>

                <div>
                    <flux:label>Kilometraje *</flux:label>
                    <flux:input type="number" wire:model="form.kilometraje_mantenimiento" placeholder="0" required />
                    <flux:error name="form.kilometraje_mantenimiento" />
                </div>

                <div>
                    <flux:label>Taller *</flux:label>
                    <flux:input wire:model="form.taller" placeholder="Nombre del taller" required />
                    <flux:error name="form.taller" />
                </div>

                <div>
                    <flux:label>Factura *</flux:label>
                    <flux:input wire:model="form.factura" placeholder="FAC-0001" required />
                    <flux:error name="form.factura" />
                </div>

                <div class="col-span-2">
                    <flux:label>Descripción *</flux:label>
                    <flux:textarea wire:model="form.descripcion" rows="3" placeholder="Detalle del mantenimiento" required />
                    <flux:error name="form.descripcion" />
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
                        ¿Estás seguro de que deseas eliminar este mantenimiento?<br>
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
