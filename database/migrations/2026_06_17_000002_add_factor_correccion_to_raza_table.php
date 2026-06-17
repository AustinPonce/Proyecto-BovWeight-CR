<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración — Agrega factor de corrección por raza (RF13).
 *
 * La columna `factor_correccion` permite aplicar un multiplicador al peso
 * estimado (por IA o fórmula manual) según la raza del animal.
 * Ejemplo: Holstein=1.08 (razas más pesadas), Jersey=0.92 (razas más livianas).
 *
 * DEFAULT 1.0000 garantiza que las razas existentes no cambien su comportamiento
 * actual (multiplicar por 1 = sin efecto).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('Raza', function (Blueprint $table) {
            $table->decimal('factor_correccion', 5, 4)
                  ->default(1.0000)
                  ->after('raza')
                  ->comment('Multiplicador de corrección por raza. 1.0000 = sin corrección.');
        });
    }

    public function down(): void
    {
        Schema::table('Raza', function (Blueprint $table) {
            $table->dropColumn('factor_correccion');
        });
    }
};
