<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use App\Models\Usuario;
use App\Services\AuditoriaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UsuarioController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Usuario::with('tipoUsuario')
            ->where('id_tipo_usuario', '!=', Usuario::ROL_ADMIN)
            ->orderBy('nombre');

        if ($request->filled('tipo')) {
            $query->where('id_tipo_usuario', $request->integer('tipo'));
        }

        $usuarios = $query->get(['cedula', 'nombre', 'correo', 'id_tipo_usuario', 'activo']);

        return response()->json(['data' => $usuarios]);
    }

    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'cedula'          => ['required', 'string', 'max:20', 'unique:Usuario,cedula'],
            'nombre'          => ['required', 'string', 'max:100'],
            'correo'          => ['required', 'email', 'max:100', 'unique:Usuario,correo'],
            'contrasena'      => ['required', 'confirmed', Password::min(8)->mixedCase()->symbols()],
            'id_tipo_usuario' => ['required', 'integer', Rule::in([Usuario::ROL_GANADERO, Usuario::ROL_VETERINARIO])],
        ]);

        $usuario = Usuario::create($datos);

        return response()->json([
            'mensaje' => 'Usuario creado correctamente.',
            'data'    => $usuario->only(['cedula', 'nombre', 'correo', 'id_tipo_usuario', 'activo']),
        ], 201);
    }

    public function update(Request $request, Usuario $usuario): JsonResponse
    {
        $datos = $request->validate([
            'nombre'          => ['required', 'string', 'max:100'],
            'correo'          => ['required', 'email', 'max:100', Rule::unique('Usuario', 'correo')->ignore($usuario->cedula, 'cedula')],
            'id_tipo_usuario' => ['required', 'integer', 'exists:Tipo_usuario,id_tipo_usuario'],
            'contrasena'      => ['nullable', 'confirmed', Password::min(8)->mixedCase()->symbols()],
        ]);

        if (empty($datos['contrasena'])) {
            unset($datos['contrasena']);
        }

        $original = $usuario->toArray();
        $usuario->update($datos);

        AuditoriaService::registrar(
            accion:       Auditoria::ACCION_ACTUALIZAR,
            modulo:       Auditoria::MODULO_USUARIOS,
            descripcion:  "Admin (API) actualizó usuario '{$usuario->nombre}' (cédula: {$usuario->cedula}).",
            datosAntes:   array_diff_key($original, ['contrasena' => '']),
            datosDespues: array_diff_key($usuario->toArray(), ['contrasena' => '']),
        );

        return response()->json([
            'mensaje' => 'Usuario actualizado correctamente.',
            'data'    => $usuario->only(['cedula', 'nombre', 'correo', 'id_tipo_usuario', 'activo']),
        ]);
    }

    public function destroy(Request $request, Usuario $usuario): JsonResponse
    {
        if ($usuario->cedula === $request->user()->cedula) {
            return response()->json(['mensaje' => 'No podés eliminar tu propia cuenta.'], 422);
        }

        $snapshot = $usuario->toArray();
        $usuario->delete();

        AuditoriaService::registrar(
            accion:      Auditoria::ACCION_ELIMINAR,
            modulo:      Auditoria::MODULO_USUARIOS,
            descripcion: "Admin (API) eliminó al usuario '{$snapshot['nombre']}' (cédula: {$snapshot['cedula']}).",
            datosAntes:  array_diff_key($snapshot, ['contrasena' => '']),
        );

        return response()->json(['mensaje' => 'Usuario eliminado correctamente.']);
    }

    public function toggleActivo(Request $request, Usuario $usuario): JsonResponse
    {
        if ($usuario->cedula === $request->user()->cedula) {
            return response()->json(['mensaje' => 'No podés desactivar tu propia cuenta.'], 422);
        }

        $usuario->update(['activo' => ! $usuario->activo]);

        return response()->json([
            'mensaje' => $usuario->activo ? 'Usuario activado.' : 'Usuario desactivado.',
            'activo'  => $usuario->activo,
        ]);
    }
}
