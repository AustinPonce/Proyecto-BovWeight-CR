<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Auditoria — registro inmutable de acciones del sistema (RF21).
 *
 * Cada fila representa un evento que ocurrió en el sistema: login, logout,
 * creación/actualización/eliminación de recursos, cambios de rol, etc.
 *
 * Los registros de auditoría son INMUTABLES por diseño:
 *   - No se actualizan (updated_at no existe).
 *   - No se eliminan desde la UI (solo el DBA puede hacerlo).
 *
 * Se crean exclusivamente a través de AuditoriaService::registrar().
 *
 * Slugs de módulo (constantes MODULE_*):
 *   auth, fincas, animales, pesajes, transacciones,
 *   veterinarios, usuarios, catalogos
 *
 * Slugs de acción comunes:
 *   login, logout, registro, crear, actualizar, eliminar,
 *   activar, desactivar, asignar, desasignar
 */
class Auditoria extends Model
{
    protected $table      = 'Auditoria';
    protected $primaryKey = 'id_auditoria';
    public    $timestamps = false;            // Solo created_at, no updated_at

    // Módulos del sistema
    public const MODULO_AUTH          = 'auth';
    public const MODULO_FINCAS        = 'fincas';
    public const MODULO_ANIMALES      = 'animales';
    public const MODULO_PESAJES       = 'pesajes';
    public const MODULO_TRANSACCIONES = 'transacciones';
    public const MODULO_VETERINARIOS  = 'veterinarios';
    public const MODULO_USUARIOS      = 'usuarios';
    public const MODULO_CATALOGOS     = 'catalogos';

    // Acciones del sistema
    public const ACCION_LOGIN        = 'login';
    public const ACCION_LOGOUT       = 'logout';
    public const ACCION_REGISTRO     = 'registro';
    public const ACCION_CREAR        = 'crear';
    public const ACCION_ACTUALIZAR   = 'actualizar';
    public const ACCION_ELIMINAR     = 'eliminar';
    public const ACCION_ACTIVAR      = 'activar';
    public const ACCION_DESACTIVAR   = 'desactivar';
    public const ACCION_ASIGNAR      = 'asignar';
    public const ACCION_DESASIGNAR   = 'desasignar';

    protected $fillable = [
        'cedula_usuario',
        'accion',
        'modulo',
        'descripcion',
        'ip',
        'user_agent',
        'datos_antes',
        'datos_despues',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'datos_antes'   => 'array',
            'datos_despues' => 'array',
            'created_at'    => 'datetime',
        ];
    }

    // ------------------------------------------------------------------
    // Relaciones
    // ------------------------------------------------------------------

    /** Usuario que realizó la acción. Nullable (intentos de login fallidos). */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'cedula_usuario', 'cedula');
    }

    // ------------------------------------------------------------------
    // Scopes para filtrado en el panel admin
    // ------------------------------------------------------------------

    public function scopeDeUsuario($query, ?string $cedula)
    {
        return $cedula ? $query->where('cedula_usuario', $cedula) : $query;
    }

    public function scopeDeModulo($query, ?string $modulo)
    {
        return $modulo ? $query->where('modulo', $modulo) : $query;
    }

    public function scopeEntreFechas($query, ?string $desde, ?string $hasta)
    {
        if ($desde) {
            $query->whereDate('created_at', '>=', $desde);
        }
        if ($hasta) {
            $query->whereDate('created_at', '<=', $hasta);
        }
        return $query;
    }

    public function scopeConBusqueda($query, ?string $texto)
    {
        return $texto
            ? $query->where('descripcion', 'like', "%{$texto}%")
            : $query;
    }
}
