<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('Veterinario_Finca', function (Blueprint $table) {
            $table->string('cedula', 30);
            $table->integer('id_finca');

            $table->primary(['cedula', 'id_finca']);

            $table->foreign('cedula')->references('cedula')->on('Usuario')->onDelete('cascade');
            $table->foreign('id_finca')->references('id_finca')->on('Finca')->onDelete('cascade');
        });
    }
    public function down(): void {
        Schema::dropIfExists('Veterinario_Finca');
    }
};