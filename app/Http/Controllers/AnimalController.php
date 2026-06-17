<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnimalRequest;
use App\Models\Auditoria;
use App\Models\Animal;
use App\Models\Estado;
use App\Models\Finca;
use App\Models\Raza;
use App\Models\Sexo;
use App\Services\AuditoriaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * AnimalController — CRUD de animales (capa web).
 *
 * Reglas de acceso (igual que Fincas, transitivas a través de la finca):
 *   ┌──────────────┬───────────────────────────────────────────────────┐
 *   │ Rol          │ Qué ve / qué puede hacer                          │
 *   ├──────────────┼───────────────────────────────────────────────────┤
 *   │ Admin        │ Todos los animales. CRUD completo.                │
 *   │ Ganadero     │ Animales en SUS fincas. CRUD completo.            │
 *   │ Veterinario  │ Animales en fincas asignadas. SOLO LECTURA.       │
 *   └──────────────┴───────────────────────────────────────────────────┘
 *
 * El filtrado por finca se delega al ya implementado en Finca:
 *   - "fincas a las que el usuario tiene acceso" se calcula con `fincasVisibles()`,
 *     y los animales son los que tienen id_finca en ese conjunto.
 */
class AnimalController extends Controller
{
    // ==================================================================
    // LISTAR — GET /animales (opcionalmente filtrado por ?finca=ID)
    // ==================================================================
    public function index(Request $request): View
    {
        $idsFincasVisibles = $this->fincasVisiblesIds();

        $query = Animal::query()
            ->with(['finca', 'raza', 'sexo', 'estado'])
            ->whereIn('id_finca', $idsFincasVisibles);

        if ($request->filled('finca') && in_array((int) $request->finca, $idsFincasVisibles, true)) {
            $query->where('id_finca', $request->finca);
        }

        // RF08 — Búsqueda por arete o nombre
        if ($request->filled('buscar')) {
            $term = $request->input('buscar');
            $query->where(function ($q) use ($term) {
                $q->where('arete', 'like', "%{$term}%")
                  ->orWhere('nombre', 'like', "%{$term}%");
            });
        }

        // RF12 — Filtro por estado
        if ($request->filled('estado')) {
            $query->whereHas('estado', fn ($q) => $q->where('estado', $request->input('estado')));
        }

        $animales = $query->orderBy('arete')->get();

        $fincaSeleccionada = $request->filled('finca')
            ? Finca::find($request->finca)
            : null;

        $estados = \App\Models\Estado::orderBy('estado')->get();

        return view('animales.index', compact('animales', 'fincaSeleccionada', 'estados'));
    }

    // ==================================================================
    // CREAR — GET /animales/create (opcionalmente ?finca=ID)
    // ==================================================================
    public function create(Request $request): View
    {
        $fincas = $this->fincasVisibles()->get();

        return view('animales.create', [
            'fincas'           => $fincas,
            'razas'            => Raza::orderBy('raza')->get(),
            'sexos'            => Sexo::orderBy('sexo')->get(),
            'estados'          => Estado::orderBy('estado')->get(),
            'fincaSeleccionada'=> $request->finca, // para preseleccionar en el form
        ]);
    }

    // ==================================================================
    // GUARDAR — POST /animales
    // ==================================================================
    public function store(AnimalRequest $request): RedirectResponse
    {
        $datos = $request->validated();

        // Validación extra de seguridad: no se puede crear un animal en una
        // finca que no es del usuario (a menos que sea admin).
        $this->validarFincaPermitida((int) $datos['id_finca']);

        $animal = Animal::create($datos);

        // RF21 — Auditoría.
        AuditoriaService::registrar(
            accion:       Auditoria::ACCION_CREAR,
            modulo:       Auditoria::MODULO_ANIMALES,
            descripcion:  "Animal '{$animal->arete}' ({$animal->nombre}) creado en finca ID {$animal->id_finca}.",
            datosDespues: $animal->toArray(),
        );

        return redirect()
            ->route('animales.index', ['finca' => $datos['id_finca']])
            ->with('exito', 'Animal registrado correctamente.');
    }

    // ==================================================================
    // VER — GET /animales/{animal}
    // ==================================================================
    public function show(Animal $animal): View
    {
        $this->autorizarAcceso($animal);

        $animal->load([
            'finca', 'raza', 'sexo', 'estado', 'pesajes',
            'recordatorios.notificaciones',   // RF22
        ]);

        return view('animales.show', compact('animal'));
    }

    // ==================================================================
    // EDITAR — GET /animales/{animal}/edit
    // ==================================================================
    public function edit(Animal $animal): View
    {
        $this->autorizarEdicion($animal);

        return view('animales.edit', [
            'animal'  => $animal,
            'fincas'  => $this->fincasVisibles()->get(),
            'razas'   => Raza::orderBy('raza')->get(),
            'sexos'   => Sexo::orderBy('sexo')->get(),
            'estados' => Estado::orderBy('estado')->get(),
        ]);
    }

    // ==================================================================
    // ACTUALIZAR — PUT /animales/{animal}
    // ==================================================================
    public function update(AnimalRequest $request, Animal $animal): RedirectResponse
    {
        $this->autorizarEdicion($animal);

        $datos = $request->validated();
        $this->validarFincaPermitida((int) $datos['id_finca']);

        // Quitamos arete del payload — la PK no se cambia al editar.
        unset($datos['arete']);

        $original = $animal->toArray();
        $animal->update($datos);

        // RF21 — Auditoría.
        AuditoriaService::registrar(
            accion:       Auditoria::ACCION_ACTUALIZAR,
            modulo:       Auditoria::MODULO_ANIMALES,
            descripcion:  "Animal '{$animal->arete}' actualizado.",
            datosAntes:   $original,
            datosDespues: $animal->toArray(),
        );

        return redirect()
            ->route('animales.index', ['finca' => $animal->id_finca])
            ->with('exito', 'Animal actualizado correctamente.');
    }

    // ==================================================================
    // ELIMINAR — DELETE /animales/{animal}
    // ==================================================================
    public function destroy(Animal $animal): RedirectResponse
    {
        $this->autorizarEdicion($animal);

        $idFinca  = $animal->id_finca;
        $snapshot = $animal->toArray();
        $animal->delete();

        // RF21 — Auditoría.
        AuditoriaService::registrar(
            accion:      Auditoria::ACCION_ELIMINAR,
            modulo:      Auditoria::MODULO_ANIMALES,
            descripcion: "Animal '{$snapshot['arete']}' eliminado.",
            datosAntes:  $snapshot,
        );

        return redirect()
            ->route('animales.index', ['finca' => $idFinca])
            ->with('exito', 'Animal eliminado.');
    }

    // ==================================================================
    // Helpers privados — el "secreto" del control de acceso
    // ==================================================================

    /**
     * Devuelve un QUERY de las fincas a las que el usuario tiene acceso.
     * Lo dejamos como query (no como colección) para encadenar where/whereIn
     * y para no traer datos innecesarios de la BD.
     */
    private function fincasVisibles()
    {
        $u = auth()->user();

        $q = Finca::query();

        if ($u->esGanadero()) {
            $q->where('cedula', $u->cedula);
        } elseif ($u->esVeterinario()) {
            $q->whereHas('veterinarios', fn ($vq) =>
                $vq->where('Veterinario_Finca.cedula', $u->cedula)
            );
        }
        // Admin: no filtra — todas.

        return $q;
    }

    /** Solo los ids de fincas visibles (para usar con whereIn). */
    private function fincasVisiblesIds(): array
    {
        return $this->fincasVisibles()->pluck('id_finca')->all();
    }

    /**
     * Aborta con 403 si la finca elegida no está en las visibles del usuario.
     * Esto evita que alguien edite el HTML y mande id_finca de una finca ajena.
     */
    private function validarFincaPermitida(int $idFinca): void
    {
        if (! in_array($idFinca, $this->fincasVisiblesIds(), true)) {
            abort(403, 'No tenés permiso para usar esa finca.');
        }
    }

    private function autorizarAcceso(Animal $animal): void
    {
        if (! in_array($animal->id_finca, $this->fincasVisiblesIds(), true)) {
            abort(403, 'No tenés permiso para ver este animal.');
        }
    }

    private function autorizarEdicion(Animal $animal): void
    {
        $u = auth()->user();

        // Veterinario nunca edita.
        if ($u->esVeterinario()) {
            abort(403, 'Los veterinarios no pueden modificar animales.');
        }

        $this->autorizarAcceso($animal);
    }
}
