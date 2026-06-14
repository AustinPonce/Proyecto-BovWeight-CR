<?php
namespace Database\Factories;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

class UsuarioFactory extends Factory {
    protected $model = Usuario::class;
    public function definition(): array {
        return [
            'cedula' => $this->faker->unique()->numerify('#########'),
            'nombre' => $this->faker->name(),
            'correo' => $this->faker->unique()->safeEmail(),
            'contrasena' => bcrypt('password'),
            'id_tipo_usuario' => 2
        ];
    }
}