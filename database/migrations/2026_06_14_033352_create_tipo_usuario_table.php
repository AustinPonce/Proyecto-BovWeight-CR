<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('Tipo_usuario', function (Blueprint $table) {
            $table->integer('id_tipo_usuario')->autoIncrement();
            $table->string('nombre_tipo', 50);
        });
    }
    public function down(): void {
        Schema::dropIfExists('Tipo_usuario');
    }
};