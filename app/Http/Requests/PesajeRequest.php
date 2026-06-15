<?php

namespace App\Http\Requests;

use App\Models\Usuario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validación de creación de Pesaje.
 *
 * El sistema soporta dos modos:
 *   - tipo=manual : el usuario mide al animal y manda largo/altura/torax.
 *                   El backend calcula el peso con FormulaManualStrategy.
 *   - tipo=foto   : el usuario sube una imagen del animal.
 *                   El backend pasa la foto al microservicio ML (mockeado
 *                   por ahora, real en 2E) y guarda el peso devuelto.
 *
 * Reglas comunes:
 *   - arete: animal existente
 *   - tipo: 'manual' o 'foto'
 *
 * Reglas condicionales:
 *   - tipo=manual → largo_cuerpo, altura, perimetro_toracico (cm > 0)
 *   - tipo=foto   → imagen (jpg/png/webp, <= 5MB)
 *
 * Solo Admin y Ganadero pueden registrar pesajes. El Veterinario es solo
 * lectura sobre pesajes.
 */
class PesajeRequest extends FormRequest
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
        $reglas = [
            'arete' => ['required', 'string', Rule::exists('Animal', 'arete')],
            'tipo'  => ['required', Rule::in(['manual', 'foto'])],
        ];

        if ($this->input('tipo') === 'manual') {
            // Validamos las 3 medidas corporales que pide FormulaManualStrategy.
            $reglas += [
                'largo_cuerpo'       => ['required', 'numeric', 'min:30', 'max:300'],
                'altura'             => ['required', 'numeric', 'min:30', 'max:200'],
                'perimetro_toracico' => ['required', 'numeric', 'min:30', 'max:300'],
            ];
        }

        if ($this->input('tipo') === 'foto') {
            // 5MB max; formatos comunes para fotos de campo.
            $reglas['imagen'] = ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'];
        }

        return $reglas;
    }

    public function messages(): array
    {
        return [
            'arete.required' => 'Debés seleccionar un animal.',
            'arete.exists'   => 'El animal seleccionado no existe.',
            'tipo.in'        => 'El tipo de pesaje debe ser manual o por foto.',

            'largo_cuerpo.required'       => 'Ingresá el largo del cuerpo.',
            'largo_cuerpo.numeric'        => 'El largo debe ser un número.',
            'altura.required'             => 'Ingresá la altura.',
            'perimetro_toracico.required' => 'Ingresá el perímetro torácico.',

            'imagen.required' => 'Subí una foto del animal.',
            'imagen.image'    => 'El archivo debe ser una imagen.',
            'imagen.mimes'    => 'La imagen debe ser JPG, PNG o WEBP.',
            'imagen.max'      => 'La imagen no puede pesar más de 5 MB.',
        ];
    }
}
