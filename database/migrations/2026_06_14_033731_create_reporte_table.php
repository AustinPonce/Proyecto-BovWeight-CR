<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('Reporte', function (Blueprint $table) {
            $table->integer('id_reporte')->autoIncrement();
            $table->dateTime('fecha_generacion');
            $table->integer('id_tipo_formato');

            $table->foreign('id_tipo_formato')->references('id_Tipo_Formato')->on('Tipo_Formato')->onDelete('cascade');
        });
    }
    public function down(): void {
        Schema::dropIfExists('Reporte');
    }
};