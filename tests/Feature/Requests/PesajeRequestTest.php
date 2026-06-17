<?php

namespace Tests\Feature\Requests;

use App\Models\Animal;
use App\Models\Finca;
use App\Models\Usuario;
use Database\Seeders\CatalogosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PesajeRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Cargar los catálogos requeridos (Tipo de Pesaje, etc.)
        $this->seed(CatalogosSeeder::class);

        // Simular el almacenamiento público para evitar escribir archivos reales en disco
        Storage::fake('public');

        // Simular llamadas HTTP al microservicio de ML por defecto
        Http::fake([
            '*/estimar' => Http::response(['peso_estimado' => 450.50, 'confianza' => 0.95], 200),
        ]);
    }

    /**
     * Helper para crear un usuario de prueba rápido.
     */
    private function createTestUser(int $rolId, string $cedula): Usuario
    {
        return Usuario::create([
            'cedula'          => $cedula,
            'nombre'          => 'Usuario Test ' . $cedula,
            'correo'          => 'test_' . $cedula . '@bovweight.cr',
            'contrasena'      => 'bovweight2026',
            'id_tipo_usuario' => $rolId,
        ]);
    }

    /**
     * Helper para crear una finca y un animal asociados.
     */
    private function createTestAnimal(Usuario $owner, string $arete = 'AR-TEST-01'): Animal
    {
        $finca = Finca::create([
            'nombre'    => 'Finca de Prueba',
            'ubicacion' => 'San Carlos',
            'cedula'    => $owner->cedula,
        ]);

        return Animal::create([
            'arete'     => $arete,
            'nombre'    => 'Bovino Test',
            'id_raza'   => 1,
            'id_sexo'   => 1,
            'id_estado' => 1,
            'id_finca'  => $finca->id_finca,
        ]);
    }

    // =========================================================================
    // PRUEBAS DE AUTORIZACIÓN (authorize)
    // =========================================================================

    /**
     * Prueba que un Administrador está autorizado para crear pesajes.
     */
    public function test_admin_autorizado_para_crear_pesaje(): void
    {
        $admin = $this->createTestUser(Usuario::ROL_ADMIN, '100000001');
        $animal = $this->createTestAnimal($admin, 'AR-ADMIN');

        $data = [
            'arete'              => $animal->arete,
            'tipo'               => 'manual',
            'largo_cuerpo'       => 150,
            'altura'             => 120,
            'perimetro_toracico' => 160,
        ];

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/pesajes', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('Pesaje', ['arete' => $animal->arete]);
    }

    /**
     * Prueba que un Ganadero está autorizado para crear pesajes en sus fincas.
     */
    public function test_ganadero_autorizado_para_crear_pesaje(): void
    {
        $ganadero = $this->createTestUser(Usuario::ROL_GANADERO, '100000002');
        $animal = $this->createTestAnimal($ganadero, 'AR-GANADERO');

        $data = [
            'arete'              => $animal->arete,
            'tipo'               => 'manual',
            'largo_cuerpo'       => 140,
            'altura'             => 110,
            'perimetro_toracico' => 150,
        ];

        $response = $this->actingAs($ganadero, 'sanctum')
            ->postJson('/api/pesajes', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('Pesaje', ['arete' => $animal->arete]);
    }

    /**
     * Prueba que un Veterinario no está autorizado para registrar pesajes (403 Forbidden).
     */
    public function test_veterinario_no_autorizado_para_crear_pesaje(): void
    {
        $vet = $this->createTestUser(Usuario::ROL_VETERINARIO, '100000003');
        $ganadero = $this->createTestUser(Usuario::ROL_GANADERO, '100000002');
        $animal = $this->createTestAnimal($ganadero, 'AR-VET');

        $data = [
            'arete'              => $animal->arete,
            'tipo'               => 'manual',
            'largo_cuerpo'       => 140,
            'altura'             => 110,
            'perimetro_toracico' => 150,
        ];

        $response = $this->actingAs($vet, 'sanctum')
            ->postJson('/api/pesajes', $data);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('Pesaje', ['arete' => $animal->arete]);
    }

    /**
     * Prueba que un Invitado sin credenciales no está autorizado para crear pesajes (401 Unauthorized).
     */
    public function test_invitado_no_autorizado_para_crear_pesaje(): void
    {
        $data = [
            'arete'              => 'AR-INVITADO',
            'tipo'               => 'manual',
            'largo_cuerpo'       => 140,
            'altura'             => 110,
            'perimetro_toracico' => 150,
        ];

        $response = $this->postJson('/api/pesajes', $data);

        $response->assertStatus(401);
    }

    // =========================================================================
    // PRUEBAS DE VALIDACIONES COMUNES
    // =========================================================================

    /**
     * Prueba que el campo arete es requerido.
     */
    public function test_arete_es_requerido(): void
    {
        $admin = $this->createTestUser(Usuario::ROL_ADMIN, '100000001');

        $data = [
            'arete'              => '', // vacío
            'tipo'               => 'manual',
            'largo_cuerpo'       => 150,
            'altura'             => 120,
            'perimetro_toracico' => 160,
        ];

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/pesajes', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['arete']);
    }

    /**
     * Prueba que el arete del animal debe existir en la base de datos.
     */
    public function test_arete_debe_existir_en_base_de_datos(): void
    {
        $admin = $this->createTestUser(Usuario::ROL_ADMIN, '100000001');

        $data = [
            'arete'              => 'AR-INEXISTENTE', // No registrado
            'tipo'               => 'manual',
            'largo_cuerpo'       => 150,
            'altura'             => 120,
            'perimetro_toracico' => 160,
        ];

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/pesajes', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['arete']);
    }

    /**
     * Prueba que el tipo de pesaje es requerido.
     */
    public function test_tipo_de_pesaje_es_requerido(): void
    {
        $admin = $this->createTestUser(Usuario::ROL_ADMIN, '100000001');
        $animal = $this->createTestAnimal($admin);

        $data = [
            'arete'              => $animal->arete,
            'tipo'               => '', // vacío
            'largo_cuerpo'       => 150,
            'altura'             => 120,
            'perimetro_toracico' => 160,
        ];

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/pesajes', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['tipo']);
    }

    /**
     * Prueba que el tipo de pesaje debe ser 'manual' o 'foto'.
     */
    public function test_tipo_de_pesaje_debe_ser_valido(): void
    {
        $admin = $this->createTestUser(Usuario::ROL_ADMIN, '100000001');
        $animal = $this->createTestAnimal($admin);

        $data = [
            'arete'              => $animal->arete,
            'tipo'               => 'invalido', // no es ni manual ni foto
            'largo_cuerpo'       => 150,
            'altura'             => 120,
            'perimetro_toracico' => 160,
        ];

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/pesajes', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['tipo']);
    }

    // =========================================================================
    // PRUEBAS DE TIPO MANUAL
    // =========================================================================

    /**
     * Prueba que al seleccionar tipo 'manual' las medidas corporales son obligatorias.
     */
    public function test_tipo_manual_requiere_medidas_corporales(): void
    {
        $admin = $this->createTestUser(Usuario::ROL_ADMIN, '100000001');
        $animal = $this->createTestAnimal($admin);

        $data = [
            'arete'              => $animal->arete,
            'tipo'               => 'manual',
            'largo_cuerpo'       => null, // requerido
            'altura'             => null, // requerido
            'perimetro_toracico' => null, // requerido
        ];

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/pesajes', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['largo_cuerpo', 'altura', 'perimetro_toracico']);
    }

    // =========================================================================
    // PRUEBAS DE TIPO FOTO
    // =========================================================================

    /**
     * Prueba que al seleccionar tipo 'foto' la imagen es obligatoria.
     */
    public function test_tipo_foto_requiere_imagen(): void
    {
        $admin = $this->createTestUser(Usuario::ROL_ADMIN, '100000001');
        $animal = $this->createTestAnimal($admin);

        $data = [
            'arete'  => $animal->arete,
            'tipo'   => 'foto',
            'imagen' => null, // requerido para tipo foto
        ];

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/pesajes', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['imagen']);
    }

    /**
     * Prueba que la imagen enviada debe ser un archivo de tipo imagen válido.
     */
    public function test_imagen_debe_ser_una_imagen_valida(): void
    {
        $admin = $this->createTestUser(Usuario::ROL_ADMIN, '100000001');
        $animal = $this->createTestAnimal($admin);

        // Crear un archivo ficticio que no es imagen (un archivo de texto)
        $archivoInvalido = UploadedFile::fake()->create('bovino.txt', 10);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/pesajes', [
                'arete'  => $animal->arete,
                'tipo'   => 'foto',
                'imagen' => $archivoInvalido,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['imagen']);
    }

    /**
     * Prueba que la imagen acepta formatos válidos y rechaza otros.
     */
    public function test_imagen_formatos_jpg_jpeg_png_webp(): void
    {
        $admin = $this->createTestUser(Usuario::ROL_ADMIN, '100000001');
        $animal = $this->createTestAnimal($admin);

        // 1. Caso Válido (PNG)
        $pngImage = UploadedFile::fake()->image('bovino.png');
        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/pesajes', [
                'arete'  => $animal->arete,
                'tipo'   => 'foto',
                'imagen' => $pngImage,
            ]);
        $response->assertStatus(201); // Debería guardarse correctamente

        // 2. Caso Inválido (PDF)
        $pdfFile = UploadedFile::fake()->create('bovino.pdf', 100);
        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/pesajes', [
                'arete'  => $animal->arete,
                'tipo'   => 'foto',
                'imagen' => $pdfFile,
            ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['imagen']);
    }

    /**
     * Prueba que la imagen no debe exceder los 5 MB (5120 KB).
     */
    public function test_imagen_maximo_5_mb(): void
    {
        $admin = $this->createTestUser(Usuario::ROL_ADMIN, '100000001');
        $animal = $this->createTestAnimal($admin);

        // Crear una imagen de 6 MB (6144 KB), superando el límite de 5120 KB
        $largeImage = UploadedFile::fake()->create('bovino_gigante.jpg', 6000);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/pesajes', [
                'arete'  => $animal->arete,
                'tipo'   => 'foto',
                'imagen' => $largeImage,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['imagen']);
    }
}
