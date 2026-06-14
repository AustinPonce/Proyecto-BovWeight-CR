<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;

/**
 * Crea 3 usuarios de prueba — uno por cada rol del sistema.
 *
 * Credenciales para testing rápido (TODAS usan la misma contraseña):
 *
 *   ┌───────────────┬─────────────┬────────────────────┐
 *   │  Rol          │  Cédula     │  Contraseña        │
 *   ├───────────────┼─────────────┼────────────────────┤
 *   │  Admin        │  100000001  │  bovweight2026     │
 *   │  Ganadero     │  100000002  │  bovweight2026     │
 *   │  Veterinario  │  100000003  │  bovweight2026     │
 *   └───────────────┴─────────────┴────────────────────┘
 *
 * El modelo Usuario tiene cast 'hashed' en contrasena, así que asignamos
 * el texto plano y se hashea solo al guardar.
 */
class UsuariosSeeder extends Seeder
{
    public function run(): void
    {
        $contrasenaComun = 'bovweight2026';

        Usuario::create([
            'cedula'          => '100000001',
            'nombre'          => 'Admin de Prueba',
            'correo'          => 'admin@bovweight.cr',
            'contrasena'      => $contrasenaComun,
            'id_tipo_usuario' => Usuario::ROL_ADMIN,
        ]);

        Usuario::create([
            'cedula'          => '100000002',
            'nombre'          => 'Ganadero de Prueba',
            'correo'          => 'ganadero@bovweight.cr',
            'contrasena'      => $contrasenaComun,
            'id_tipo_usuario' => Usuario::ROL_GANADERO,
        ]);

        Usuario::create([
            'cedula'          => '100000003',
            'nombre'          => 'Veterinario de Prueba',
            'correo'          => 'vet@bovweight.cr',
            'contrasena'      => $contrasenaComun,
            'id_tipo_usuario' => Usuario::ROL_VETERINARIO,
        ]);
    }
}
