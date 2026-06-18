<?php

namespace App\Http\Controllers;

use App\Contracts\ICalculadorPeso;
use App\Exceptions\NoBovinoDetectadoException;
use App\Http\Requests\PesajeRequest;
use App\Models\Animal;
use App\Models\Pesaje;
use App\Models\TipoPesaje;
use App\Services\CalculadorPesoContext;
use App\Strategies\FormulaManualStrategy;
use App\Strategies\FotoIAWeightStrategy;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
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
    // CALCULAR (paso 1 del flujo RF04) — POST /pesajes/calcular
    // Calcula el peso estimado y guarda la imagen (si aplica) SIN crear
    // el registro en BD. Devuelve JSON para que el JS muestre el resultado.
    // ==================================================================
    public function calcular(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'arete'              => ['required', 'string', Rule::exists('Animal', 'arete')],
            'tipo'               => ['required', Rule::in(['manual', 'foto'])],
            'largo_cuerpo'       => ['required_if:tipo,manual', 'nullable', 'numeric', 'min:30', 'max:300'],
            'altura'             => ['required_if:tipo,manual', 'nullable', 'numeric', 'min:30', 'max:200'],
            'perimetro_toracico' => ['required_if:tipo,manual', 'nullable', 'numeric', 'min:30', 'max:300'],
            'imagen'             => ['required_if:tipo,foto', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'categoria_animal'   => ['nullable', Rule::in(['auto', 'cria', 'cria_neonato', 'cria_joven', 'cria_mayor', 'joven', 'adulto'])],
        ]);

        $this->autorizarAnimal($request, $datos['arete']);

        // Guardar imagen (si aplica) para tenerla disponible en el paso 2.
        $pathImagen = null;
        if ($request->hasFile('imagen')) {
            $pathImagen = $request->file('imagen')->store('pesajes', 'public');
        }

        // Elegir estrategia y calcular.
        $strategy = $this->resolverStrategy($datos['tipo']);
        $context  = new CalculadorPesoContext($strategy);

        $datosCalculo = $datos['tipo'] === 'manual'
            ? [
                'largo_cuerpo'       => (float) $datos['largo_cuerpo'],
                'altura'             => (float) $datos['altura'],
                'perimetro_toracico' => (float) $datos['perimetro_toracico'],
            ]
            : [
                'imagen'    => $pathImagen,
                'categoria' => $datos['categoria_animal'] ?? 'auto',
            ];

        try {
            $pesoOriginal = $context->calcular($datosCalculo);
        } catch (NoBovinoDetectadoException $e) {
            if ($pathImagen) {
                Storage::disk('public')->delete($pathImagen);
            }
            return response()->json(['error' => $e->mensajeUsuario], 422);
        }

        // RF13 — Factor de corrección por raza.
        $animal        = Animal::with('raza')->whereKey($datos['arete'])->first();
        $factorRaza    = (float) ($animal->raza->factor_correccion ?? 1.0);
        $pesoCalculado = round($pesoOriginal * $factorRaza, 2);

        return response()->json([
            'peso_calculado'     => $pesoCalculado,
            'peso_original_calc' => round($pesoOriginal, 2),
            'factor_raza_calc'   => $factorRaza,
            'imagen_guardada'    => $pathImagen ?? '',
        ]);
    }

    // ==================================================================
    // GUARDAR (paso 2 del flujo RF04) — POST /pesajes
    // Recibe los datos pre-calculados del paso 1 y persiste el Pesaje.
    // ==================================================================
    public function store(PesajeRequest $request): RedirectResponse
    {
        $datos = $request->validated();

        // 1) Verificar que el animal sea uno que el usuario tiene permitido.
        $this->autorizarAnimal($request, $datos['arete']);

        // 2) Imagen y peso: si vienen del paso 1 (calcular), ya están listos.
        //    Si no (flujo directo sin JS), calcular de nuevo.
        $pathImagen    = null;
        $pesoOriginal  = 0;
        $factorRaza    = 1.0;
        $pesoCorregido = 0;

        $usoDosParos = ! empty($datos['imagen_guardada']) || ! empty($datos['peso_calculado']);

        if ($usoDosParos) {
            // --- Paso 2: valores ya calculados en calcular() ---
            $pathImagen    = $datos['imagen_guardada'] ?: null;
            $pesoOriginal  = (float) ($datos['peso_original_calc'] ?? $datos['peso_calculado']);
            $factorRaza    = (float) ($datos['factor_raza_calc'] ?? 1.0);
            $pesoCorregido = round((float) $datos['peso_calculado'], 2);
        } else {
            // --- Flujo directo (fallback si JS está desactivado) ---
            if ($request->hasFile('imagen')) {
                $pathImagen = $request->file('imagen')->store('pesajes', 'public');
            }

            $strategy = $this->resolverStrategy($datos['tipo']);
            $context  = new CalculadorPesoContext($strategy);

            $datosCalculo = $datos['tipo'] === 'manual'
                ? [
                    'largo_cuerpo'       => (float) $datos['largo_cuerpo'],
                    'altura'             => (float) $datos['altura'],
                    'perimetro_toracico' => (float) $datos['perimetro_toracico'],
                ]
                : ['imagen' => $pathImagen];

            try {
                $pesoOriginal = $context->calcular($datosCalculo);
            } catch (NoBovinoDetectadoException $e) {
                if ($pathImagen) {
                    Storage::disk('public')->delete($pathImagen);
                }
                return redirect()
                    ->route('pesajes.create', ['animal' => $datos['arete']])
                    ->withInput()
                    ->withErrors(['imagen' => $e->mensajeUsuario]);
            }

            $animal        = Animal::with('raza')->whereKey($datos['arete'])->first();
            $factorRaza    = (float) ($animal->raza->factor_correccion ?? 1.0);
            $pesoCorregido = round($pesoOriginal * $factorRaza, 2);
        }

        $pesoFinal = $pesoCorregido;

        // RF04 — Si el usuario ingresó un peso manual en la pantalla de confirmación,
        //        ese valor reemplaza la estimación como peso oficial del registro.
        $fueCorrectionManual = false;
        if (! empty($datos['peso_manual']) && (float) $datos['peso_manual'] > 0) {
            $pesoFinal           = round((float) $datos['peso_manual'], 2);
            $fueCorrectionManual = true;
        }

        // 3) Crear el Pesaje. Los Observers se disparan automáticamente:
        //    - AuditoriaPesajeObserver  → log de auditoría + BD
        //    - CrearNotificacionObserver → notificación si hay recordatorio
        //    - VerificarPesoObserver    → alerta sanitaria si peso < 100
        Pesaje::create([
            'fecha'          => Carbon::now(),
            'peso'           => $pesoFinal,
            'peso_original'  => round($pesoOriginal, 2),
            'factor_raza'    => $factorRaza,
            'peso_corregido' => $pesoCorregido,
            'imagen'         => $pathImagen,
            'sincronizado'   => 1,
            'arete'          => $datos['arete'],
            'id_tipo_pesaje' => $this->resolverTipoPesajeId($datos['tipo']),
        ]);

        $msgFactor    = $factorRaza != 1.0
            ? " (original: {$pesoOriginal} kg × factor {$factorRaza} raza)"
            : '';
        $msgCorreccion = $fueCorrectionManual
            ? ' [peso ingresado manualmente]'
            : '';

        return redirect()
            ->route('animales.show', $datos['arete'])
            ->with('exito', "Pesaje registrado: {$pesoFinal} kg.{$msgFactor}{$msgCorreccion}");
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
