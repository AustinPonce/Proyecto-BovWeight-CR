<?php

namespace Tests\Feature\Requests;

use App\Models\Animal;
use App\Models\Finca;
use App\Models\Usuario;
use Database\Seeders\CatalogosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnimalRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Cargar datos semilla de catálogos necesarios para las reglas exist (Raza, Sexo, Estado)
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

    /**
     * Helper para crear una finca asociada a un usuario Ganadero.
     */
    private function createTestFinca(Usuario $owner): Finca
    {
        return Finca::create([
            'nombre' => 'Finca de Prueba',
            'ubicacion' => 'San Carlos, Alajuela',
            'cedula' => $owner->cedula,
        ]);
    }

    // =========================================================================
    // PRUEBAS DE AUTORIZACIÓN (authorize)
    // =========================================================================

    /**
     * Prueba que un Administrador está autorizado para crear animales.
     */
    public function test_admin_autorizado_para_crear_animal(): void
    {
        $admin = $this->createTestUser(Usuario::ROL_ADMIN, '100000001');
        $finca = $this->createTestFinca($admin);

        $data = [
            'arete' => 'AR-ADMIN-01',
            'nombre' => 'Lola Admin',
            'id_raza' => 1, // Brahman (existente por seeder)
            'id_sexo' => 1, // Macho (existente por seeder)
            'id_estado' => 1, // Activo (existente por seeder)
            'id_finca' => $finca->id_finca,
        ];

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/animales', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('Animal', ['arete' => 'AR-ADMIN-01']);
    }

    /**
     * Prueba que un Ganadero está autorizado para crear animales en sus fincas.
     */
    public function test_ganadero_autorizado_para_crear_animal(): void
    {
        $ganadero = $this->createTestUser(Usuario::ROL_GANADERO, '100000002');
        $finca = $this->createTestFinca($ganadero);

        $data = [
            'arete' => 'AR-GANADERO-01',
            'nombre' => 'Lola Ganadero',
            'id_raza' => 1,
            'id_sexo' => 2, // Hembra
            'id_estado' => 1,
            'id_finca' => $finca->id_finca,
        ];

        $response = $this->actingAs($ganadero, 'sanctum')
            ->postJson('/api/animales', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('Animal', ['arete' => 'AR-GANADERO-01']);
    }

    /**
     * Prueba que un Veterinario no está autorizado para crear animales (403 Forbidden).
     */
    public function test_veterinario_no_autorizado_para_crear_animal(): void
    {
        $vet = $this->createTestUser(Usuario::ROL_VETERINARIO, '100000003');
        $ganadero = $this->createTestUser(Usuario::ROL_GANADERO, '100000002');
        $finca = $this->createTestFinca($ganadero);

        $data = [
            'arete' => 'AR-VET-01',
            'nombre' => 'Lola Vet',
            'id_raza' => 1,
            'id_sexo' => 2,
            'id_estado' => 1,
            'id_finca' => $finca->id_finca,
        ];

        $response = $this->actingAs($vet, 'sanctum')
            ->postJson('/api/animales', $data);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('Animal', ['arete' => 'AR-VET-01']);
    }

    /**
     * Prueba que un Invitado sin credenciales no está autorizado para crear animales (401 Unauthorized).
     */
    public function test_invitado_no_autorizado_para_crear_animal(): void
    {
        $data = [
            'arete' => 'AR-GUEST-01',
            'nombre' => 'Lola Guest',
            'id_raza' => 1,
            'id_sexo' => 2,
            'id_estado' => 1,
            'id_finca' => 1,
        ];

        $response = $this->postJson('/api/animales', $data);

        $response->assertStatus(401);
    }

    // =========================================================================
    // PRUEBAS DE VALIDACIONES
    // =========================================================================

    /**
     * Prueba que el campo arete es requerido.
     */
    public function test_arete_requerido(): void
    {
        $admin = $this->createTestUser(Usuario::ROL_ADMIN, '100000001');
        $finca = $this->createTestFinca($admin);

        $data = [
            'arete' => '', // vacío
            'nombre' => 'Sin Arete',
            'id_raza' => 1,
            'id_sexo' => 1,
            'id_estado' => 1,
            'id_finca' => $finca->id_finca,
        ];

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/animales', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['arete']);
    }

    /**
     * Prueba que el campo arete debe ser único en la tabla de Animales.
     */
    public function test_arete_unico(): void
    {
        $admin = $this->createTestUser(Usuario::ROL_ADMIN, '100000001');
        $finca = $this->createTestFinca($admin);

        // Crear animal previo
        Animal::create([
            'arete' => 'AR-DUPLICADO',
            'nombre' => 'Primero',
            'id_raza' => 1,
            'id_sexo' => 1,
            'id_estado' => 1,
            'id_finca' => $finca->id_finca,
        ]);

        $data = [
            'arete' => 'AR-DUPLICADO', // Valor duplicado
            'nombre' => 'Segundo',
            'id_raza' => 1,
            'id_sexo' => 1,
            'id_estado' => 1,
            'id_finca' => $finca->id_finca,
        ];

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/animales', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['arete']);
    }

    /**
     * Prueba que el campo arete no exceda los 30 caracteres.
     */
    public function test_arete_maximo_30_caracteres(): void
    {
        $admin = $this->createTestUser(Usuario::ROL_ADMIN, '100000001');
        $finca = $this->createTestFinca($admin);

        $data = [
            'arete' => str_repeat('X', 31), // 31 caracteres
            'nombre' => 'Arete Largo',
            'id_raza' => 1,
            'id_sexo' => 1,
            'id_estado' => 1,
            'id_finca' => $finca->id_finca,
        ];

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/animales', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['arete']);
    }

    /**
     * Prueba que id_raza, id_sexo, id_estado e id_finca son requeridos.
     */
    public function test_id_raza_id_sexo_id_estado_id_finca_son_requeridos(): void
    {
        $admin = $this->createTestUser(Usuario::ROL_ADMIN, '100000001');

        $data = [
            'arete' => 'AR-VALID-01',
            'nombre' => 'Lola Test',
            'id_raza' => null,
            'id_sexo' => null,
            'id_estado' => null,
            'id_finca' => null,
        ];

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/animales', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['id_raza', 'id_sexo', 'id_estado', 'id_finca']);
    }

    // =========================================================================
    // PRUEBAS DE VALIDACIONES EXISTS (Referencial)
    // =========================================================================

    /**
     * Prueba que id_raza debe existir en la tabla Raza.
     */
    public function test_id_raza_debe_existir_en_base_de_datos(): void
    {
        $admin = $this->createTestUser(Usuario::ROL_ADMIN, '100000001');
        $finca = $this->createTestFinca($admin);

        $data = [
            'arete' => 'AR-RAZA-999',
            'nombre' => 'Lola Raza Invalida',
            'id_raza' => 999, // Raza inexistente
            'id_sexo' => 1,
            'id_estado' => 1,
            'id_finca' => $finca->id_finca,
        ];

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/animales', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['id_raza']);
    }

    /**
     * Prueba que id_sexo debe existir en la tabla Sexo.
     */
    public function test_id_sexo_debe_existir_en_base_de_datos(): void
    {
        $admin = $this->createTestUser(Usuario::ROL_ADMIN, '100000001');
        $finca = $this->createTestFinca($admin);

        $data = [
            'arete' => 'AR-SEXO-999',
            'nombre' => 'Lola Sexo Invalida',
            'id_raza' => 1,
            'id_sexo' => 999, // Sexo inexistente
            'id_estado' => 1,
            'id_finca' => $finca->id_finca,
        ];

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/animales', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['id_sexo']);
    }

    /**
     * Prueba que id_estado debe existir en la tabla Estado.
     */
    public function test_id_estado_debe_existir_en_base_de_datos(): void
    {
        $admin = $this->createTestUser(Usuario::ROL_ADMIN, '100000001');
        $finca = $this->createTestFinca($admin);

        $data = [
            'arete' => 'AR-ESTADO-999',
            'nombre' => 'Lola Estado Invalida',
            'id_raza' => 1,
            'id_sexo' => 1,
            'id_estado' => 999, // Estado inexistente
            'id_finca' => $finca->id_finca,
        ];

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/animales', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['id_estado']);
    }

    /**
     * Prueba que id_finca debe existir en la tabla Finca.
     */
    public function test_id_finca_debe_existir_en_base_de_datos(): void
    {
        $admin = $this->createTestUser(Usuario::ROL_ADMIN, '100000001');

        $data = [
            'arete' => 'AR-FINCA-999',
            'nombre' => 'Lola Finca Invalida',
            'id_raza' => 1,
            'id_sexo' => 1,
            'id_estado' => 1,
            'id_finca' => 999, // Finca inexistente
        ];

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/animales', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['id_finca']);
    }
}
