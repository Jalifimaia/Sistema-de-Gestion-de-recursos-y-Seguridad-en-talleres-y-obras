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
    Schema::table('serie_recurso', function (Blueprint $table) {
        // 1. Eliminar la restricción de clave foránea
        $table->dropForeign('serie_recurso_ibfk_2'); // nombre exacto del constraint
    });

    Schema::table('serie_recurso', function (Blueprint $table) {
        // 2. Modificar la columna para que sea nullable
        $table->unsignedBigInteger('id_incidente_detalle')->nullable()->change();
    });

    Schema::table('serie_recurso', function (Blueprint $table) {
        // 3. Volver a agregar la restricción de clave foránea
        $table->foreign('id_incidente_detalle')
              ->references('id')
              ->on('incidente_detalle')
              ->onDelete('set null');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('serie_recurso', function (Blueprint $table) {
            //
        });
    }
};
