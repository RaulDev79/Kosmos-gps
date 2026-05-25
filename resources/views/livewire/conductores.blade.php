<div class="space-y-4">

    <flux:heading size="xl">Conductores</flux:heading>
    <flux:subheading>Listado de conductores registrados</flux:subheading>
    <flux:separator />

    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

        <div class="w-full md:max-w-sm">
            <flux:input
                type="search"
                placeholder="{{ __('Buscar por nombre, licencia o teléfono...') }}"
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
                :variant="$estadoFilter === 'activo' ? 'primary' : 'ghost'"
                wire:click="$set('estadoFilter', 'activo')"
            >
                Activos
            </flux:button>
            <flux:button
                size="sm"
                :variant="$estadoFilter === 'suspendido' ? 'primary' : 'ghost'"
                wire:click="$set('estadoFilter', 'suspendido')"
            >
                Suspendidos
            </flux:button>
            <flux:button
                size="sm"
                :variant="$estadoFilter === 'inactivo' ? 'primary' : 'ghost'"
                wire:click="$set('estadoFilter', 'inactivo')"
            >
                Inactivos
            </flux:button>
        </div>

        <div>
            <flux:button wire:click="create" variant="primary">
                + Nuevo Conductor
            </flux:button>
        </div>

    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr class="border-b border-zinc-200 dark:border-zinc-700">
                    <th scope="col" class="px-4 py-3 text-left text-sm font-medium cursor-pointer" wire:click="sortBy('nombre')">
                        <div class="flex items-center gap-1">
                            {{ __('Nombre') }}
                            @if ($sortBy === 'nombre')
                                <span class="text-xs">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </div>
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-sm font-medium cursor-pointer" wire:click="sortBy('numero_licencia')">
                        <div class="flex items-center gap-1">
                            {{ __('Licencia') }}
                            @if ($sortBy === 'numero_licencia')
                                <span class="text-xs">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </div>
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-sm font-medium cursor-pointer" wire:click="sortBy('telefono')">
                        <div class="flex items-center gap-1">
                            {{ __('Teléfono') }}
                            @if ($sortBy === 'telefono')
                                <span class="text-xs">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </div>
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-sm font-medium cursor-pointer" wire:click="sortBy('vencimiento_licencia')">
                        <div class="flex items-center gap-1">
                            {{ __('Vence Licencia') }}
                            @if ($sortBy === 'vencimiento_licencia')
                                <span class="text-xs">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </div>
                    </th>
                    <th scope="col" class="px-4 py-3 text-left text-sm font-medium cursor-pointer" wire:click="sortBy('fecha_contratacion')">
                        <div class="flex items-center gap-1">
                            {{ __('Contratación') }}
                            @if ($sortBy === 'fecha_contratacion')
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
                @forelse ($conductores as $conductor)
                    <tr class="border-b border-zinc-200 dark:border-zinc-700" wire:key="conductor-{{ $conductor->id }}">
                        <td class="px-4 py-2 font-medium">{{ $conductor->nombre }}</td>
                        <td class="px-4 py-2">{{ $conductor->numero_licencia }}</td>
                        <td class="px-4 py-2">{{ $conductor->telefono ?: 'N/A' }}</td>
                        <td class="px-4 py-2">{{ optional($conductor->vencimiento_licencia)->format('d/m/Y') }}</td>
                        <td class="px-4 py-2">{{ optional($conductor->fecha_contratacion)->format('d/m/Y') }}</td>
                        <td class="px-4 py-2 text-center">
                            @php
                                $color = $conductor->estado === 'activo' ? 'green' : ($conductor->estado === 'suspendido' ? 'yellow' : 'red');
                            @endphp
                            <flux:badge size="sm" :color="$color">
                                {{ ucfirst($conductor->estado) }}
                            </flux:badge>
                        </td>
                        <td class="px-4 py-2">
                            <div class="flex justify-end gap-2">
                                <flux:dropdown>
                                    <flux:button icon:trailing="chevron-down" size="sm">Acciones</flux:button>
                                    <flux:menu>
                                        <flux:menu.item wire:click="edit({{ $conductor->id }})">Editar</flux:menu.item>
                                        <flux:menu.item wire:click="confirmDelete({{ $conductor->id }})">Eliminar</flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="border-b border-zinc-200 dark:border-zinc-700">
                        <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">
                            {{ __('No hay conductores registrados.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $conductores->links() }}
    </div>

    @if($modalOpen)
    <flux:modal name="conductor-modal" class="md:w-2xl" wire:model="modalOpen">
        <div class="space-y-4 p-4">
            <div class="flex justify-between items-center">
                <flux:heading size="lg">{{ $editingId ? 'Editar Conductor' : 'Nuevo Conductor' }}</flux:heading>
            </div>

            <flux:separator />

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:label>Nombre *</flux:label>
                    <flux:input wire:model="form.nombre" placeholder="Nombre completo" required />
                    <flux:error name="form.nombre" />
                </div>

                <div>
                    <flux:label>Número de Licencia *</flux:label>
                    <flux:input wire:model="form.numero_licencia" placeholder="LIC-123456" required />
                    <flux:error name="form.numero_licencia" />
                </div>

                <div>
                    <flux:label>Vencimiento de Licencia *</flux:label>
                    <flux:input type="date" wire:model="form.vencimiento_licencia" required />
                    <flux:error name="form.vencimiento_licencia" />
                </div>

                <div>
                    <flux:label>Teléfono *</flux:label>
                    <flux:input wire:model="form.telefono" placeholder="555-123-4567" required />
                    <flux:error name="form.telefono" />
                </div>

                <div>
                    <flux:label>Fecha de Contratación *</flux:label>
                    <flux:input type="date" wire:model="form.fecha_contratacion" required />
                    <flux:error name="form.fecha_contratacion" />
                </div>

                <div>
                    <flux:label>Estado *</flux:label>
                    <flux:select wire:model="form.estado" required>
                        <option value="activo">Activo</option>
                        <option value="suspendido">Suspendido</option>
                        <option value="inactivo">Inactivo</option>
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
                        ¿Estás seguro de que deseas eliminar este conductor?<br>
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
