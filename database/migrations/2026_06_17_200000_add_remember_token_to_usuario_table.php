<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega la columna remember_token a la tabla Usuario.
 *
 * Laravel la necesita para manejar sesiones "recuérdame" y para el ciclo
 * de Auth::login() / Auth::logout(). Sin esta columna el login lanza
 * QueryException 42S22 al intentar actualizar el token.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('Usuario', function (Blueprint $table) {
            $table->rememberToken()->nullable()->after('contrasena');
        });
    }

    public function down(): void
    {
        Schema::table('Usuario', function (Blueprint $table) {
            $table->dropColumn('remember_token');
        });
    }
};
