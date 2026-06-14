<?php
namespace Database\Factories;
use App\Models\Pesaje;
use Illuminate\Database\Eloquent\Factories\Factory;

class PesajeFactory extends Factory {
    protected $model = Pesaje::class;
    public function definition(): array {
        return [
            'fecha' => $this->faker->date(),
            'peso' => $this->faker->randomFloat(2, 150, 600),
            'imagen' => null,
            'sincronizado' => 1,
            'arete' => 'CR-XXXXX',
            'id_tipo_pesaje' => 1
        ];
    }
}