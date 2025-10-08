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
     Schema::create('incidentes', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('id_recurso');
    $table->unsignedBigInteger('id_estado');
    $table->unsignedBigInteger('id_supervisor');
    $table->string('descripcion', 250)->nullable();
    $table->datetime('fecha_incidente');
    $table->datetime('fecha_cierre_incidente')->nullable();
    $table->string('resolucion', 250)->nullable();
    $table->text('detalle')->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidentes');
    }
};
