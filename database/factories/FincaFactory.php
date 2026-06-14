<?php
namespace Database\Factories;
use App\Models\Finca;
use Illuminate\Database\Eloquent\Factories\Factory;

class FincaFactory extends Factory {
    protected $model = Finca::class;
    public function definition(): array {
        return [
            'nombre' => $this->faker->company() . ' ' . $this->faker->randomElement(['Finca', 'Hacienda']),
            'ubicacion' => $this->faker->city(),
            'cedula' => '101110111'
        ];
    }
}