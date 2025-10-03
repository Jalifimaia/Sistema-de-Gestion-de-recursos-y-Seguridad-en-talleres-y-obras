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
    Schema::table('users', function (Blueprint $table) {
        $table->unsignedBigInteger('id_rol')->nullable()->after('password');
        $table->unsignedBigInteger('usuario_creacion')->nullable()->after('id_rol');
        $table->unsignedBigInteger('usuario_modificacion')->nullable()->after('usuario_creacion');
    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['id_rol', 'usuario_creacion', 'usuario_modificacion']);
    });
}

};
