<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración — Tabla de auditoría del sistema (RF21).
 *
 * Registra cada acción relevante realizada por un usuario autenticado:
 * login, logout, registro, CRUD de animales, fincas, pesajes,
 * transacciones, veterinarios y gestión de catálogos / usuarios.
 *
 * Columnas:
 *   id              → PK autoincremental
 *   cedula_usuario  → FK nullable a Usuario (nullable para auditar incluso
 *                     intentos de login fallidos en el futuro)
 *   accion          → slug de la acción: 'login', 'logout', 'crear_animal', etc.
 *   modulo          → módulo del sistema: 'auth', 'animales', 'pesajes', etc.
 *   descripcion     → texto legible para el admin
 *   ip              → IP del cliente en el momento de la acción
 *   user_agent      → navegador/dispositivo del cliente
 *   datos_antes     → JSON con el estado del recurso ANTES del cambio (nullable)
 *   datos_despues   → JSON con el estado del recurso DESPUÉS del cambio (nullable)
 *   created_at      → timestamp del evento (solo inserted_at, no updated_at)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Auditoria', function (Blueprint $table) {
            $table->id('id_auditoria');

            // Usuario que ejecutó la acción. Nullable por si en el futuro
            // auditamos intentos fallidos de login (sin sesión activa).
            $table->string('cedula_usuario', 20)->nullable();
            $table->foreign('cedula_usuario')
                  ->references('cedula')
                  ->on('Usuario')
                  ->onDelete('set null');

            $table->string('accion', 80);         // ej. 'login', 'crear_finca', 'eliminar_animal'
            $table->string('modulo', 50);          // ej. 'auth', 'fincas', 'animales', 'pesajes'
            $table->text('descripcion');           // Texto legible para el admin
            $table->string('ip', 45)->nullable();  // IPv4 o IPv6
            $table->text('user_agent')->nullable();

            // Estado del recurso antes y después del cambio.
            // Almacenados como JSON para ser flexibles con cualquier modelo.
            $table->json('datos_antes')->nullable();
            $table->json('datos_despues')->nullable();

            // Solo timestamp de creación (los registros de auditoría son inmutables).
            $table->timestamp('created_at')->useCurrent();

            // Índices para filtrado rápido en el panel admin.
            $table->index('cedula_usuario');
            $table->index('accion');
            $table->index('modulo');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Auditoria');
    }
};
