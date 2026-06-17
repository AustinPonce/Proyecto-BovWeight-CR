<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ResetPasswordController extends Controller
{
    public function mostrar(Request $request, string $token): View
    {
        return view('auth.passwords.reset', [
            'token'  => $token,
            'correo' => $request->query('correo', ''),
        ]);
    }

    public function resetear(Request $request): RedirectResponse
    {
        $request->validate([
            'correo'     => ['required', 'email'],
            'token'      => ['required', 'string'],
            'contrasena' => ['required', 'confirmed', Password::min(8)->mixedCase()->symbols()],
        ]);

        $registro = DB::table('password_reset_tokens')
            ->where('email', $request->input('correo'))
            ->first();

        if (! $registro
            || ! hash_equals($registro->token, hash('sha256', $request->input('token')))
            || Carbon::parse($registro->created_at)->addMinutes(60)->isPast()
        ) {
            return back()->withErrors(['token' => 'El enlace de restablecimiento es inválido o ha expirado.']);
        }

        $usuario = Usuario::where('correo', $request->input('correo'))->firstOrFail();

        $usuario->update(['contrasena' => $request->input('contrasena')]);

        DB::table('password_reset_tokens')->where('email', $request->input('correo'))->delete();

        return redirect()->route('login')
            ->with('exito', 'Contraseña restablecida correctamente. Podés ingresar ahora.');
    }
}
