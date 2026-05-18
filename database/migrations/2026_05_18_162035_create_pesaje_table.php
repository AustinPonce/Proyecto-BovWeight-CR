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
        Schema::create('Pesaje', function (Blueprint $table) {
    $table->integer('id_pesaje')->autoIncrement();
    $table->dateTime('fecha');
    $table->decimal('peso', 6, 2);
    $table->string('imagen', 255)->nullable();
    $table->tinyInteger('sincronizado')->default(1);
    $table->string('arete', 30);
    $table->integer('id_tipo_pesaje');

    $table->foreign('arete')->references('arete')->on('Animal')->onDelete('cascade')->onUpdate('cascade');
    $table->foreign('id_tipo_pesaje')->references('id_tipo_pesaje')->on('Tipo_Pesaje')->onDelete('restrict')->onUpdate('cascade');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Pesaje');
    }
};
