<?php

namespace App\Livewire;

use App\Models\Mantenimiento;
use App\Models\RegistroCombustible;
use App\Models\Vehiculo;
use App\Models\Viaje;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Livewire\Component;

class Dashboard extends Component
{
    // =========================================================================
    // FILTROS DEL DASHBOARD
    // =========================================================================
    // startDate / endDate: rango de fechas para todas las métricas
    // vehiculoId: filtro opcional por vehículo
    // groupBy: criterio temporal para agrupar la serie de viajes
    public $startDate = '';

    public $endDate = '';

    public $vehiculoId = '';

    public $groupBy = 'month';

    // =========================================================================
    // MÉTRICAS Y ESTRUCTURAS DE PRESENTACIÓN
    // =========================================================================
    // Estas propiedades alimentan cards, gráfico de barras y gráfico circular
    public $totalViajes = ['count' => 0, 'km' => 0];

    public $totalCombustible = ['costo' => 0, 'litros' => 0];

    public $totalMantenimientos = ['count' => 0, 'costo' => 0];

    public $porcentajeCompletados = 0;

    public $costoPorKm = 0;

    public $mantenimientosPendientes = 0;

    public $viajesPorPeriodo = ['series' => [], 'max' => 0];

    public $kmPorVehiculo = ['items' => []];

    public $pieGradient = 'conic-gradient(#d4d4d8 0% 100%)';

    public $chartLabel = '';

    public $step = 1;

    // =========================================================================
    // CATÁLOGOS DE APOYO
    // =========================================================================
    // Se usan en el filtro de vehículo del dashboard
    public $vehiculos = [];

    /**
     * mount() - Carga catálogos base e inicializa el rango de fechas
     */
    public function mount()
    {
        $this->vehiculos = Vehiculo::orderBy('placa')->get();
        $this->initializeDateRange();
    }

    /**
     * updatedStartDate() - Reacciona a cambios en la fecha inicial
     */
    public function updatedStartDate()
    {
        $this->normalizeDates();
    }

    /**
     * updatedEndDate() - Reacciona a cambios en la fecha final
     */
    public function updatedEndDate()
    {
        $this->normalizeDates();
    }

    /**
     * normalizeDates() - Evita que el rango quede invertido
     *
     * Si el usuario selecciona una fecha inicial mayor a la final,
     * la fecha final se ajusta automáticamente.
     */
    protected function normalizeDates(): void
    {
        if ($this->startDate && $this->endDate && $this->startDate > $this->endDate) {
            $this->endDate = $this->startDate;
        }
    }

    /**
     * initializeDateRange() - Define el rango inicial del dashboard
     *
     * Busca la fecha más antigua disponible entre viajes, combustible
     * y mantenimientos para que el dashboard cargue histórico real
     * en lugar de limitarse al mes actual.
     */
    protected function initializeDateRange(): void
    {
        $dates = array_filter([
            Viaje::min('fecha_hora_inicio'),
            RegistroCombustible::min('fecha'),
            Mantenimiento::min('fecha_programada'),
            Mantenimiento::whereNotNull('fecha_realizada')->min('fecha_realizada'),
        ]);

        $this->startDate = empty($dates)
            ? now()->startOfMonth()->format('Y-m-d')
            : Carbon::parse(min($dates))->format('Y-m-d');

        $this->endDate = now()->format('Y-m-d');
    }

    /**
     * refreshDashboardData() - Calcula todas las métricas visibles del dashboard
     *
     * Aquí se construyen las consultas base filtradas por fecha y vehículo,
     * y luego se derivan:
     * - cards de viajes, combustible y mantenimientos
     * - porcentaje completado
     * - costo por kilómetro
     * - mantenimientos pendientes
     * - serie temporal de viajes
     * - distribución de kilómetros por vehículo
     */
    protected function refreshDashboardData(): void
    {
        $this->vehiculos = Vehiculo::orderBy('placa')->get();

        // Consulta base para viajes dentro del rango seleccionado
        $viajesBase = Viaje::query()
            ->when($this->vehiculoId, fn ($query) => $query->where('vehiculo_id', $this->vehiculoId))
            ->whereBetween('fecha_hora_inicio', [
                $this->startBoundary(),
                $this->endBoundary(),
            ]);

        // Consulta base para combustible dentro del rango seleccionado
        $combustibleBase = RegistroCombustible::query()
            ->when($this->vehiculoId, fn ($query) => $query->where('vehiculo_id', $this->vehiculoId))
            ->whereBetween('fecha', [$this->startDate, $this->endDate]);

        // Consulta base para mantenimientos programados o realizados dentro del rango
        $mantenimientosBase = Mantenimiento::query()
            ->when($this->vehiculoId, fn ($query) => $query->where('vehiculo_id', $this->vehiculoId))
            ->where(function ($query) {
                $query->whereBetween('fecha_programada', [$this->startDate, $this->endDate])
                    ->orWhereBetween('fecha_realizada', [$this->startDate, $this->endDate]);
            });

        $viajesStats = (clone $viajesBase)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN kilometraje_fin IS NOT NULL AND kilometraje_fin >= kilometraje_inicio THEN kilometraje_fin - kilometraje_inicio ELSE 0 END) as km')
            ->selectRaw("SUM(CASE WHEN estado = 'completado' THEN 1 ELSE 0 END) as completados")
            ->first();

        // Se agregan costo y litros totales de combustible
        $combustibleStats = (clone $combustibleBase)
            ->selectRaw('COALESCE(SUM(costo_total), 0) as costo')
            ->selectRaw('COALESCE(SUM(litros), 0) as litros')
            ->first();

        // Se agregan cantidad y costo acumulado de mantenimientos
        $mantenimientosStats = (clone $mantenimientosBase)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COALESCE(SUM(costo), 0) as costo')
            ->first();

        // Pendiente = mantenimiento sin fecha_realizada y vencido o vigente hasta el fin del rango
        $pendientes = Mantenimiento::query()
            ->when($this->vehiculoId, fn ($query) => $query->where('vehiculo_id', $this->vehiculoId))
            ->whereNull('fecha_realizada')
            ->whereDate('fecha_programada', '<=', $this->endDate)
            ->count();

        $totalViajes = (int) ($viajesStats->total ?? 0);
        $kmRecorridos = (float) ($viajesStats->km ?? 0);
        $completados = (int) ($viajesStats->completados ?? 0);

        $this->totalViajes = [
            'count' => $totalViajes,
            'km' => $kmRecorridos,
        ];

        $this->totalCombustible = [
            'costo' => (float) ($combustibleStats->costo ?? 0),
            'litros' => (float) ($combustibleStats->litros ?? 0),
        ];

        $this->totalMantenimientos = [
            'count' => (int) ($mantenimientosStats->total ?? 0),
            'costo' => (float) ($mantenimientosStats->costo ?? 0),
        ];

        $this->porcentajeCompletados = $totalViajes > 0
            ? round(($completados / $totalViajes) * 100, 1)
            : 0;

        $this->costoPorKm = $kmRecorridos > 0
            ? round($this->totalCombustible['costo'] / $kmRecorridos, 2)
            : 0;

        $this->mantenimientosPendientes = $pendientes;

        // Se construyen los insumos de ambos gráficos
        $this->buildViajesPorPeriodo(clone $viajesBase);
        $this->buildKmPorVehiculo(clone $viajesBase);
    }

    /**
     * buildViajesPorPeriodo() - Genera la serie temporal del gráfico de barras
     *
     * @param  Builder  $query  Consulta base de viajes
     */
    protected function buildViajesPorPeriodo($query): void
    {
        $series = collect();

        if ($this->groupBy === 'day') {
            // Agrupa por fecha exacta
            $series = $query
                ->selectRaw('DATE(fecha_hora_inicio) as period')
                ->selectRaw('COUNT(*) as total')
                ->groupBy('period')
                ->orderBy('period')
                ->get()
                ->map(function ($row) {
                    return [
                        'label' => Carbon::parse($row->period)->format('d M'),
                        'total' => (int) $row->total,
                    ];
                });
        } elseif ($this->groupBy === 'week') {
            // Agrupa por semana ISO dentro del año
            $series = $query
                ->selectRaw("CAST(strftime('%Y', fecha_hora_inicio) AS INTEGER) as year_number")
                ->selectRaw("CAST(strftime('%W', fecha_hora_inicio) AS INTEGER) as week_number")
                ->selectRaw('MIN(DATE(fecha_hora_inicio)) as period_start')
                ->selectRaw('COUNT(*) as total')
                ->groupBy('year_number', 'week_number')
                ->orderBy('year_number')
                ->orderBy('week_number')
                ->get()
                ->map(function ($row) {
                    return [
                        'label' => 'Sem '.$row->week_number,
                        'total' => (int) $row->total,
                    ];
                });
        } else {
            // Agrupa por mes
            $series = $query
                ->selectRaw("CAST(strftime('%Y', fecha_hora_inicio) AS INTEGER) as year_number")
                ->selectRaw("CAST(strftime('%m', fecha_hora_inicio) AS INTEGER) as month_number")
                ->selectRaw('COUNT(*) as total')
                ->groupBy('year_number', 'month_number')
                ->orderBy('year_number')
                ->orderBy('month_number')
                ->get()
                ->map(function ($row) {
                    $date = Carbon::createFromDate($row->year_number, $row->month_number, 1);

                    return [
                        'label' => $date->translatedFormat('M Y'),
                        'total' => (int) $row->total,
                    ];
                });
        }

        // max se usa para escalar visualmente las barras
        $this->viajesPorPeriodo = [
            'series' => $series->values()->all(),
            'max' => max(1, (int) $series->max('total')),
        ];

        $this->chartLabel = $this->buildChartLabel();
        $this->step = $this->calculateChartStep($series->count());
    }

    /**
     * buildKmPorVehiculo() - Calcula la distribución porcentual de kilómetros
     *
     * @param  Builder  $query  Consulta base de viajes
     */
    protected function buildKmPorVehiculo($query): void
    {
        $colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#06b6d4', '#8b5cf6', '#f97316', '#22c55e'];

        // Se suman kilómetros recorridos por cada vehículo a partir de viajes cerrados
        $rows = $query
            ->with('vehiculo')
            ->selectRaw('vehiculo_id')
            ->selectRaw('SUM(CASE WHEN kilometraje_fin IS NOT NULL AND kilometraje_fin >= kilometraje_inicio THEN kilometraje_fin - kilometraje_inicio ELSE 0 END) as km')
            ->groupBy('vehiculo_id')
            ->havingRaw('km > 0')
            ->orderByDesc('km')
            ->get();

        $totalKm = (float) $rows->sum('km');

        $items = $rows->values()->map(function ($row, $index) use ($colors, $totalKm) {
            $vehiculo = $row->vehiculo;
            $label = $vehiculo
                ? trim($vehiculo->placa.' ('.$vehiculo->marca.')')
                : 'Vehículo #'.$row->vehiculo_id;

            return [
                'label' => $label,
                'percentage' => $totalKm > 0 ? round((((float) $row->km) / $totalKm) * 100, 1) : 0,
                'color' => $colors[$index % count($colors)],
            ];
        })->all();

        $this->kmPorVehiculo = ['items' => $items];
        $this->pieGradient = $this->buildPieGradient($items);
    }

    /**
     * buildPieGradient() - Construye el CSS del gráfico circular
     *
     * @param  array  $items  Segmentos con color y porcentaje
     */
    protected function buildPieGradient(array $items): string
    {
        if (empty($items)) {
            return 'conic-gradient(#d4d4d8 0% 100%)';
        }

        $start = 0.0;
        $segments = [];

        foreach ($items as $index => $item) {
            $end = $index === array_key_last($items) ? 100.0 : min(100.0, $start + (float) $item['percentage']);
            $segments[] = "{$item['color']} {$start}% {$end}%";
            $start = $end;
        }

        return 'conic-gradient('.implode(', ', $segments).')';
    }

    /**
     * buildChartLabel() - Genera el subtítulo descriptivo del gráfico de barras
     */
    protected function buildChartLabel(): string
    {
        $labels = [
            'day' => 'Viajes por día',
            'week' => 'Viajes por semana',
            'month' => 'Viajes por mes',
        ];

        return ($labels[$this->groupBy] ?? 'Viajes por período').' - '.
            Carbon::parse($this->startDate)->format('d/m/Y').' a '.
            Carbon::parse($this->endDate)->format('d/m/Y');
    }

    /**
     * calculateChartStep() - Decide cada cuántas etiquetas mostrar en el eje X
     *
     * @param  int  $count  Cantidad de puntos de la serie
     */
    protected function calculateChartStep(int $count): int
    {
        if ($count <= 8) {
            return 1;
        }

        if ($count <= 16) {
            return 2;
        }

        if ($count <= 24) {
            return 3;
        }

        return 4;
    }

    /**
     * startBoundary() - Retorna el inicio del día para startDate
     */
    protected function startBoundary(): string
    {
        return Carbon::parse($this->startDate)->startOfDay()->toDateTimeString();
    }

    /**
     * endBoundary() - Retorna el final del día para endDate
     */
    protected function endBoundary(): string
    {
        return Carbon::parse($this->endDate)->endOfDay()->toDateTimeString();
    }

    /**
     * render() - Recalcula métricas y retorna la vista del dashboard
     */
    public function render()
    {
        $this->refreshDashboardData();

        return view('livewire.dashboard');
    }
}
