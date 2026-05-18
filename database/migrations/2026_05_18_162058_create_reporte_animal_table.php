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
        Schema::create('Reporte_Animal', function (Blueprint $table) {
    $table->integer('id_reporte');
    $table->string('arete', 30);

    $table->primary(['id_reporte', 'arete']);
    $table->foreign('id_reporte')->references('id_reporte')->on('Reporte')->onDelete('cascade')->onUpdate('cascade');
    $table->foreign('arete')->references('arete')->on('Animal')->onDelete('cascade')->onUpdate('cascade');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Reporte_Animal');
    }
};
