<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeMail;
use App\Models\Auditoria;
use App\Models\TipoUsuario;
use App\Models\Usuario;
use App\Services\AuditoriaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UsuarioAdminController extends Controller
{
    public function index(Request $request): View
    {
        $query = Usuario::with('tipoUsuario')->orderBy('nombre');

        if ($request->filled('buscar')) {
            $term = $request->input('buscar');
            $query->where(function ($q) use ($term) {
                $q->where('nombre', 'like', "%{$term}%")
                  ->orWhere('cedula', 'like', "%{$term}%")
                  ->orWhere('correo', 'like', "%{$term}%");
            });
        }

        if ($request->filled('rol')) {
            $query->where('id_tipo_usuario', $request->input('rol'));
        }

        $usuarios = $query->paginate(20)->withQueryString();
        $roles = TipoUsuario::orderBy('nombre_tipo')->get();

        return view('admin.usuarios.index', compact('usuarios', 'roles'));
    }

    public function create(): View
    {
        $roles = TipoUsuario::orderBy('nombre_tipo')->get();
        return view('admin.usuarios.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'cedula'          => ['required', 'string', 'max:20', 'unique:Usuario,cedula'],
            'nombre'          => ['required', 'string', 'max:100'],
            'correo'          => ['required', 'email', 'max:100', 'unique:Usuario,correo'],
            'contrasena'      => ['required', 'confirmed', Password::min(8)->mixedCase()->symbols()],
            'id_tipo_usuario' => ['required', 'integer', 'exists:Tipo_usuario,id_tipo_usuario'],
        ]);

        $usuario = Usuario::create($datos);

        Mail::to($usuario->correo)->send(new WelcomeMail($usuario));

        // RF21 — Auditoría.
        AuditoriaService::registrar(
            accion:       Auditoria::ACCION_CREAR,
            modulo:       Auditoria::MODULO_USUARIOS,
            descripcion:  "Admin creó usuario '{$usuario->nombre}' (cédula: {$usuario->cedula}, rol: {$usuario->id_tipo_usuario}).",
            datosDespues: ['cedula' => $usuario->cedula, 'nombre' => $usuario->nombre, 'id_tipo_usuario' => $usuario->id_tipo_usuario],
        );

        return redirect()->route('admin.usuarios.index')
            ->with('exito', 'Usuario creado correctamente.');
    }

    public function edit(Usuario $usuario): View
    {
        $roles = TipoUsuario::orderBy('nombre_tipo')->get();
        return view('admin.usuarios.edit', compact('usuario', 'roles'));
    }

    public function update(Request $request, Usuario $usuario): RedirectResponse
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

        // RF21 — Auditoría.
        AuditoriaService::registrar(
            accion:       Auditoria::ACCION_ACTUALIZAR,
            modulo:       Auditoria::MODULO_USUARIOS,
            descripcion:  "Admin actualizó usuario '{$usuario->nombre}' (cédula: {$usuario->cedula}).",
            datosAntes:   array_diff_key($original, ['contrasena' => '']),
            datosDespues: array_diff_key($usuario->toArray(), ['contrasena' => '']),
        );

        return redirect()->route('admin.usuarios.index')
            ->with('exito', 'Usuario actualizado correctamente.');
    }

    public function toggleActivo(Usuario $usuario): RedirectResponse
    {
        if ($usuario->cedula === auth()->user()->cedula) {
            return redirect()->route('admin.usuarios.index')
                ->with('error', 'No podés desactivar tu propia cuenta.');
        }

        $usuario->update(['activo' => ! $usuario->activo]);

        $estado = $usuario->activo ? 'activado' : 'desactivado';

        // RF21 — Auditoría.
        AuditoriaService::registrar(
            accion:      $usuario->activo ? Auditoria::ACCION_ACTIVAR : Auditoria::ACCION_DESACTIVAR,
            modulo:      Auditoria::MODULO_USUARIOS,
            descripcion: "Admin {$estado} al usuario '{$usuario->nombre}' (cédula: {$usuario->cedula}).",
            datosDespues: ['cedula' => $usuario->cedula, 'activo' => $usuario->activo],
        );

        return redirect()->route('admin.usuarios.index')
            ->with('exito', "Usuario {$usuario->nombre} {$estado} correctamente.");
    }

    public function destroy(Usuario $usuario): RedirectResponse
    {
        if ($usuario->cedula === auth()->user()->cedula) {
            return redirect()->route('admin.usuarios.index')
                ->with('error', 'No podés eliminar tu propia cuenta.');
        }

        $snapshot = $usuario->toArray();
        $usuario->delete();

        // RF21 — Auditoría.
        AuditoriaService::registrar(
            accion:      Auditoria::ACCION_ELIMINAR,
            modulo:      Auditoria::MODULO_USUARIOS,
            descripcion: "Admin eliminó al usuario '{$snapshot['nombre']}' (cédula: {$snapshot['cedula']}).",
            datosAntes:  array_diff_key($snapshot, ['contrasena' => '']),
        );

        return redirect()->route('admin.usuarios.index')
            ->with('exito', 'Usuario eliminado.');
    }
}
