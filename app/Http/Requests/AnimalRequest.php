<?php

namespace App\Http\Requests;

use App\Models\Animal;
use App\Models\Usuario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * FormRequest para crear y actualizar Animales.
 *
 * Diferencia clave respecto a FincaRequest:
 *   - El "arete" es la PK (no autoincremental). El usuario lo escribe.
 *     Por eso lo validamos como único, pero solo al CREAR; al EDITAR
 *     el arete viene del URL y no se cambia.
 *   - id_raza / id_sexo / id_estado / id_finca deben existir en sus tablas.
 *     `Rule::exists` hace ese chequeo contra la BD.
 *
 * El Veterinario NO puede crear ni editar animales — solo lectura.
 */
class AnimalRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->tieneRol(
            Usuario::ROL_ADMIN,
            Usuario::ROL_GANADERO
        );
    }

    public function rules(): array
    {
        // ¿Estamos editando un animal existente?
        $animal = $this->route('animal'); // null si es creación
        $esEdicion = $animal instanceof Animal;

        return [
            // El arete solo se valida al crear (al editar viene del URL y no se cambia).
            'arete' => $esEdicion
                ? ['nullable']
                : ['required', 'string', 'max:30', 'unique:Animal,arete'],

            'nombre'    => ['nullable', 'string', 'max:50'],
            'id_raza'   => ['required', 'integer', Rule::exists('Raza', 'id_raza')],
            'id_sexo'   => ['required', 'integer', Rule::exists('Sexo', 'id_sexo')],
            'id_estado' => ['required', 'integer', Rule::exists('Estado', 'id_estado')],
            'id_finca'  => ['required', 'integer', Rule::exists('Finca', 'id_finca')],
        ];
    }

    public function messages(): array
    {
        return [
            'arete.required'    => 'El número de arete es obligatorio.',
            'arete.unique'      => 'Ya hay un animal registrado con ese arete.',
            'arete.max'         => 'El arete no puede tener más de 30 caracteres.',
            'id_raza.required'  => 'Seleccioná una raza.',
            'id_sexo.required'  => 'Seleccioná el sexo.',
            'id_estado.required'=> 'Seleccioná el estado.',
            'id_finca.required' => 'Seleccioná la finca a la que pertenece.',
            'id_finca.exists'   => 'La finca seleccionada no existe.',
        ];
    }
}
