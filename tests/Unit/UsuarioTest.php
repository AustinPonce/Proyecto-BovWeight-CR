<?php

namespace Tests\Unit;

use App\Models\Finca;
use App\Models\TipoUsuario;
use App\Models\Usuario;
use Database\Seeders\CatalogosSeeder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsuarioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Poblar la base de datos con los catálogos básicos (Tipo_usuario, etc.)
        $this->seed(CatalogosSeeder::class);
    }

    /**
     * Helper para crear un usuario de prueba manual.
     */
    private function createTestUser(int $rolId, string $cedula = '123456789'): Usuario
    {
        return Usuario::create([
            'cedula' => $cedula,
            'nombre' => 'Test User',
            'correo' => "test_{$cedula}@bovweight.cr",
            'contrasena' => 'password123',
            'id_tipo_usuario' => $rolId,
        ]);
    }

    /**
     * 1. test_usuario_es_admin
     */
    public function test_usuario_es_admin(): void
    {
        $usuario = $this->createTestUser(Usuario::ROL_ADMIN);

        $this->assertTrue($usuario->esAdmin());
        $this->assertFalse($usuario->esGanadero());
        $this->assertFalse($usuario->esVeterinario());
    }

    /**
     * 2. test_usuario_es_ganadero
     */
    public function test_usuario_es_ganadero(): void
    {
        $usuario = $this->createTestUser(Usuario::ROL_GANADERO);

        $this->assertFalse($usuario->esAdmin());
        $this->assertTrue($usuario->esGanadero());
        $this->assertFalse($usuario->esVeterinario());
    }

    /**
     * 3. test_usuario_es_veterinario
     */
    public function test_usuario_es_veterinario(): void
    {
        $usuario = $this->createTestUser(Usuario::ROL_VETERINARIO);

        $this->assertFalse($usuario->esAdmin());
        $this->assertFalse($usuario->esGanadero());
        $this->assertTrue($usuario->esVeterinario());
    }

    /**
     * 4. test_tiene_rol_funciona_correctamente
     */
    public function test_tiene_rol_funciona_correctamente(): void
    {
        $usuario = $this->createTestUser(Usuario::ROL_GANADERO);

        // Individual
        $this->assertTrue($usuario->tieneRol(Usuario::ROL_GANADERO));
        $this->assertFalse($usuario->tieneRol(Usuario::ROL_ADMIN));

        // Múltiples roles
        $this->assertTrue($usuario->tieneRol(Usuario::ROL_ADMIN, Usuario::ROL_GANADERO));
        $this->assertFalse($usuario->tieneRol(Usuario::ROL_ADMIN, Usuario::ROL_VETERINARIO));
    }

    /**
     * 5. test_get_auth_password_retorna_contrasena
     */
    public function test_get_auth_password_retorna_contrasena(): void
    {
        $usuario = $this->createTestUser(Usuario::ROL_ADMIN);

        // getAuthPassword() debe retornar el hash de la contraseña guardada
        $this->assertNotEmpty($usuario->getAuthPassword());
        $this->assertTrue(\Hash::check('password123', $usuario->getAuthPassword()));
    }

    /**
     * 6. test_relacion_tipo_usuario
     */
    public function test_relacion_tipo_usuario(): void
    {
        $usuario = $this->createTestUser(Usuario::ROL_ADMIN);

        // Verificar el método de relación
        $this->assertInstanceOf(BelongsTo::class, $usuario->tipoUsuario());

        // Verificar el resultado de la relación cargada
        $this->assertInstanceOf(TipoUsuario::class, $usuario->tipoUsuario);
        $this->assertEquals(Usuario::ROL_ADMIN, $usuario->tipoUsuario->id_tipo_usuario);
    }

    /**
     * 7. test_relacion_fincas
     */
    public function test_relacion_fincas(): void
    {
        $usuario = $this->createTestUser(Usuario::ROL_GANADERO);

        // Crear una finca perteneciente a este usuario
        $finca = Finca::create([
            'nombre' => 'Finca Test Relacion',
            'ubicacion' => 'San Carlos',
            'cedula' => $usuario->cedula,
        ]);

        // Verificar el método de relación
        $this->assertInstanceOf(HasMany::class, $usuario->fincas());

        // Verificar el resultado de la relación cargada
        $this->assertTrue($usuario->fincas->contains($finca));
        $this->assertEquals($finca->id_finca, $usuario->fincas->first()->id_finca);
    }
}
