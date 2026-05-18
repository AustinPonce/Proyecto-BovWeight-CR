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
        Schema::create('Recordatorio', function (Blueprint $table) {
    $table->integer('id_recordatorio')->autoIncrement();
    $table->string('frecuencia', 50);
    $table->date('fecha_inicio');
    $table->string('arete', 30);

    $table->foreign('arete')->references('arete')->on('Animal')->onDelete('cascade')->onUpdate('cascade');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Recordatorio');
    }
};
