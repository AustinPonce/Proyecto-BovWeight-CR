<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Transaccion', function (Blueprint $table) {
            $table->id('id_transaccion');
            $table->enum('tipo', ['compra', 'venta']);
            $table->string('arete', 30);
            $table->string('cedula_usuario', 20);
            $table->string('nombre_contraparte', 150);
            $table->string('cedula_contraparte', 30)->nullable();
            $table->decimal('precio_por_kg', 10, 2);
            $table->decimal('peso_negociado', 8, 2);
            $table->decimal('monto_total', 12, 2);
            $table->datetime('fecha');
            $table->text('notas')->nullable();

            $table->foreign('arete')->references('arete')->on('Animal')->onDelete('cascade');
            $table->foreign('cedula_usuario')->references('cedula')->on('Usuario')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Transaccion');
    }
};
