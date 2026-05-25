<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehiculo extends Model
{
    // =========================================================================
    // CONFIGURACIÓN BASE
    // =========================================================================
    // Se especifica manualmente el nombre de la tabla porque el dominio
    // del proyecto trabaja con nombres en español.
    protected $table = 'vehiculos';
    
    // =========================================================================
    // ASIGNACIÓN MASIVA
    // =========================================================================
    // Campos que pueden poblarse mediante create() o update()
    protected $fillable = [
        'placa',
        'marca',
        'modelo',
        'anio',
        'vin',
        'numero_motor',
        'tipo',
        'estado',
        'fecha_compra',
        'kilometraje_actual'
    ];
    
    // =========================================================================
    // CASTS
    // =========================================================================
    // Convierte automáticamente ciertos atributos a tipos útiles para PHP
    protected $casts = [
        'anio' => 'integer',
        'fecha_compra' => 'date',
        'kilometraje_actual' => 'integer'
    ];
    
    /**
     * viajes() - Relación uno a muchos con los viajes del vehículo
     */
    public function viajes(): HasMany
    {
        return $this->hasMany(Viaje::class, 'vehiculo_id');
    }
    
    /**
     * mantenimientos() - Relación uno a muchos con mantenimientos del vehículo
     */
    public function mantenimientos(): HasMany
    {
        return $this->hasMany(Mantenimiento::class, 'vehiculo_id');
    }
    
    /**
     * registrosCombustible() - Relación uno a muchos con cargas de combustible
     */
    public function registrosCombustible(): HasMany
    {
        return $this->hasMany(RegistroCombustible::class, 'vehiculo_id');
    }
}
