<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetMail;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function mostrar(): View
    {
        return view('auth.passwords.forgot');
    }

    public function enviar(Request $request): RedirectResponse
    {
        $request->validate([
            'correo' => ['required', 'email'],
        ]);

        $usuario = Usuario::where('correo', $request->input('correo'))->first();

        // Siempre respondemos con el mismo mensaje para no revelar si el correo existe.
        if ($usuario) {
            $token = Str::random(64);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $usuario->correo],
                ['token' => hash('sha256', $token), 'created_at' => Carbon::now()]
            );

            Mail::to($usuario->correo)->send(new PasswordResetMail($usuario, $token));
        }

        return back()->with('exito', 'Si ese correo está registrado, recibirás un enlace para restablecer tu contraseña.');
    }
}
