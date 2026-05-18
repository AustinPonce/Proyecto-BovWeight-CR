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
        Schema::create('Reporte', function (Blueprint $table) {
    $table->integer('id_reporte')->autoIncrement();
    $table->dateTime('fecha_generacion');
    $table->integer('id_Tipo_Formato');

    $table->foreign('id_Tipo_Formato')->references('id_Tipo_Formato')->on('Tipo_Formato')->onDelete('restrict')->onUpdate('cascade');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Reporte');
    }
};
