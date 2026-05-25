<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
      public function up(): void
      {
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->id();
            $table->string('placa', 10)->unique();
            $table->string('marca', 50);
            $table->string('modelo', 50);
            $table->integer('anio');
            $table->string('vin', 17)->nullable(); // número de chasis
            $table->string('numero_motor', 30)->nullable();
            $table->enum('tipo', ['camion', 'furgoneta', 'autobus', 'camioneta']);
            $table->enum('estado', ['activo', 'mantenimiento', 'inactivo'])->default('activo');
            $table->date('fecha_compra')->nullable();
            $table->integer('kilometraje_actual')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehiculos');
    }
};
