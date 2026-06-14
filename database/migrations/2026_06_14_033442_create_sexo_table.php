<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('Sexo', function (Blueprint $table) {
            $table->integer('id_sexo')->autoIncrement();
            $table->string('sexo', 20);
        });
    }
    public function down(): void {
        Schema::dropIfExists('Sexo');
    }
};