<?php

namespace Tests\Unit;

use App\Models\Animal;
use App\Models\Finca;
use App\Models\Usuario;
use Database\Seeders\CatalogosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FincaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Poblar base de datos con catálogos básicos
        $this->seed(CatalogosSeeder::class);
    }

    /**
     * Helper para crear un usuario.
     */
    private function createTestUser(int $rolId, string $cedula): Usuario
    {
        return Usuario::create([
            'cedula'          => $cedula,
            'nombre'          => 'Usuario ' . $cedula,
            'correo'          => 'test_' . $cedula . '@bovweight.cr',
            'contrasena'      => 'password123',
            'id_tipo_usuario' => $rolId,
        ]);
    }

    /**
     * Helper para crear una finca.
     */
    private function createTestFinca(Usuario $owner, string $nombre): Finca
    {
        return Finca::create([
            'nombre'    => $nombre,
            'ubicacion' => 'San Carlos, Costa Rica',
            'cedula'    => $owner->cedula,
        ]);
    }

    // =========================================================================
    // PRUEBAS DE CONFIGURACIÓN DEL MODELO
    // =========================================================================

    /**
     * 7. test_fillable_contiene_campos_correctos
     */
    public function test_fillable_contiene_campos_correctos(): void
    {
        $finca = new Finca();

        $this->assertEquals(['nombre', 'ubicacion', 'cedula'], $finca->getFillable());
    }

    /**
     * 8. test_primary_key_correcta
     */
    public function test_primary_key_correcta(): void
    {
        $finca = new Finca();

        $this->assertEquals('id_finca', $finca->getKeyName());
    }

    // =========================================================================
    // PRUEBAS DE RELACIONES (Eloquent Relationships)
    // =========================================================================

    /**
     * 1. test_relacion_usuario
     */
    public function test_relacion_usuario(): void
    {
        $ganadero = $this->createTestUser(Usuario::ROL_GANADERO, '100000002');
        $finca = $this->createTestFinca($ganadero, 'Finca Bella Vista');

        // Verificar tipo de relación
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $finca->usuario());

        // Verificar valor cargado
        $this->assertInstanceOf(Usuario::class, $finca->usuario);
        $this->assertEquals($ganadero->cedula, $finca->usuario->cedula);
    }

    /**
     * 2. test_relacion_animales
     */
    public function test_relacion_animales(): void
    {
        $ganadero = $this->createTestUser(Usuario::ROL_GANADERO, '100000002');
        $finca = $this->createTestFinca($ganadero, 'Finca Bella Vista');

        $animal = Animal::create([
            'arete'     => 'AR-FINCA-TEST',
            'nombre'    => 'Lola',
            'id_raza'   => 1,
            'id_sexo'   => 2,
            'id_estado' => 1,
            'id_finca'  => $finca->id_finca,
        ]);

        // Verificar tipo de relación
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $finca->animales());

        // Verificar valor cargado
        $this->assertTrue($finca->animales->contains($animal));
    }

    /**
     * 3. test_relacion_veterinarios
     */
    public function test_relacion_veterinarios(): void
    {
        $ganadero = $this->createTestUser(Usuario::ROL_GANADERO, '100000002');
        $finca = $this->createTestFinca($ganadero, 'Finca Bella Vista');

        $vet = $this->createTestUser(Usuario::ROL_VETERINARIO, '100000003');

        // Adjuntar veterinario a la finca (escribe en tabla pivote Veterinario_Finca)
        $finca->veterinarios()->attach($vet->cedula);

        // Verificar tipo de relación
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $finca->veterinarios());

        // Verificar valor cargado
        $this->assertTrue($finca->veterinarios->contains($vet));
    }

    // =========================================================================
    // PRUEBAS DE SCOPE (Local Scopes)
    // =========================================================================

    /**
     * 4. test_scope_visible_for_admin
     */
    public function test_scope_visible_for_admin(): void
    {
        $admin = $this->createTestUser(Usuario::ROL_ADMIN, '100000001');
        $ganadero1 = $this->createTestUser(Usuario::ROL_GANADERO, '100000002');
        $ganadero2 = $this->createTestUser(Usuario::ROL_GANADERO, '100000003');

        $finca1 = $this->createTestFinca($ganadero1, 'Finca 1');
        $finca2 = $this->createTestFinca($ganadero2, 'Finca 2');

        // Administrador debe ver todas las fincas
        $visibleFincas = Finca::visibleFor($admin)->get();

        $this->assertCount(2, $visibleFincas);
        $this->assertTrue($visibleFincas->contains($finca1));
        $this->assertTrue($visibleFincas->contains($finca2));
    }

    /**
     * 5. test_scope_visible_for_ganadero
     */
    public function test_scope_visible_for_ganadero(): void
    {
        $ganadero1 = $this->createTestUser(Usuario::ROL_GANADERO, '100000002');
        $ganadero2 = $this->createTestUser(Usuario::ROL_GANADERO, '100000003');

        $finca1 = $this->createTestFinca($ganadero1, 'Finca Propia');
        $finca2 = $this->createTestFinca($ganadero2, 'Finca Ajena');

        // El Ganadero 1 solo debe ver su finca
        $visibleFincas = Finca::visibleFor($ganadero1)->get();

        $this->assertCount(1, $visibleFincas);
        $this->assertTrue($visibleFincas->contains($finca1));
        $this->assertFalse($visibleFincas->contains($finca2));
    }

    /**
     * 6. test_scope_visible_for_veterinario
     */
    public function test_scope_visible_for_veterinario(): void
    {
        $vet = $this->createTestUser(Usuario::ROL_VETERINARIO, '100000003');
        $ganadero = $this->createTestUser(Usuario::ROL_GANADERO, '100000002');

        $finca1 = $this->createTestFinca($ganadero, 'Finca Asignada');
        $finca2 = $this->createTestFinca($ganadero, 'Finca No Asignada');

        // Asignar el veterinario a la finca 1
        $finca1->veterinarios()->attach($vet->cedula);

        // El Veterinario debe ver únicamente las fincas que tiene asignadas
        $visibleFincas = Finca::visibleFor($vet)->get();

        $this->assertCount(1, $visibleFincas);
        $this->assertTrue($visibleFincas->contains($finca1));
        $this->assertFalse($visibleFincas->contains($finca2));
    }
}
