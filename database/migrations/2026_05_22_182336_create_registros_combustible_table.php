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
        Schema::create('registros_combustible', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehiculo_id')->constrained('vehiculos')->onDelete('cascade');
            $table->foreignId('conductor_id')->nullable()->constrained('conductores')->onDelete('set null');
            $table->date('fecha');
            $table->decimal('litros', 8, 2);
            $table->decimal('costo_total', 10, 2);
            $table->integer('kilometraje');
            $table->string('estacion_servicio', 100)->nullable();
            $table->string('factura', 100)->nullable();
            $table->timestamps();
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registros_combustible');
    }
};
