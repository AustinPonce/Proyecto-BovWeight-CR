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
        Schema::create('Animal', function (Blueprint $table) {
    $table->string('arete', 30)->primary();
    $table->string('nombre', 50)->nullable();
    $table->integer('id_raza');
    $table->integer('id_sexo');
    $table->integer('id_estado');
    $table->integer('id_finca');

    $table->foreign('id_raza')->references('id_raza')->on('Raza')->onDelete('restrict')->onUpdate('cascade');
    $table->foreign('id_sexo')->references('id_sexo')->on('Sexo')->onDelete('restrict')->onUpdate('cascade');
    $table->foreign('id_estado')->references('id_estado')->on('Estado')->onDelete('restrict')->onUpdate('cascade');
    $table->foreign('id_finca')->references('id_finca')->on('Finca')->onDelete('restrict')->onUpdate('cascade');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Animal');
    }
};
