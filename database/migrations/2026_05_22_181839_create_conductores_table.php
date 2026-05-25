<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('conductores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('numero_licencia', 20)->unique();
            $table->date('vencimiento_licencia');
            $table->string('telefono', 20)->nullable();
            $table->date('fecha_contratacion');
            $table->enum('estado', ['activo', 'suspendido', 'inactivo'])->default('activo');
            $table->timestamps();
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conductores');
    }
};
