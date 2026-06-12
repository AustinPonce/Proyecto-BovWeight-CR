<?php

namespace Database\Factories;

use App\Models\TipoUsuario;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para Usuario (entidad de autenticación).
 *
 * - `cedula` es la PK (string) y sigue el formato numérico de cédula CR (9 dígitos).
 * - `contrasena` tiene el cast 'hashed' en el modelo, así que basta con asignar
 *   el texto plano y Eloquent lo hashea al guardar.
 * - `id_tipo_usuario` por defecto toma un id existente del catálogo Tipo_usuario
 *   (sembrado por CatalogosSeeder); si no existe ninguno, crea uno con su factory.
 *
 * Estados disponibles: admin(), ganadero(), veterinario() — usan las constantes
 * Usuario::ROL_* y asumen que CatalogosSeeder ya corrió (ids 1, 2, 3).
 *
 * @extends Factory<Usuario>
 */
class UsuarioFactory extends Factory
{
    protected $model = Usuario::class;

    /**
     * Contraseña común reutilizada entre instancias para no re-hashear en cada create().
     */
    protected static ?string $password = null;

    public function definition(): array
    {
        return [
            'cedula' => fake()->unique()->numerify('#########'),
            'nombre' => fake()->name(),
            'correo' => fake()->unique()->safeEmail(),
            'contrasena' => static::$password ??= 'password',
            'id_tipo_usuario' => TipoUsuario::query()->inRandomOrder()->value('id_tipo_usuario')
                ?? TipoUsuario::factory(),
        ];
    }

    /** Usuario con rol Administrador. */
    public function admin(): static
    {
        return $this->state(['id_tipo_usuario' => Usuario::ROL_ADMIN]);
    }

    /** Usuario con rol Ganadero. */
    public function ganadero(): static
    {
        return $this->state(['id_tipo_usuario' => Usuario::ROL_GANADERO]);
    }

    /** Usuario con rol Veterinario. */
    public function veterinario(): static
    {
        return $this->state(['id_tipo_usuario' => Usuario::ROL_VETERINARIO]);
    }
}
