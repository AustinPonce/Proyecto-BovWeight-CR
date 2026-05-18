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
       Schema::create('Usuario', function (Blueprint $table) {
    $table->string('cedula', 20)->primary();
    $table->string('nombre', 100);
    $table->string('correo', 100)->unique();
    $table->string('contrasena', 255);
    $table->integer('id_tipo_usuario');

    $table->foreign('id_tipo_usuario')->references('id_tipo_usuario')->on('Tipo_usuario')->onDelete('restrict')->onUpdate('cascade');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Usuario');
    }
};
