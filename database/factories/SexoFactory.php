<?php

namespace Database\Factories;

use App\Models\Sexo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para Sexo (catálogo: Macho, Hembra).
 *
 * @extends Factory<Sexo>
 */
class SexoFactory extends Factory
{
    protected $model = Sexo::class;

    public function definition(): array
    {
        return [
            'sexo' => fake()->unique()->randomElement(['Macho', 'Hembra']),
        ];
    }
}
