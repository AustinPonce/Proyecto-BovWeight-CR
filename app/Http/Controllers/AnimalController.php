<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnimalRequest;
use App\Models\Animal;
use App\Models\Estado;
use App\Models\Finca;
use App\Models\Raza;
use App\Models\Sexo;
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

        // Filtro opcional: si vienen con ?finca=5, mostramos solo esa.
        // (Solo si el usuario tiene acceso a esa finca.)
        if ($request->filled('finca') && in_array((int) $request->finca, $idsFincasVisibles, true)) {
            $query->where('id_finca', $request->finca);
        }

        $animales = $query->orderBy('arete')->get();

        // Para que la vista pueda mostrar el nombre de la finca seleccionada (si la hay).
        $fincaSeleccionada = $request->filled('finca')
            ? Finca::find($request->finca)
            : null;

        return view('animales.index', compact('animales', 'fincaSeleccionada'));
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

        Animal::create($datos);

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

        $animal->load(['finca', 'raza', 'sexo', 'estado', 'pesajes']);

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

        $animal->update($datos);

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

        $idFinca = $animal->id_finca;
        $animal->delete();

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
