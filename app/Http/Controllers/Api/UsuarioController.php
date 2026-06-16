<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
