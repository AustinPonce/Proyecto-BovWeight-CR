<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeMail;
use App\Models\TipoUsuario;
use App\Models\Usuario;
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

        $usuario->update($datos);

        return redirect()->route('admin.usuarios.index')
            ->with('exito', 'Usuario actualizado correctamente.');
    }

    public function destroy(Usuario $usuario): RedirectResponse
    {
        if ($usuario->cedula === auth()->user()->cedula) {
            return redirect()->route('admin.usuarios.index')
                ->with('error', 'No podés eliminar tu propia cuenta.');
        }

        $usuario->delete();

        return redirect()->route('admin.usuarios.index')
            ->with('exito', 'Usuario eliminado.');
    }
}
