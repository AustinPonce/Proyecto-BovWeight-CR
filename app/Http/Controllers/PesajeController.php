<?php

namespace App\Http\Controllers;

use App\Contracts\ICalculadorPeso;
use App\Http\Requests\PesajeRequest;
use App\Models\Animal;
use App\Models\Pesaje;
use App\Models\TipoPesaje;
use App\Services\CalculadorPesoContext;
use App\Strategies\FormulaManualStrategy;
use App\Strategies\FotoIAWeightStrategy;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * PesajeController — registro y consulta de pesajes (capa web).
 *
 * Este controller es el CLIENTE del patrón Strategy implementado por el equipo
 * de SOLID:
 *
 *   ┌────────────────────────────────────────────────────────────────┐
 *   │  PesajeController                                              │
 *   │       │                                                        │
 *   │       ▼ usa                                                    │
 *   │  CalculadorPesoContext  ◀── inyecta ──  ICalculadorPeso        │
 *   │                                          ▲     ▲               │
 *   │                              implementa  │     │ implementa    │
 *   │                                          │     │               │
 *   │                            FormulaManualStrategy                │
 *   │                                                FotoIAWeightStrategy │
 *   └────────────────────────────────────────────────────────────────┘
 *
 * El controller elige la Strategy según el tipo del request (manual/foto),
 * calcula el peso y persiste el Pesaje. Los Observers attached al modelo
 * Pesaje se disparan automáticamente (auditoría, notificación, alerta).
 *
 * Acceso por rol:
 *   - Admin     → ve, registra y elimina cualquier pesaje
 *   - Ganadero  → ve, registra y elimina pesajes de animales en SUS fincas
 *   - Veterinario → ve pesajes de animales en fincas asignadas (NO escribe)
 */
class PesajeController extends Controller
{
    // ==================================================================
    // LISTAR  — GET /pesajes  (opcionalmente ?animal=arete o ?finca=id)
    // ==================================================================
    public function index(Request $request): View
    {
        $usuario = $request->user();

        // Solo pesajes de animales visibles para el usuario.
        $aretesVisibles = Animal::visibleFor($usuario)->pluck('arete');

        $query = Pesaje::query()
            ->whereIn('arete', $aretesVisibles)
            ->with(['animal.finca']);

        // Filtros opcionales.
        if ($request->filled('animal')) {
            $query->where('arete', $request->input('animal'));
        }
        if ($request->filled('finca')) {
            $aretesDeFinca = Animal::where('id_finca', $request->integer('finca'))->pluck('arete');
            $query->whereIn('arete', $aretesDeFinca);
        }

        $pesajes = $query->orderByDesc('fecha')->paginate(20);

        return view('pesajes.index', compact('pesajes'));
    }

    // ==================================================================
    // FORMULARIO DE REGISTRO  — GET /pesajes/create?animal=arete
    // ==================================================================
    public function create(Request $request): View
    {
        // Animales sobre los que el ganadero/admin puede registrar pesaje.
        $animales = Animal::visibleFor($request->user())
            ->with('finca')
            ->orderBy('arete')
            ->get();

        return view('pesajes.create', [
            'animales'           => $animales,
            'animalPreseleccionado' => $request->input('animal'),
        ]);
    }

    // ==================================================================
    // GUARDAR  — POST /pesajes
    // ==================================================================
    public function store(PesajeRequest $request): RedirectResponse
    {
        $datos = $request->validated();

        // 1) Verificar que el animal sea uno que el usuario tiene permitido.
        $this->autorizarAnimal($request, $datos['arete']);

        // 2) Elegir la estrategia (PATRÓN STRATEGY).
        $strategy = $this->resolverStrategy($datos['tipo']);
        $context  = new CalculadorPesoContext($strategy);

        // 3) Armar el array de datos para la estrategia.
        $datosCalculo = $datos['tipo'] === 'manual'
            ? [
                'largo_cuerpo'       => (float) $datos['largo_cuerpo'],
                'altura'             => (float) $datos['altura'],
                'perimetro_toracico' => (float) $datos['perimetro_toracico'],
            ]
            : [
                // En 2E acá pasamos el path de la imagen al microservicio ML.
                // Por ahora la FotoIAWeightStrategy devuelve un mock random.
                'imagen' => null,
            ];

        // 4) Calcular el peso.
        $peso = $context->calcular($datosCalculo);

        // 5) Si vino imagen, guardarla en storage/app/public/pesajes.
        $pathImagen = null;
        if ($request->hasFile('imagen')) {
            $pathImagen = $request->file('imagen')->store('pesajes', 'public');
        }

        // 6) Crear el Pesaje. Los Observers se disparan automáticamente acá:
        //    - AuditoriaPesajeObserver  → log de auditoría
        //    - CrearNotificacionObserver → notificación si hay recordatorio
        //    - VerificarPesoObserver    → alerta sanitaria si peso < 100
        Pesaje::create([
            'fecha'          => Carbon::now(),
            'peso'           => $peso,
            'imagen'         => $pathImagen,
            'sincronizado'   => 1,
            'arete'          => $datos['arete'],
            'id_tipo_pesaje' => $this->resolverTipoPesajeId($datos['tipo']),
        ]);

        return redirect()
            ->route('animales.show', $datos['arete'])
            ->with('exito', "Pesaje registrado: {$peso} kg.");
    }

    // ==================================================================
    // VER  — GET /pesajes/{pesaje}
    // ==================================================================
    public function show(Pesaje $pesaje): View
    {
        $this->autorizarVerPesaje($pesaje);

        $pesaje->load(['animal.finca', 'animal.raza']);

        return view('pesajes.show', compact('pesaje'));
    }

    // ==================================================================
    // ELIMINAR  — DELETE /pesajes/{pesaje}
    // ==================================================================
    public function destroy(Pesaje $pesaje): RedirectResponse
    {
        $this->autorizarEditarPesaje($pesaje);

        // Si tenía imagen, la borramos del storage para no dejar huérfanos.
        if ($pesaje->imagen) {
            Storage::disk('public')->delete($pesaje->imagen);
        }

        $arete = $pesaje->arete;
        $pesaje->delete();

        return redirect()
            ->route('animales.show', $arete)
            ->with('exito', 'Pesaje eliminado.');
    }

    // ==================================================================
    // Helpers privados
    // ==================================================================

    /** Elige la implementación de ICalculadorPeso según el tipo. */
    private function resolverStrategy(string $tipo): ICalculadorPeso
    {
        return match ($tipo) {
            'manual' => new FormulaManualStrategy(),
            'foto'   => new FotoIAWeightStrategy(),
        };
    }

    /**
     * Mapea el tipo (slug) al id_tipo_pesaje real en la BD.
     * Cachea el lookup para no repetir queries innecesarias.
     */
    private function resolverTipoPesajeId(string $tipo): int
    {
        static $cache = [];

        if (! isset($cache[$tipo])) {
            $nombreBuscado = $tipo === 'foto'
                ? 'Estimación por Fotografía'
                : 'Pesaje Manual con Báscula';

            $cache[$tipo] = TipoPesaje::where('tipo_pesaje', $nombreBuscado)
                ->value('id_tipo_pesaje');
        }

        return (int) $cache[$tipo];
    }

    /** Aborta si el usuario no puede registrar pesaje sobre ese animal. */
    private function autorizarAnimal(Request $request, string $arete): void
    {
        if (! Animal::visibleFor($request->user())->whereKey($arete)->exists()) {
            abort(403, 'No tenés permiso para registrar pesaje sobre este animal.');
        }
    }

    /** Cualquier rol con acceso al animal puede VER su historial. */
    private function autorizarVerPesaje(Pesaje $pesaje): void
    {
        if (! Animal::visibleFor(auth()->user())->whereKey($pesaje->arete)->exists()) {
            abort(403, 'No tenés permiso para ver este pesaje.');
        }
    }

    /** Solo Admin y Ganadero (dueño) pueden eliminar pesajes. */
    private function autorizarEditarPesaje(Pesaje $pesaje): void
    {
        $u = auth()->user();

        if ($u->esVeterinario()) {
            abort(403, 'Los veterinarios no pueden eliminar pesajes.');
        }

        $this->autorizarVerPesaje($pesaje);
    }
}
