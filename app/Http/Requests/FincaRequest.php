<?php

namespace App\Http\Requests;

use App\Models\Usuario;
use Illuminate\Foundation\Http\FormRequest;

/**
 * FormRequest para crear y actualizar Fincas.
 *
 * Un FormRequest es una clase que Laravel inyecta en el método del controller.
 * Cuando entra el request, automáticamente corre authorize() y luego rules().
 * Si falla la validación, redirige solo con los errores. Esto deja el controller
 * mucho más limpio (no hace falta validar manualmente).
 *
 * Reglas:
 *   - nombre    : obligatorio, máximo 100 chars (límite del esquema).
 *   - ubicacion : obligatorio, máximo 255 chars.
 *
 * La cédula del dueño NO se valida acá — se asigna automáticamente desde el
 * usuario autenticado en el controller (un Ganadero solo puede crear fincas
 * a su nombre, nunca a nombre de otro).
 */
class FincaRequest extends FormRequest
{
    /**
     * Quién puede mandar este request.
     * Solo Admin y Ganadero pueden crear/editar fincas.
     */
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
        return [
            'nombre'    => ['required', 'string', 'max:100'],
            'ubicacion' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Mensajes de error en español (más amigables para el usuario final).
     */
    public function messages(): array
    {
        return [
            'nombre.required'    => 'El nombre de la finca es obligatorio.',
            'nombre.max'         => 'El nombre no puede tener más de 100 caracteres.',
            'ubicacion.required' => 'La ubicación es obligatoria.',
            'ubicacion.max'      => 'La ubicación no puede tener más de 255 caracteres.',
        ];
    }
}
