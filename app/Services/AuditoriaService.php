<?php

namespace App\Services;

use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * AuditoriaService — punto único de registro de eventos de auditoría (RF21).
 *
 * Todos los controladores y observers usan este servicio para crear registros
 * en la tabla Auditoria. Esto centraliza la lógica y garantiza consistencia.
 *
 * Uso típico en un controlador:
 *
 *   AuditoriaService::registrar(
 *       accion:      Auditoria::ACCION_CREAR,
 *       modulo:      Auditoria::MODULO_FINCAS,
 *       descripcion: "Finca '{$finca->nombre}' creada.",
 *       datosDespues: $finca->toArray(),
 *   );
 *
 * Uso en un observer (sin request disponible):
 *
 *   AuditoriaService::registrar(
 *       accion:      Auditoria::ACCION_CREAR,
 *       modulo:      Auditoria::MODULO_PESAJES,
 *       descripcion: "Pesaje #{$pesaje->id_pesaje} registrado.",
 *       datosDespues: $pesaje->toArray(),
 *       cedula:      $pesaje->usuario?->cedula,  // si conocemos el usuario
 *   );
 */
class AuditoriaService
{
    /**
     * Registra un evento de auditoría en la base de datos.
     *
     * @param  string  $accion  Slug de la acción (Auditoria::ACCION_*)
     * @param  string  $modulo  Módulo del sistema (Auditoria::MODULO_*)
     * @param  string  $descripcion  Texto legible para el administrador
     * @param  array|null  $datosAntes  Estado del recurso antes del cambio
     * @param  array|null  $datosDespues  Estado del recurso después del cambio
     * @param  string|null  $cedula  Cédula del usuario (si no se puede obtener del Auth)
     */
    public static function registrar(
        string $accion,
        string $modulo,
        string $descripcion,
        ?array $datosAntes = null,
        ?array $datosDespues = null,
        ?string $cedula = null,
    ): void {
        try {
            // Intentamos obtener el usuario autenticado si no se pasó la cédula.
            $cedulaFinal = $cedula ?? Auth::user()?->cedula;

            // Intentamos leer IP y User-Agent del request actual.
            $request = app(Request::class);
            $ip = $request?->ip();
            $userAgent = $request?->userAgent();

            Auditoria::create([
                'cedula_usuario' => $cedulaFinal,
                'accion' => $accion,
                'modulo' => $modulo,
                'descripcion' => $descripcion,
                'ip' => $ip,
                'user_agent' => $userAgent,
                'datos_antes' => $datosAntes,
                'datos_despues' => $datosDespues,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // La auditoría NUNCA debe bloquear la operación principal.
            // Registramos el fallo en el log del sistema como fallback.
            Log::error(
                'AuditoriaService::registrar falló: '.$e->getMessage(),
                ['accion' => $accion, 'modulo' => $modulo]
            );
        }
    }
}
