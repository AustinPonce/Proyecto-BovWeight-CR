<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración — Agrega columnas de corrección por raza a la tabla Pesaje (RF13).
 *
 * Se agregan tres columnas para mantener trazabilidad completa del cálculo:
 *
 *   peso_original   → Peso estimado ANTES de aplicar el factor (valor puro del ML
 *                     o de la fórmula manual). Useful para comparación/auditoría.
 *   factor_raza     → Factor aplicado en el momento del pesaje (snapshot del factor
 *                     de la raza). Se guarda aquí porque el admin puede cambiar el
 *                     factor de una raza en el futuro y no queremos alterar el historial.
 *   peso_corregido  → Resultado final: peso_original × factor_raza. Este es el peso
 *                     que se muestra al usuario y se usa en reportes.
 *
 * Todas son NULLABLE para compatibilidad con pesajes históricos anteriores a RF13.
 * En la vista se mostrará "N/A" para registros sin estos valores.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('Pesaje', function (Blueprint $table) {
            $table->decimal('peso_original', 8, 2)
                  ->nullable()
                  ->after('peso')
                  ->comment('Peso estimado antes de aplicar factor de raza.');

            $table->decimal('factor_raza', 5, 4)
                  ->nullable()
                  ->after('peso_original')
                  ->comment('Snapshot del factor de corrección de la raza al momento del pesaje.');

            $table->decimal('peso_corregido', 8, 2)
                  ->nullable()
                  ->after('factor_raza')
                  ->comment('Peso final = peso_original × factor_raza.');
        });
    }

    public function down(): void
    {
        Schema::table('Pesaje', function (Blueprint $table) {
            $table->dropColumn(['peso_original', 'factor_raza', 'peso_corregido']);
        });
    }
};
