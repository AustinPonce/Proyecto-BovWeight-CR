<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ComentarioVeterinario', function (Blueprint $table) {
            $table->id('id_comentario');
            $table->string('arete', 30);
            $table->string('cedula_veterinario', 20);
            $table->text('comentario');
            $table->datetime('fecha');

            $table->foreign('arete')->references('arete')->on('Animal')->onDelete('cascade');
            $table->foreign('cedula_veterinario')->references('cedula')->on('Usuario')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ComentarioVeterinario');
    }
};
