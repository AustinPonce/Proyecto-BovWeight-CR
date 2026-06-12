<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\LoginNotificationMail;
use App\Mail\PasswordResetMail;
use App\Mail\WelcomeMail;
use App\Models\Auditoria;
use App\Models\Usuario;
use App\Services\AuditoriaService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * AuthController — registro, login y logout.
 *
 * Diseñado para responder tanto a la app web (sesión + redirect) como a la app
 * móvil (token Sanctum + JSON). El método `expectsJson()` del request distingue
 * uno del otro sin duplicar lógica.
 *
 * Flujos:
 *   WEB  → GET  /login           → mostrarLogin()  (vista Blade)
 *          POST /login           → login()         → redirect a /dashboard
 *          GET  /registro        → mostrarRegistro()
 *          POST /registro        → registrar()     → redirect a /dashboard
 *          POST /logout          → logout()        → redirect a /login
 *
 *   API  → POST /api/login       → login()         → JSON con token
 *          POST /api/registro    → registrar()     → JSON con token
 *          POST /api/logout      → logout()        → JSON ok
 */
class AuthController extends Controller
{
    // ==================================================================
    // FORGOT PASSWORD (API)
    // ==================================================================

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $usuario = Usuario::where('correo', $request->input('email'))->first();

        // Siempre respondemos con el mismo mensaje para no revelar si el correo existe.
        if ($usuario) {
            $token = Str::random(64);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $usuario->correo],
                ['token' => hash('sha256', $token), 'created_at' => Carbon::now()]
            );

            Mail::to($usuario->correo)->send(new PasswordResetMail($usuario, $token));
        }

        return response()->json([
            'message' => 'Si ese correo está registrado, recibirás un enlace para restablecer tu contraseña.',
        ]);
    }

    // ==================================================================
    // VISTAS (solo web)
    // ==================================================================

    public function mostrarLogin(): View
    {
        return view('auth.login');
    }

    public function mostrarRegistro(): View
    {
        // Pasamos la lista de roles seleccionables (excluimos Admin: solo se crea
        // desde un seeder o por otro admin, nunca desde el formulario público).
        $rolesSeleccionables = [
            Usuario::ROL_GANADERO    => 'Ganadero',
            Usuario::ROL_VETERINARIO => 'Veterinario',
        ];

        return view('auth.register', compact('rolesSeleccionables'));
    }

    // ==================================================================
    // REGISTRO
    // ==================================================================

    public function registrar(Request $request): JsonResponse|RedirectResponse
    {
        // 1) Validación de entrada. Las reglas reflejan los constraints del esquema:
        //    cedula único, máx 20 chars; correo único, máx 100.
        //
        //    Contraseña segura (requerimiento #14):
        //      - mínimo 8 caracteres
        //      - al menos una mayúscula
        //      - al menos una minúscula
        //      - al menos un símbolo
        //    La regla Password::min(8)->mixedCase()->symbols() encapsula esto.
        $datos = $request->validate([
            'cedula'          => ['required', 'string', 'max:20', 'unique:Usuario,cedula'],
            'nombre'          => ['required', 'string', 'max:100'],
            'correo'          => ['required', 'email', 'max:100', 'unique:Usuario,correo'],
            'contrasena'      => [
                'required',
                'confirmed',
                Password::min(8)->mixedCase()->symbols(),
            ],
            'id_tipo_usuario' => [
                'required',
                'integer',
                // No permitimos auto-asignarse Admin desde el formulario público.
                Rule::in([
                    Usuario::ROL_GANADERO,
                    Usuario::ROL_VETERINARIO,
                ]),
            ],
        ]);

        // 2) Creación. El cast 'hashed' del modelo se encarga del bcrypt automáticamente.
        $usuario = Usuario::create($datos);

        // RF21 — Auditoría: registro de nuevo usuario.
        AuditoriaService::registrar(
            accion:       Auditoria::ACCION_REGISTRO,
            modulo:       Auditoria::MODULO_AUTH,
            descripcion:  "Nuevo usuario registrado: {$usuario->nombre} (cédula: {$usuario->cedula}, rol: {$usuario->id_tipo_usuario}).",
            datosDespues: ['cedula' => $usuario->cedula, 'nombre' => $usuario->nombre, 'id_tipo_usuario' => $usuario->id_tipo_usuario],
            cedula:       $usuario->cedula,
        );

        // 3) Respuesta diferenciada por tipo de cliente.
        if ($request->expectsJson()) {
            // Móvil: devolvemos token Sanctum para llamadas autenticadas siguientes.
            $token = $usuario->createToken('app-movil')->plainTextToken;

            return response()->json([
                'mensaje' => 'Usuario registrado correctamente',
                'usuario' => $usuario,
                'token'   => $token,
            ], 201);
        }

        // Correo de bienvenida.
        Mail::to($usuario->correo)->send(new WelcomeMail($usuario));

        // Web: iniciamos sesión y mandamos al dashboard.
        Auth::login($usuario);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('exito', 'Bienvenido, ' . $usuario->nombre);
    }

    // ==================================================================
    // LOGIN
    // ==================================================================

    public function login(Request $request): JsonResponse|RedirectResponse
    {
        // 1) Validación. Se loguea con CÉDULA + contraseña (no con email, porque
        //    en campo es más práctico tener la cédula a mano).
        $credenciales = $request->validate([
            'cedula'     => ['required', 'string'],
            'contrasena' => ['required', 'string'],
        ]);

        // 2) Mapeamos al campo que espera Auth::attempt (mira getAuthPassword()
        //    en el modelo, que devuelve $this->contrasena).
        $intento = [
            'cedula'   => $credenciales['cedula'],
            'password' => $credenciales['contrasena'], // alias requerido por el framework
        ];

        // 3) En web usamos el guard 'session' (Auth::attempt mantiene sesión),
        //    en móvil hacemos check manual y emitimos token Sanctum.
        if ($request->expectsJson()) {
            $usuario = Usuario::where('cedula', $credenciales['cedula'])->first();

            \Log::info('LOGIN_DEBUG', [
                'cedula'         => $credenciales['cedula'],
                'contrasena_len' => strlen($credenciales['contrasena']),
                'usuario_found'  => $usuario ? true : false,
                'hash_check'     => $usuario ? \Hash::check($credenciales['contrasena'], $usuario->contrasena) : false,
            ]);

            if (! $usuario || ! \Hash::check($credenciales['contrasena'], $usuario->contrasena)) {
                throw ValidationException::withMessages([
                    'cedula' => ['Credenciales inválidas.'],
                ]);
            }

            if (! $usuario->activo) {
                return response()->json([
                    'message' => 'Tu cuenta está desactivada. Contactá al administrador.',
                ], 403);
            }

            \Log::info('LOGIN_DEBUG_2', ['paso' => 'activo ok']);

            // Borra tokens previos del mismo dispositivo (opcional, pero ordena la tabla).
            $usuario->tokens()->where('name', 'app-movil')->delete();
            $token = $usuario->createToken('app-movil')->plainTextToken;

            \Log::info('LOGIN_DEBUG_3', ['paso' => 'token creado', 'token_len' => strlen($token)]);

            // RF21 — Auditoría: login desde la app móvil.
            AuditoriaService::registrar(
                accion:      Auditoria::ACCION_LOGIN,
                modulo:      Auditoria::MODULO_AUTH,
                descripcion: "Login exitoso (API) para {$usuario->nombre} (cédula: {$usuario->cedula}).",
                cedula:      $usuario->cedula,
            );

            \Log::info('LOGIN_DEBUG_4', ['paso' => 'auditoria ok']);

            return response()->json([
                'mensaje' => 'Login exitoso',
                'usuario' => $usuario->load('tipoUsuario'),
                'token'   => $token,
            ]);
        }

        // Camino web (sesión).
        if (! Auth::attempt($intento, $request->boolean('recordarme'))) {
            throw ValidationException::withMessages([
                'cedula' => 'Credenciales inválidas.',
            ]);
        }

        // Bloquear usuarios desactivados por el admin.
        if (! Auth::user()->activo) {
            Auth::logout();
            throw ValidationException::withMessages([
                'cedula' => 'Tu cuenta está desactivada. Contactá al administrador.',
            ]);
        }

        $request->session()->regenerate();

        // Notificación de inicio de sesión.
        $usuario = Auth::user();
        Mail::to($usuario->correo)->send(new LoginNotificationMail(
            usuario: $usuario,
            ip: $request->ip(),
            fechaHora: now()->format('d/m/Y H:i:s'),
        ));

        // RF21 — Auditoría: login web.
        AuditoriaService::registrar(
            accion:      Auditoria::ACCION_LOGIN,
            modulo:      Auditoria::MODULO_AUTH,
            descripcion: "Login exitoso (web) para {$usuario->nombre} (cédula: {$usuario->cedula}).",
            cedula:      $usuario->cedula,
        );

        return redirect()->intended(route('dashboard'));
    }

    // ==================================================================
    // LOGOUT
    // ==================================================================

    public function logout(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            $usuario = $request->user();

            // RF21 — Auditoría: logout API.
            AuditoriaService::registrar(
                accion:      Auditoria::ACCION_LOGOUT,
                modulo:      Auditoria::MODULO_AUTH,
                descripcion: "Logout (API) para {$usuario->nombre} (cédula: {$usuario->cedula}).",
                cedula:      $usuario->cedula,
            );

            // Móvil: revocamos solo el token usado en esta llamada (no todos).
            $usuario->currentAccessToken()->delete();

            return response()->json(['mensaje' => 'Sesión cerrada']);
        }

        // RF21 — Auditoría: logout web.
        $usuario = Auth::user();
        if ($usuario) {
            AuditoriaService::registrar(
                accion:      Auditoria::ACCION_LOGOUT,
                modulo:      Auditoria::MODULO_AUTH,
                descripcion: "Logout (web) para {$usuario->nombre} (cédula: {$usuario->cedula}).",
                cedula:      $usuario->cedula,
            );
        }

        // Web: cerramos sesión e invalidamos cookies.
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
