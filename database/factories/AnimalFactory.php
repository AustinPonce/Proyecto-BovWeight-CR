<?php

namespace Database\Factories;

use App\Models\Animal;
use App\Models\Estado;
use App\Models\Finca;
use App\Models\Raza;
use App\Models\Sexo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para Animal.
 *
 * - `arete` es la PK (string) y simula el formato numérico asignado por SENASA.
 * - `nombre` es opcional: solo algunos animales (p. ej. vacas de ordeño) tienen
 *   nombre propio, según lo comentado por Don Iván Chavarría en el levantamiento.
 * - Los catálogos (raza, sexo, estado) toman por defecto un registro ya existente
 *   (sembrado por CatalogosSeeder); si la tabla está vacía, crean uno con su factory.
 * - `id_finca` crea una finca nueva por defecto.
 *
 * Estados disponibles: activo(), vendido(), fallecido(), conNombre().
 *
 * @extends Factory<Animal>
 */
class AnimalFactory extends Factory
{
    protected $model = Animal::class;

    public function definition(): array
    {
        return [
            'arete'     => fake()->unique()->numerify('CR-#########'),
            'nombre'    => fake()->boolean(30) ? fake()->firstName() : null,
            'id_raza'   => Raza::query()->inRandomOrder()->value('id_raza') ?? Raza::factory(),
            'id_sexo'   => Sexo::query()->inRandomOrder()->value('id_sexo') ?? Sexo::factory(),
            'id_estado' => Estado::query()->where('estado', 'Activo')->value('id_estado')
                ?? Estado::factory()->activo(),
            'id_finca'  => Finca::factory(),
        ];
    }

    /** Fuerza un nombre propio para el animal (p. ej. vacas de ordeño). */
    public function conNombre(?string $nombre = null): static
    {
        return $this->state([
            'nombre' => $nombre ?? fake()->firstName(),
        ]);
    }

    /** Animal activo en la finca. */
    public function activo(): static
    {
        return $this->state([
            'id_estado' => Estado::query()->where('estado', 'Activo')->value('id_estado')
                ?? Estado::factory()->activo(),
        ]);
    }

    /** Animal vendido (ya no se gestiona como activo, pero conserva historial). */
    public function vendido(): static
    {
        return $this->state([
            'id_estado' => Estado::query()->where('estado', 'Vendido')->value('id_estado')
                ?? Estado::factory()->vendido(),
        ]);
    }

    /** Animal fallecido (conserva historial de pesajes). */
    public function fallecido(): static
    {
        return $this->state([
            'id_estado' => Estado::query()->where('estado', 'Fallecido')->value('id_estado')
                ?? Estado::factory()->fallecido(),
        ]);
    }
}
