<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistroCombustible extends Model
{
    // =========================================================================
    // CONFIGURACIÓN BASE
    // =========================================================================
    protected $table = 'registros_combustible';
    
    // =========================================================================
    // ASIGNACIÓN MASIVA
    // =========================================================================
    protected $fillable = [
        'vehiculo_id',
        'conductor_id',
        'fecha',
        'litros',
        'costo_total',
        'kilometraje',
        'estacion_servicio',
        'factura'
    ];
    
    // =========================================================================
    // CASTS
    // =========================================================================
    // Convierte fecha, litros, costo y kilometraje a tipos manejables
    protected $casts = [
        'fecha' => 'date',
        'litros' => 'decimal:2',
        'costo_total' => 'decimal:2',
        'kilometraje' => 'integer'
    ];
    
    /**
     * vehiculo() - Relación inversa hacia el vehículo abastecido
     */
    public function vehiculo(): BelongsTo
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_id');
    }
    
    /**
     * conductor() - Relación inversa hacia el conductor asociado, si existe
     */
    public function conductor(): BelongsTo
    {
        return $this->belongsTo(Conductor::class, 'conductor_id');
    }
    
    /**
     * getCostoPorLitroAttribute() - Calcula el costo unitario por litro
     *
     * Si litros es cero o inválido, retorna 0 para evitar divisiones inválidas.
     */
    public function getCostoPorLitroAttribute(): float
    {
        if ($this->litros > 0) {
            return $this->costo_total / $this->litros;
        }
        return 0;
    }
}
