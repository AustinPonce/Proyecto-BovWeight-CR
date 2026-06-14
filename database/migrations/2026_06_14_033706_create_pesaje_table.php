<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('Pesaje', function (Blueprint $table) {
            $table->integer('id_pesaje')->autoIncrement();
            $table->date('fecha');
            $table->decimal('peso', 8, 2);
            $table->string('imagen', 255)->nullable();
            $table->tinyInteger('sincronizado')->default(0);
            $table->string('arete', 50);
            $table->integer('id_tipo_pesaje');

            $table->foreign('arete')->references('arete')->on('Animal')->onDelete('cascade');
            $table->foreign('id_tipo_pesaje')->references('id_tipo_pesaje')->on('Tipo_Pesaje')->onDelete('cascade');
        });
    }
    public function down(): void {
        Schema::dropIfExists('Pesaje');
    }
};