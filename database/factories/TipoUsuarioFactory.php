<?php

namespace Database\Factories;

use App\Models\TipoUsuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para TipoUsuario (catálogo de roles).
 *
 * Los 3 roles reales del sistema ya se siembran con ids fijos vía
 * CatalogosSeeder (1=Administrador, 2=Ganadero, 3=Veterinario).
 * Esta factory permite generar registros adicionales para pruebas
 * que no dependan de ese seeder.
 *
 * @extends Factory<TipoUsuario>
 */
class TipoUsuarioFactory extends Factory
{
    protected $model = TipoUsuario::class;

    public function definition(): array
    {
        return [
            'nombre_tipo' => fake()->unique()->randomElement([
                'Administrador',
                'Ganadero',
                'Veterinario',
            ]),
        ];
    }

    public function administrador(): static
    {
        return $this->state(['nombre_tipo' => 'Administrador']);
    }

    public function ganadero(): static
    {
        return $this->state(['nombre_tipo' => 'Ganadero']);
    }

    public function veterinario(): static
    {
        return $this->state(['nombre_tipo' => 'Veterinario']);
    }
}
