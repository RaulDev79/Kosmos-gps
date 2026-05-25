<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Viaje extends Model
{
    // =========================================================================
    // CONFIGURACIÓN BASE
    // =========================================================================
    protected $table = 'viajes';
    
    // =========================================================================
    // ASIGNACIÓN MASIVA
    // =========================================================================
    // Incluye tanto datos operativos como llaves foráneas de relación
    protected $fillable = [
        'vehiculo_id',
        'conductor_id',
        'fecha_hora_inicio',
        'fecha_hora_fin',
        'kilometraje_inicio',
        'kilometraje_fin',
        'proposito',
        'estado'
    ];
    
    // =========================================================================
    // CASTS
    // =========================================================================
    // Convierte fechas y kilometrajes a tipos apropiados en PHP
    protected $casts = [
        'fecha_hora_inicio' => 'datetime',
        'fecha_hora_fin' => 'datetime',
        'kilometraje_inicio' => 'integer',
        'kilometraje_fin' => 'integer'
    ];
    
    /**
     * vehiculo() - Relación inversa hacia el vehículo asignado al viaje
     */
    public function vehiculo(): BelongsTo
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_id');
    }
    
    /**
     * conductor() - Relación inversa hacia el conductor asignado al viaje
     */
    public function conductor(): BelongsTo
    {
        return $this->belongsTo(Conductor::class, 'conductor_id');
    }
    
    /**
     * getKilometrosRecorridosAttribute() - Calcula kilómetros recorridos
     *
     * Solo retorna un valor si existen kilometraje inicial y final.
     * Si el viaje no ha sido cerrado, retorna null.
     */
    public function getKilometrosRecorridosAttribute()
    {
        if ($this->kilometraje_fin && $this->kilometraje_inicio) {
            return $this->kilometraje_fin - $this->kilometraje_inicio;
        }
        return null;
    }
}
