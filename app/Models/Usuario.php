<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Sanctum\HasApiTokens;

/**
 * Modelo Usuario — entidad central de autenticación del sistema.
 *
 * Mapea a la tabla `Usuario` cuya PK es `cedula` (string, no autoincremental).
 * Extiende Authenticatable para permitir login web (sesión) y Sanctum (token API móvil).
 *
 * Roles disponibles (constantes ROL_*):
 *  - 1 Administrador : acceso total al sistema
 *  - 2 Ganadero      : gestiona sus propias fincas, animales y pesajes
 *  - 3 Veterinario   : ve fincas a las que está asignado
 */
class Usuario extends Authenticatable
{
    use HasApiTokens, Notifiable;

    // --- Configuración de la tabla (no usa convenciones Laravel por defecto) ---
    protected $table = 'Usuario';
    protected $primaryKey = 'cedula';
    public $incrementing = false;       // La cédula es string, no autoincremental
    protected $keyType = 'string';
    public $timestamps = false;         // El esquema no tiene created_at/updated_at

    // --- Constantes de rol: usar siempre estas en vez de hardcodear ids ---
    public const ROL_ADMIN       = 1;
    public const ROL_GANADERO    = 2;
    public const ROL_VETERINARIO = 3;

    /**
     * Campos asignables masivamente (Usuario::create([...])).
     */
    protected $fillable = [
        'cedula',
        'nombre',
        'correo',
        'contrasena',
        'id_tipo_usuario',
    ];

    /**
     * Campos ocultos al serializar a JSON (response API).
     * No queremos exponer el hash de la contraseña jamás.
     */
    protected $hidden = [
        'contrasena',
        'remember_token',
    ];

    /**
     * Casts automáticos. El cast 'hashed' hashea la contraseña al guardarla,
     * así nunca queda en texto plano en la BD aunque alguien olvide hashearla manualmente.
     */
    protected function casts(): array
    {
        return [
            'contrasena' => 'hashed',
        ];
    }

    // ------------------------------------------------------------------
    // Override del nombre del campo de contraseña
    // ------------------------------------------------------------------
    // Laravel busca por defecto el campo `password`. El esquema usa `contrasena`,
    // así que sobrescribimos este método para que Auth::attempt() compare bien.

    public function getAuthPassword(): string
    {
        return $this->contrasena;
    }

    // ------------------------------------------------------------------
    // Helpers de rol — usar en controladores, middleware y vistas Blade
    // ------------------------------------------------------------------

    /**
     * Devuelve true si el usuario tiene el rol indicado.
     * Acepta el id (int) o varios ids como argumentos.
     *
     *  $user->tieneRol(Usuario::ROL_ADMIN);
     *  $user->tieneRol(Usuario::ROL_ADMIN, Usuario::ROL_GANADERO);
     */
    public function tieneRol(int ...$ids): bool
    {
        return in_array((int) $this->id_tipo_usuario, $ids, true);
    }

    public function esAdmin(): bool        { return $this->tieneRol(self::ROL_ADMIN); }
    public function esGanadero(): bool     { return $this->tieneRol(self::ROL_GANADERO); }
    public function esVeterinario(): bool  { return $this->tieneRol(self::ROL_VETERINARIO); }

    // ------------------------------------------------------------------
    // Relaciones Eloquent
    // ------------------------------------------------------------------

    /** Tipo de usuario (rol) al que pertenece. */
    public function tipoUsuario(): BelongsTo
    {
        return $this->belongsTo(TipoUsuario::class, 'id_tipo_usuario', 'id_tipo_usuario');
    }

    /** Fincas que este usuario posee (caso Ganadero). */
    public function fincas(): HasMany
    {
        return $this->hasMany(Finca::class, 'cedula', 'cedula');
    }

    /** Fincas a las que está asignado como veterinario (tabla pivote Veterinario_Finca). */
    public function fincasAsignadas(): BelongsToMany
    {
        return $this->belongsToMany(
            Finca::class,
            'Veterinario_Finca',
            'cedula',
            'id_finca',
            'cedula',
            'id_finca'
        );
    }
}
