<?php

namespace App\Http\Controllers;

use App\Http\Requests\FincaRequest;
use App\Models\Finca;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * FincaController — CRUD de fincas (capa web).
 *
 * Reglas de acceso por rol:
 *   ┌──────────────┬──────────────────────────────────────────────────┐
 *   │ Rol          │ Qué ve / qué puede hacer                         │
 *   ├──────────────┼──────────────────────────────────────────────────┤
 *   │ Admin        │ Todas las fincas. Crea/edita/elimina cualquiera. │
 *   │ Ganadero     │ Solo SUS fincas (cedula = cedula del usuario).   │
 *   │              │ Crea/edita/elimina solo las suyas.               │
 *   │ Veterinario  │ Solo fincas a las que está ASIGNADO (vía pivote).│
 *   │              │ Lectura: NO crea/edita/elimina.                  │
 *   └──────────────┴──────────────────────────────────────────────────┘
 *
 * El control de acceso se aplica en DOS niveles:
 *   1) Ruta (middleware `rol:` filtra por rol antes de entrar al controller).
 *   2) Controller (chequeo de ownership: "esta finca es mía o no?").
 */
class FincaController extends Controller
{
    // ==================================================================
    // LISTAR  — GET /fincas
    // ==================================================================
    public function index(): View
    {
        $usuario = auth()->user();

        // Query base: empieza con TODAS y la vamos filtrando según el rol.
        $query = Finca::query()->with(['usuario', 'animales']);

        if ($usuario->esGanadero()) {
            // El ganadero solo ve sus propias fincas.
            $query->where('cedula', $usuario->cedula);
        } elseif ($usuario->esVeterinario()) {
            // El veterinario ve solo fincas a las que está asignado.
            // whereHas filtra fincas que tienen al menos una relación coincidente
            // en la tabla pivote Veterinario_Finca.
            $query->whereHas('veterinarios', fn ($q) =>
                $q->where('Veterinario_Finca.cedula', $usuario->cedula)
            );
        }
        // Admin no filtra nada → ve todas.

        $fincas = $query->orderBy('nombre')->get();

        return view('fincas.index', compact('fincas'));
    }

    // ==================================================================
    // MOSTRAR FORMULARIO DE CREACIÓN  — GET /fincas/create
    // ==================================================================
    public function create(): View
    {
        // Si el usuario es admin, puede crear una finca a nombre de cualquier
        // ganadero, así que cargamos la lista. Si es ganadero, no necesitamos
        // lista — su cédula se asigna automáticamente.
        $ganaderos = auth()->user()->esAdmin()
            ? Usuario::where('id_tipo_usuario', Usuario::ROL_GANADERO)
                ->orderBy('nombre')
                ->get(['cedula', 'nombre'])
            : collect();

        return view('fincas.create', compact('ganaderos'));
    }

    // ==================================================================
    // GUARDAR  — POST /fincas
    // ==================================================================
    public function store(FincaRequest $request): RedirectResponse
    {
        $datos = $request->validated();

        // Si es Admin y eligió ganadero, lo respetamos. Si no, el dueño es
        // automáticamente el usuario que está logueado.
        $datos['cedula'] = $request->user()->esAdmin() && $request->filled('cedula')
            ? $request->input('cedula')
            : $request->user()->cedula;

        Finca::create($datos);

        return redirect()
            ->route('fincas.index')
            ->with('exito', 'Finca creada correctamente.');
    }

    // ==================================================================
    // VER UNA FINCA  — GET /fincas/{finca}
    // ==================================================================
    public function show(Finca $finca): View
    {
        $this->autorizarAcceso($finca);

        $finca->load(['usuario', 'animales', 'veterinarios']);

        return view('fincas.show', compact('finca'));
    }

    // ==================================================================
    // MOSTRAR FORMULARIO DE EDICIÓN  — GET /fincas/{finca}/edit
    // ==================================================================
    public function edit(Finca $finca): View
    {
        $this->autorizarEdicion($finca);

        $ganaderos = auth()->user()->esAdmin()
            ? Usuario::where('id_tipo_usuario', Usuario::ROL_GANADERO)
                ->orderBy('nombre')
                ->get(['cedula', 'nombre'])
            : collect();

        return view('fincas.edit', compact('finca', 'ganaderos'));
    }

    // ==================================================================
    // ACTUALIZAR  — PUT/PATCH /fincas/{finca}
    // ==================================================================
    public function update(FincaRequest $request, Finca $finca): RedirectResponse
    {
        $this->autorizarEdicion($finca);

        $datos = $request->validated();

        // Solo Admin puede reasignar dueño.
        if ($request->user()->esAdmin() && $request->filled('cedula')) {
            $datos['cedula'] = $request->input('cedula');
        }

        $finca->update($datos);

        return redirect()
            ->route('fincas.index')
            ->with('exito', 'Finca actualizada correctamente.');
    }

    // ==================================================================
    // ELIMINAR  — DELETE /fincas/{finca}
    // ==================================================================
    public function destroy(Finca $finca): RedirectResponse
    {
        $this->autorizarEdicion($finca);

        $finca->delete();

        return redirect()
            ->route('fincas.index')
            ->with('exito', 'Finca eliminada.');
    }

    // ==================================================================
    // Helpers privados — chequeos de ownership
    // ==================================================================

    /**
     * Lectura: Admin ve todo, Ganadero solo las propias, Veterinario solo
     * las asignadas. Si el usuario no califica, 403.
     */
    private function autorizarAcceso(Finca $finca): void
    {
        $u = auth()->user();

        if ($u->esAdmin()) return;

        if ($u->esGanadero() && $finca->cedula === $u->cedula) return;

        if ($u->esVeterinario()
            && $finca->veterinarios()->where('Veterinario_Finca.cedula', $u->cedula)->exists()) {
            return;
        }

        abort(403, 'No tenés permiso para ver esta finca.');
    }

    /**
     * Edición / eliminación: solo Admin o el Ganadero dueño.
     * El Veterinario NUNCA edita ni elimina (solo lectura).
     */
    private function autorizarEdicion(Finca $finca): void
    {
        $u = auth()->user();

        if ($u->esAdmin()) return;

        if ($u->esGanadero() && $finca->cedula === $u->cedula) return;

        abort(403, 'No tenés permiso para modificar esta finca.');
    }
}
