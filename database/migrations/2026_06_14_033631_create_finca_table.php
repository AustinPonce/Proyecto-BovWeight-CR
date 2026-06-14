<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('Finca', function (Blueprint $table) {
            $table->integer('id_finca')->autoIncrement();
            $table->string('nombre', 100);
            $table->string('ubicacion', 150);
            $table->string('cedula', 30);

            $table->foreign('cedula')->references('cedula')->on('Usuario')->onDelete('cascade');
        });
    }
    public function down(): void {
        Schema::dropIfExists('Finca');
    }
};