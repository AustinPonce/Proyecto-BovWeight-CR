<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('Recordatorio', function (Blueprint $table) {
            $table->integer('id_recordatorio')->autoIncrement();
            $table->string('frecuencia', 50);
            $table->dateTime('fecha_inicio');
            $table->string('arete', 50);

            $table->foreign('arete')->references('arete')->on('Animal')->onDelete('cascade');
        });
    }
    public function down(): void {
        Schema::dropIfExists('Recordatorio');
    }
};