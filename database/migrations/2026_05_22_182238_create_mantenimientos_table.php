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
        Schema::create('mantenimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehiculo_id')->constrained('vehiculos')->onDelete('cascade');
            $table->date('fecha_programada');
            $table->date('fecha_realizada')->nullable();
            $table->enum('tipo', ['preventivo', 'correctivo']);
            $table->decimal('costo', 10, 2);
            $table->string('taller', 100);
            $table->text('descripcion')->nullable();
            $table->integer('kilometraje_mantenimiento');
            $table->string('factura', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mantenimientos');
    }
};
