<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mantenimiento extends Model
{
    // =========================================================================
    // CONFIGURACIÓN BASE
    // =========================================================================
    protected $table = 'mantenimientos';
    
    // =========================================================================
    // ASIGNACIÓN MASIVA
    // =========================================================================
    protected $fillable = [
        'vehiculo_id',
        'fecha_programada',
        'fecha_realizada',
        'tipo',
        'costo',
        'taller',
        'descripcion',
        'kilometraje_mantenimiento',
        'factura'
    ];
    
    // =========================================================================
    // CASTS
    // =========================================================================
    // Las fechas se manipulan como date y el costo como decimal de 2 posiciones
    protected $casts = [
        'fecha_programada' => 'date',
        'fecha_realizada' => 'date',
        'costo' => 'decimal:2',
        'kilometraje_mantenimiento' => 'integer'
    ];
    
    /**
     * vehiculo() - Relación inversa hacia el vehículo del mantenimiento
     */
    public function vehiculo(): BelongsTo
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_id');
    }
    
    /**
     * getRealizadoAttribute() - Indica si el mantenimiento ya fue realizado
     *
     * Se interpreta como realizado cuando fecha_realizada tiene valor.
     */
    public function getRealizadoAttribute(): bool
    {
        return !is_null($this->fecha_realizada);
    }
}
