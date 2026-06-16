<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Medicamento', function (Blueprint $table) {
            $table->id('id_medicamento');
            $table->string('nombre', 150);
            $table->string('unidad', 20)->default('ml');
            $table->decimal('dosis_por_kg', 8, 4);
            $table->string('descripcion', 500)->nullable();
            $table->boolean('activo')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Medicamento');
    }
};
