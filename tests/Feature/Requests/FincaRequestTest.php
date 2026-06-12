<?php

namespace Tests\Feature\Requests;

use App\Models\Usuario;
use Database\Seeders\CatalogosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FincaRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Cargar datos semilla
        $this->seed(CatalogosSeeder::class);
    }

    /**
     * Helper para crear un usuario de prueba rápido con rol específico.
     */
    private function createTestUser(int $rolId, string $cedula): Usuario
    {
        return Usuario::create([
            'cedula' => $cedula,
            'nombre' => 'Usuario Test '.$cedula,
            'correo' => 'test_'.$cedula.'@bovweight.cr',
            'contrasena' => 'bovweight2026',
            'id_tipo_usuario' => $rolId,
        ]);
    }

    // =========================================================================
    // PRUEBAS DE AUTORIZACIÓN (authorize)
    // =========================================================================

    /**
     * Prueba que un Administrador está autorizado para crear fincas.
     */
    public function test_admin_autorizado_para_crear_finca(): void
    {
        $admin = $this->createTestUser(Usuario::ROL_ADMIN, '100000001');

        $data = [
            'nombre' => 'Finca del Administrador',
            'ubicacion' => 'San José, Costa Rica',
        ];

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/fincas', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('Finca', ['nombre' => 'Finca del Administrador']);
    }

    /**
     * Prueba que un Ganadero está autorizado para crear fincas.
     */
    public function test_ganadero_autorizado_para_crear_finca(): void
    {
        $ganadero = $this->createTestUser(Usuario::ROL_GANADERO, '100000002');

        $data = [
            'nombre' => 'Finca del Ganadero',
            'ubicacion' => 'Pérez Zeledón, San José',
        ];

        $response = $this->actingAs($ganadero, 'sanctum')
            ->postJson('/api/fincas', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('Finca', ['nombre' => 'Finca del Ganadero']);
    }

    /**
     * Prueba que un Veterinario no está autorizado para crear fincas (403 Forbidden).
     */
    public function test_veterinario_no_autorizado_para_crear_finca(): void
    {
        $vet = $this->createTestUser(Usuario::ROL_VETERINARIO, '100000003');

        $data = [
            'nombre' => 'Finca del Veterinario',
            'ubicacion' => 'Liberia, Guanacaste',
        ];

        $response = $this->actingAs($vet, 'sanctum')
            ->postJson('/api/fincas', $data);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('Finca', ['nombre' => 'Finca del Veterinario']);
    }

    /**
     * Prueba que un Invitado sin credenciales no está autorizado para crear fincas (401 Unauthorized).
     */
    public function test_invitado_no_autorizado_para_crear_finca(): void
    {
        $data = [
            'nombre' => 'Finca de Invitado',
            'ubicacion' => 'Alajuela, Costa Rica',
        ];

        $response = $this->postJson('/api/fincas', $data);

        $response->assertStatus(401);
    }

    // =========================================================================
    // PRUEBAS DE VALIDACIÓN (rules)
    // =========================================================================

    /**
     * Prueba que el campo nombre es requerido.
     */
    public function test_nombre_es_requerido(): void
    {
        $admin = $this->createTestUser(Usuario::ROL_ADMIN, '100000001');

        $data = [
            'nombre' => '', // vacío
            'ubicacion' => 'Guápiles, Limón',
        ];

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/fincas', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['nombre']);
    }

    /**
     * Prueba que el campo nombre no debe superar los 100 caracteres.
     */
    public function test_nombre_no_debe_exceder_100_caracteres(): void
    {
        $admin = $this->createTestUser(Usuario::ROL_ADMIN, '100000001');

        $data = [
            'nombre' => str_repeat('A', 101), // 101 caracteres
            'ubicacion' => 'Guápiles, Limón',
        ];

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/fincas', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['nombre']);
    }

    /**
     * Prueba que el campo ubicacion es requerido.
     */
    public function test_ubicacion_es_requerida(): void
    {
        $admin = $this->createTestUser(Usuario::ROL_ADMIN, '100000001');

        $data = [
            'nombre' => 'Finca El Encanto',
            'ubicacion' => '', // vacío
        ];

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/fincas', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['ubicacion']);
    }

    /**
     * Prueba que el campo ubicacion no debe superar los 255 caracteres.
     */
    public function test_ubicacion_no_debe_exceder_255_caracteres(): void
    {
        $admin = $this->createTestUser(Usuario::ROL_ADMIN, '100000001');

        $data = [
            'nombre' => 'Finca El Encanto',
            'ubicacion' => str_repeat('B', 256), // 256 caracteres
        ];

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/fincas', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['ubicacion']);
    }
}
