<?php
namespace Database\Factories;
use App\Models\Animal;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnimalFactory extends Factory {
    protected $model = Animal::class;
    public function definition(): array {
        return [
            'arete' => 'CR-' . $this->faker->unique()->numberBetween(10000, 99999),
            'nombre' => $this->faker->firstName(),
            'id_raza' => 1,
            'id_sexo' => 1,
            'id_estado' => 1,
            'id_finca' => 1
        ];
    }
}