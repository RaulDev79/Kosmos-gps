<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conductor extends Model
{
    // =========================================================================
    // CONFIGURACIÓN BASE
    // =========================================================================
    protected $table = 'conductores';
    
    // =========================================================================
    // ASIGNACIÓN MASIVA
    // =========================================================================
    protected $fillable = [
        'nombre',
        'numero_licencia',
        'vencimiento_licencia',
        'telefono',
        'fecha_contratacion',
        'estado'
    ];
    
    // =========================================================================
    // CASTS
    // =========================================================================
    // Las fechas se transforman para poder manipularse como objetos date
    protected $casts = [
        'vencimiento_licencia' => 'date',
        'fecha_contratacion' => 'date'
    ];
    
    /**
     * viajes() - Relación uno a muchos con viajes asignados al conductor
     */
    public function viajes(): HasMany
    {
        return $this->hasMany(Viaje::class, 'conductor_id');
    }
    
    /**
     * registrosCombustible() - Relación uno a muchos con combustible asociado
     */
    public function registrosCombustible(): HasMany
    {
        return $this->hasMany(RegistroCombustible::class, 'conductor_id');
    }
}
