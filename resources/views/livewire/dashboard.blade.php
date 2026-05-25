<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <!-- Filtros -->
    <div class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
        <div class="grid gap-3 md:grid-cols-4">
            <div>
                <label class="block text-xs font-medium text-neutral-500 mb-1">Fecha inicio</label>
                <input type="date" wire:model.live="startDate" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-600 dark:bg-neutral-800">
            </div>
            <div>
                <label class="block text-xs font-medium text-neutral-500 mb-1">Fecha fin</label>
                <input type="date" wire:model.live="endDate" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-600 dark:bg-neutral-800">
            </div>
            <div>
                <label class="block text-xs font-medium text-neutral-500 mb-1">Vehículo</label>
                <select wire:model.live="vehiculoId" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-600 dark:bg-neutral-800">
                    <option value="">Todos</option>
                    @foreach ($this->vehiculos as $vehiculo)
                        <option value="{{ $vehiculo->id }}">{{ $vehiculo->placa }} - {{ $vehiculo->marca }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-neutral-500 mb-1">Agrupar</label>
                <select wire:model.live="groupBy" class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-600 dark:bg-neutral-800">
                    <option value="day">Día</option>
                    <option value="week">Semana</option>
                    <option value="month">Mes</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Cards 3 columnas -->
    <div class="grid auto-rows-min gap-4 md:grid-cols-3">
        <!-- Card 1: Viajes -->
        <div class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
            <div class="flex items-start justify-between">
                <div class="space-y-1">
                    <p class="text-xs font-medium uppercase tracking-wide text-neutral-500">Viajes</p>
                    <p class="text-3xl font-semibold text-neutral-900 dark:text-neutral-100">
                        {{ number_format($this->totalViajes['count']) }}
                    </p>
                    <p class="text-xs text-neutral-500">{{ number_format($this->totalViajes['km']) }} km recorridos</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2 text-xs">
                <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 font-medium text-blue-700 dark:bg-blue-500/20 dark:text-blue-200">
                    {{ number_format($this->porcentajeCompletados, 1) }}%
                </span>
                <span class="text-neutral-500">viajes completados</span>
            </div>
        </div>

        <!-- Card 2: Combustible -->
        <div class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
            <div class="flex items-start justify-between">
                <div class="space-y-1">
                    <p class="text-xs font-medium uppercase tracking-wide text-neutral-500">Combustible</p>
                    <p class="text-3xl font-semibold text-neutral-900 dark:text-neutral-100">
                        $ {{ number_format($this->totalCombustible['costo'], 2) }}
                    </p>
                    <p class="text-xs text-neutral-500">{{ number_format($this->totalCombustible['litros'], 1) }} litros</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2 text-xs">
                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 font-medium text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200">
                    {{ number_format($this->costoPorKm, 2) }}
                </span>
                <span class="text-neutral-500">costo por km</span>
            </div>
        </div>

        <!-- Card 3: Mantenimientos -->
        <div class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
            <div class="flex items-start justify-between">
                <div class="space-y-1">
                    <p class="text-xs font-medium uppercase tracking-wide text-neutral-500">Mantenimientos</p>
                    <p class="text-3xl font-semibold text-neutral-900 dark:text-neutral-100">
                        {{ number_format($this->totalMantenimientos['count']) }}
                    </p>
                    <p class="text-xs text-neutral-500">$ {{ number_format($this->totalMantenimientos['costo'], 2) }} gastados</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2 text-xs">
                <span class="text-neutral-500">{{ $this->mantenimientosPendientes }} pendientes</span>
            </div>
        </div>
    </div>

    <!-- Gráficos: 2 columnas (barras + circular) -->
    <div class="grid gap-4 lg:grid-cols-3">
        <!-- Gráfico de barras: Viajes por período -->
        <div class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-700 dark:bg-neutral-900 lg:col-span-2">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-neutral-500">Viajes por período</p>
                    <p class="text-sm text-neutral-500">{{ $this->chartLabel }}</p>
                </div>
                <div class="text-xs text-neutral-400">Cantidad</div>
            </div>
            <div class="mt-4 h-48 rounded-lg bg-gradient-to-b from-blue-50 to-transparent dark:from-blue-900/20">
                <div class="relative h-full w-full">
                    <!-- Líneas de grid -->
                    <div class="absolute inset-0 grid grid-rows-4 gap-2 px-2 py-3">
                        <div class="border-b border-dashed border-neutral-200/80 dark:border-neutral-700/60"></div>
                        <div class="border-b border-dashed border-neutral-200/80 dark:border-neutral-700/60"></div>
                        <div class="border-b border-dashed border-neutral-200/80 dark:border-neutral-700/60"></div>
                        <div class="border-b border-dashed border-neutral-200/80 dark:border-neutral-700/60"></div>
                    </div>
                    <!-- Barras -->
                    <div class="absolute inset-x-2 bottom-4 flex items-end justify-between gap-1">
                        @foreach ($this->viajesPorPeriodo['series'] as $index => $point)
                            @php
                                $height = $this->viajesPorPeriodo['max'] > 0
                                    ? max(6, ($point['total'] / $this->viajesPorPeriodo['max']) * 100)
                                    : 6;
                            @endphp
                            <div class="flex w-full flex-col items-center gap-1">
                                <span class="text-[10px] font-medium text-neutral-500">
                                    {{ $point['total'] > 0 ? number_format($point['total'], 0) : '' }}
                                </span>
                                <div class="w-full rounded bg-blue-200/60" style="height: {{ $height }}px"></div>
                                <span class="text-[10px] text-neutral-400">
                                    {{ $loop->index % $this->step === 0 ? $point['label'] : '' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico circular: Kilómetros por vehículo -->
        <div class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-neutral-500">Kilómetros por vehículo</p>
                    <p class="text-sm text-neutral-500">Distribución</p>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-center">
                <div class="relative h-32 w-32 rounded-full" style="background: {{ $this->pieGradient }};">
                    <div class="absolute inset-3 rounded-full bg-white dark:bg-neutral-900"></div>
                </div>
            </div>
            <div class="mt-4 space-y-2 text-xs text-neutral-600 dark:text-neutral-300">
                @forelse ($this->kmPorVehiculo['items'] as $item)
                    <div class="flex items-center justify-between">
                        <span class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full" style="background: {{ $item['color'] }};"></span>
                            {{ $item['label'] }}
                        </span>
                        <span>{{ number_format($item['percentage'], 1) }}%</span>
                    </div>
                @empty
                    <div class="text-center text-neutral-400">Sin datos</div>
                @endforelse
            </div>
        </div>
    </div>
</div>