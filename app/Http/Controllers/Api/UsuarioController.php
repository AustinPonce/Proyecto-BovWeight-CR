<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
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
