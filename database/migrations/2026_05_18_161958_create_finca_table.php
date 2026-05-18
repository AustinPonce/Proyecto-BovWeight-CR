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
        Schema::create('Finca', function (Blueprint $table) {
    $table->integer('id_finca')->autoIncrement();
    $table->string('nombre', 100);
    $table->string('ubicacion', 255);
    $table->string('cedula', 20);

    $table->foreign('cedula')->references('cedula')->on('Usuario')->onDelete('restrict')->onUpdate('cascade');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Finca');
    }
};
