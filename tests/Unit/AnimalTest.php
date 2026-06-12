<?php

namespace Tests\Unit;

use App\Models\Animal;
use App\Models\Estado;
use App\Models\Finca;
use App\Models\Pesaje;
use App\Models\Raza;
use App\Models\Sexo;
use App\Models\Usuario;
use Database\Seeders\CatalogosSeeder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnimalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Poblar base de datos con los catálogos básicos
        $this->seed(CatalogosSeeder::class);
    }

    /**
     * Helper para crear un usuario.
     */
    private function createTestUser(int $rolId, string $cedula): Usuario
    {
        return Usuario::create([
            'cedula' => $cedula,
            'nombre' => 'Usuario '.$cedula,
            'correo' => 'test_'.$cedula.'@bovweight.cr',
            'contrasena' => 'password123',
            'id_tipo_usuario' => $rolId,
        ]);
    }

    /**
     * Helper para crear una finca.
     */
    private function createTestFinca(Usuario $owner, string $nombre): Finca
    {
        return Finca::create([
            'nombre' => $nombre,
            'ubicacion' => 'San Carlos',
            'cedula' => $owner->cedula,
        ]);
    }

    /**
     * Helper para crear un animal.
     */
    private function createTestAnimal(string $arete, Finca $finca): Animal
    {
        return Animal::create([
            'arete' => $arete,
            'nombre' => 'Bovino '.$arete,
            'id_raza' => 1, // Brahman
            'id_sexo' => 1, // Macho
            'id_estado' => 1, // Activo
            'id_finca' => $finca->id_finca,
        ]);
    }

    // =========================================================================
    // PRUEBAS DE CONFIGURACIÓN DEL MODELO
    // =========================================================================

    /**
     * 6. test_modelo_animal_usa_arete_como_primary_key
     */
    public function test_modelo_animal_usa_arete_como_primary_key(): void
    {
        $animal = new Animal;

        $this->assertEquals('arete', $animal->getKeyName());
        $this->assertFalse($animal->getIncrementing());
        $this->assertEquals('string', $animal->getKeyType());
    }

    /**
     * 7. test_fillable_contiene_campos_correctos
     */
    public function test_fillable_contiene_campos_correctos(): void
    {
        $animal = new Animal;

        $expected = ['arete', 'nombre', 'id_raza', 'id_sexo', 'id_estado', 'id_finca'];

        $this->assertEquals($expected, $animal->getFillable());
    }

    // =========================================================================
    // PRUEBAS DE RELACIONES (Eloquent Relationships)
    // =========================================================================

    /**
     * 1. test_relacion_finca
     */
    public function test_relacion_finca(): void
    {
        $ganadero = $this->createTestUser(Usuario::ROL_GANADERO, '100000002');
        $finca = $this->createTestFinca($ganadero, 'Finca Santa Fe');
        $animal = $this->createTestAnimal('AR-01', $finca);

        // Verificar el método de relación
        $this->assertInstanceOf(BelongsTo::class, $animal->finca());

        // Verificar la relación cargada
        $this->assertInstanceOf(Finca::class, $animal->finca);
        $this->assertEquals($finca->id_finca, $animal->finca->id_finca);
    }

    /**
     * 2. test_relacion_raza
     */
    public function test_relacion_raza(): void
    {
        $ganadero = $this->createTestUser(Usuario::ROL_GANADERO, '100000002');
        $finca = $this->createTestFinca($ganadero, 'Finca Santa Fe');
        $animal = $this->createTestAnimal('AR-02', $finca);

        // Verificar el método de relación
        $this->assertInstanceOf(BelongsTo::class, $animal->raza());

        // Verificar la relación cargada
        $this->assertInstanceOf(Raza::class, $animal->raza);
        $this->assertEquals(1, $animal->raza->id_raza); // Brahman
    }

    /**
     * 3. test_relacion_sexo
     */
    public function test_relacion_sexo(): void
    {
        $ganadero = $this->createTestUser(Usuario::ROL_GANADERO, '100000002');
        $finca = $this->createTestFinca($ganadero, 'Finca Santa Fe');
        $animal = $this->createTestAnimal('AR-03', $finca);

        // Verificar el método de relación
        $this->assertInstanceOf(BelongsTo::class, $animal->sexo());

        // Verificar la relación cargada
        $this->assertInstanceOf(Sexo::class, $animal->sexo);
        $this->assertEquals(1, $animal->sexo->id_sexo); // Macho
    }

    /**
     * 4. test_relacion_estado
     */
    public function test_relacion_estado(): void
    {
        $ganadero = $this->createTestUser(Usuario::ROL_GANADERO, '100000002');
        $finca = $this->createTestFinca($ganadero, 'Finca Santa Fe');
        $animal = $this->createTestAnimal('AR-04', $finca);

        // Verificar el método de relación
        $this->assertInstanceOf(BelongsTo::class, $animal->estado());

        // Verificar la relación cargada
        $this->assertInstanceOf(Estado::class, $animal->estado);
        $this->assertEquals(1, $animal->estado->id_estado); // Activo
    }

    /**
     * 5. test_relacion_pesajes
     */
    public function test_relacion_pesajes(): void
    {
        $ganadero = $this->createTestUser(Usuario::ROL_GANADERO, '100000002');
        $finca = $this->createTestFinca($ganadero, 'Finca Santa Fe');
        $animal = $this->createTestAnimal('AR-05', $finca);

        // Crear un registro de pesaje manual asociado
        $pesaje = Pesaje::create([
            'fecha' => now(),
            'peso' => 420.50,
            'imagen' => null,
            'sincronizado' => 1,
            'arete' => $animal->arete,
            'id_tipo_pesaje' => 2, // Pesaje Manual
        ]);

        // Verificar el método de relación
        $this->assertInstanceOf(HasMany::class, $animal->pesajes());

        // Verificar la relación cargada
        $this->assertTrue($animal->pesajes->contains($pesaje));
    }

    // =========================================================================
    // PRUEBAS DE SCOPE (Local Scopes)
    // =========================================================================

    /**
     * 8. test_scope_visible_for_admin
     */
    public function test_scope_visible_for_admin(): void
    {
        $admin = $this->createTestUser(Usuario::ROL_ADMIN, '100000001');
        $ganadero1 = $this->createTestUser(Usuario::ROL_GANADERO, '100000002');
        $ganadero2 = $this->createTestUser(Usuario::ROL_GANADERO, '100000003');

        $finca1 = $this->createTestFinca($ganadero1, 'Finca 1');
        $finca2 = $this->createTestFinca($ganadero2, 'Finca 2');

        $animal1 = $this->createTestAnimal('AR-A', $finca1);
        $animal2 = $this->createTestAnimal('AR-B', $finca2);

        // El administrador debe ver todos los animales del sistema
        $visibleAnimals = Animal::visibleFor($admin)->get();

        $this->assertCount(2, $visibleAnimals);
        $this->assertTrue($visibleAnimals->contains($animal1));
        $this->assertTrue($visibleAnimals->contains($animal2));
    }

    /**
     * 9. test_scope_visible_for_ganadero
     */
    public function test_scope_visible_for_ganadero(): void
    {
        $ganadero1 = $this->createTestUser(Usuario::ROL_GANADERO, '100000002');
        $ganadero2 = $this->createTestUser(Usuario::ROL_GANADERO, '100000003');

        $finca1 = $this->createTestFinca($ganadero1, 'Finca Ganadero 1');
        $finca2 = $this->createTestFinca($ganadero2, 'Finca Ganadero 2');

        $animal1 = $this->createTestAnimal('AR-G1', $finca1);
        $animal2 = $this->createTestAnimal('AR-G2', $finca2);

        // El Ganadero 1 debe ver UNICAMENTE los animales en su finca (finca1)
        $visibleAnimals = Animal::visibleFor($ganadero1)->get();

        $this->assertCount(1, $visibleAnimals);
        $this->assertTrue($visibleAnimals->contains($animal1));
        $this->assertFalse($visibleAnimals->contains($animal2));
    }
}
