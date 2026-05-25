<div class="space-y-4">

    <flux:heading size="xl">Viajes</flux:heading>
    <flux:subheading>Listado de viajes operativos</flux:subheading>
    <flux:separator />

    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

        <div class="w-full md:max-w-sm">
            <flux:input
                type="search"
                placeholder="{{ __('Buscar por vehículo, conductor o propósito...') }}"
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
                :variant="$estadoFilter === 'planificado' ? 'primary' : 'ghost'"
                wire:click="$set('estadoFilter', 'planificado')"
            >
                Planificados
            </flux:button>
            <flux:button
                size="sm"
                :variant="$estadoFilter === 'en_curso' ? 'primary' : 'ghost'"
                wire:click="$set('estadoFilter', 'en_curso')"
            >
                En Curso
            </flux:button>
            <flux:button
                size="sm"
                :variant="$estadoFilter === 'completado' ? 'primary' : 'ghost'"
                wire:click="$set('estadoFilter', 'completado')"
            >
                Completados
            </flux:button>
            <flux:button
                size="sm"
                :variant="$estadoFilter === 'cancelado' ? 'primary' : 'ghost'"
                wire:click="$set('estadoFilter', 'cancelado')"
            >
                Cancelados
            </flux:button>
        </div>

        <div>
            <flux:button wire:click="create" variant="primary">
                + Nuevo Viaje
            </flux:button>
        </div>

    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr class="border-b border-zinc-200 dark:border-zinc-700">
                    <th scope="col" class="px-4 py-3 text-left text-sm font-medium cursor-pointer" wire:click="sortBy('fecha_hora_inicio')">
                        <div class="flex items-center gap-1">
                            {{ __('Inicio') }}
                            @if ($sortBy === 'fecha_hora_inicio')
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
                    <th scope="col" class="px-4 py-3 text-left text-sm font-medium cursor-pointer" wire:click="sortBy('proposito')">
                        <div class="flex items-center gap-1">
                            {{ __('Propósito') }}
                            @if ($sortBy === 'proposito')
                                <span class="text-xs">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </div>
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-sm font-medium cursor-pointer" wire:click="sortBy('kilometraje_inicio')">
                        <div class="flex items-center gap-1">
                            {{ __('KM Inicio') }}
                            @if ($sortBy === 'kilometraje_inicio')
                                <span class="text-xs">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </div>
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-sm font-medium cursor-pointer" wire:click="sortBy('kilometraje_fin')">
                        <div class="flex items-center gap-1">
                            {{ __('KM Fin') }}
                            @if ($sortBy === 'kilometraje_fin')
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
                @forelse ($viajes as $viaje)
                    <tr class="border-b border-zinc-200 dark:border-zinc-700" wire:key="viaje-{{ $viaje->id }}">
                        <td class="px-4 py-2">{{ optional($viaje->fecha_hora_inicio)->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-2 font-medium">
                            {{ $viaje->vehiculo?->placa }}
                            @if($viaje->vehiculo?->marca)
                                <span class="text-gray-500">({{ $viaje->vehiculo->marca }})</span>
                            @endif
                        </td>
                        <td class="px-4 py-2">{{ $viaje->conductor?->nombre }}</td>
                        <td class="px-4 py-2">{{ $viaje->proposito }}</td>
                        <td class="px-4 py-2">{{ number_format($viaje->kilometraje_inicio, 0, ',', '.') }} km</td>
                        <td class="px-4 py-2">
                            {{ $viaje->kilometraje_fin !== null ? number_format($viaje->kilometraje_fin, 0, ',', '.') . ' km' : 'N/A' }}
                        </td>
                        <td class="px-4 py-2 text-center">
                            @php
                                $color = match($viaje->estado) {
                                    'planificado' => 'blue',
                                    'en_curso' => 'yellow',
                                    'completado' => 'green',
                                    default => 'red',
                                };
                            @endphp
                            <flux:badge size="sm" :color="$color">
                                {{ ucfirst(str_replace('_', ' ', $viaje->estado)) }}
                            </flux:badge>
                        </td>
                        <td class="px-4 py-2">
                            <div class="flex justify-end gap-2">
                                <flux:dropdown>
                                    <flux:button icon:trailing="chevron-down" size="sm">Acciones</flux:button>
                                    <flux:menu>
                                        <flux:menu.item wire:click="edit({{ $viaje->id }})">Editar</flux:menu.item>
                                        <flux:menu.item wire:click="confirmDelete({{ $viaje->id }})">Eliminar</flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="border-b border-zinc-200 dark:border-zinc-700">
                        <td colspan="8" class="px-4 py-6 text-center text-sm text-gray-500">
                            {{ __('No hay viajes registrados.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $viajes->links() }}
    </div>

    @if($modalOpen)
    <flux:modal name="viaje-modal" class="md:w-3xl" wire:model="modalOpen">
        <div class="space-y-4 p-4">
            <div class="flex justify-between items-center">
                <flux:heading size="lg">{{ $editingId ? 'Editar Viaje' : 'Nuevo Viaje' }}</flux:heading>
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
                    <flux:label>Fecha y Hora de Inicio *</flux:label>
                    <flux:input type="datetime-local" wire:model="form.fecha_hora_inicio" required />
                    <flux:error name="form.fecha_hora_inicio" />
                </div>

                <div>
                    <flux:label>Fecha y Hora de Fin *</flux:label>
                    <flux:input type="datetime-local" wire:model="form.fecha_hora_fin" required />
                    <flux:error name="form.fecha_hora_fin" />
                </div>

                <div>
                    <flux:label>Kilometraje Inicio *</flux:label>
                    <flux:input type="number" wire:model="form.kilometraje_inicio" placeholder="0" required />
                    <flux:error name="form.kilometraje_inicio" />
                </div>

                <div>
                    <flux:label>Kilometraje Fin *</flux:label>
                    <flux:input type="number" wire:model="form.kilometraje_fin" placeholder="0" required />
                    <flux:error name="form.kilometraje_fin" />
                </div>

                <div class="col-span-2">
                    <flux:label>Propósito *</flux:label>
                    <flux:textarea wire:model="form.proposito" rows="3" placeholder="Describe el propósito del viaje" required />
                    <flux:error name="form.proposito" />
                </div>

                <div>
                    <flux:label>Estado *</flux:label>
                    <flux:select wire:model="form.estado" required>
                        <option value="planificado">Planificado</option>
                        <option value="en_curso">En Curso</option>
                        <option value="completado">Completado</option>
                        <option value="cancelado">Cancelado</option>
                    </flux:select>
                    <flux:error name="form.estado" />
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
                        ¿Estás seguro de que deseas eliminar este viaje?<br>
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
