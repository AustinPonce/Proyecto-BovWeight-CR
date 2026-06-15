<?php

namespace Database\Factories;

use App\Models\Raza;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para Raza (catálogo de razas de ganado bovino).
 *
 * Coincide con las razas mencionadas por el cliente en el levantamiento
 * (Brahman, Pardo Suizo, Holstein) más algunas adicionales comunes en
 * la región Chorotega (Nelore, Gyr).
 *
 * @extends Factory<Raza>
 */
class RazaFactory extends Factory
{
    protected $model = Raza::class;

    public function definition(): array
    {
        return [
            'raza' => fake()->unique()->randomElement([
                'Brahman',
                'Pardo Suizo',
                'Holstein',
                'Nelore',
                'Gyr',
            ]),
        ];
    }
}
