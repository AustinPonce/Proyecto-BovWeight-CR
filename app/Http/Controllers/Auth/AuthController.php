<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
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
        //    cedula único, máx 20 chars; correo único, máx 100; contraseña mínima 8.
        $datos = $request->validate([
            'cedula'          => ['required', 'string', 'max:20', 'unique:Usuario,cedula'],
            'nombre'          => ['required', 'string', 'max:100'],
            'correo'          => ['required', 'email', 'max:100', 'unique:Usuario,correo'],
            'contrasena'      => ['required', 'string', 'min:8', 'confirmed'],
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

            if (! $usuario || ! \Hash::check($credenciales['contrasena'], $usuario->contrasena)) {
                throw ValidationException::withMessages([
                    'cedula' => ['Credenciales inválidas.'],
                ]);
            }

            // Borra tokens previos del mismo dispositivo (opcional, pero ordena la tabla).
            $usuario->tokens()->where('name', 'app-movil')->delete();
            $token = $usuario->createToken('app-movil')->plainTextToken;

            return response()->json([
                'mensaje' => 'Login exitoso',
                'usuario' => $usuario,
                'token'   => $token,
            ]);
        }

        // Camino web (sesión).
        if (! Auth::attempt($intento, $request->boolean('recordarme'))) {
            throw ValidationException::withMessages([
                'cedula' => 'Credenciales inválidas.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    // ==================================================================
    // LOGOUT
    // ==================================================================

    public function logout(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            // Móvil: revocamos solo el token usado en esta llamada (no todos).
            $request->user()->currentAccessToken()->delete();

            return response()->json(['mensaje' => 'Sesión cerrada']);
        }

        // Web: cerramos sesión e invalidamos cookies.
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
