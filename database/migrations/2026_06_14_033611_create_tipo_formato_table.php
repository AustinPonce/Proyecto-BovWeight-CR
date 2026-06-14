<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('Tipo_Formato', function (Blueprint $table) {
            $table->integer('id_Tipo_Formato')->autoIncrement();
            $table->string('tipo', 30);
        });
    }
    public function down(): void {
        Schema::dropIfExists('Tipo_Formato');
    }
};