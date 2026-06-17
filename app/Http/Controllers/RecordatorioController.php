<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\Notificacion;
use App\Models\Recordatorio;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * RF22 — Recordatorios de re-pesaje por animal.
 *
 * Rutas que maneja:
 *   GET  animales/{animal}/recordatorios          → index (vista dentro de show)
 *   POST animales/{animal}/recordatorios          → store
 *   DELETE animales/{animal}/recordatorios/{rec}  → destroy
 */
class RecordatorioController extends Controller
{
    /**
     * Muestra recordatorios y notificaciones del animal.
     * Se embebe en animales/show, pero también puede cargarse independiente.
     */
    public function index(Animal $animal)
    {
        $this->authorize_acceso($animal);

        $recordatorios = $animal->recordatorios()
            ->with(['notificaciones' => fn ($q) => $q->latest('fecha_envio')->limit(3)])
            ->get();

        return view('recordatorios.index', compact('animal', 'recordatorios'));
    }

    /**
     * Crea un nuevo recordatorio para el animal.
     */
    public function store(Request $request, Animal $animal)
    {
        $this->authorize_acceso($animal);

        $datos = $request->validate([
            'frecuencia'   => ['required', 'in:semanal,quincenal,mensual,trimestral'],
            'fecha_inicio' => ['required', 'date', 'date_format:Y-m-d'],
        ], [
            'frecuencia.required'   => 'Seleccioná una frecuencia.',
            'frecuencia.in'         => 'Frecuencia no válida.',
            'fecha_inicio.required' => 'Ingresá la fecha de inicio.',
            'fecha_inicio.date'     => 'La fecha de inicio no es válida.',
        ]);

        $datos['arete'] = $animal->arete;

        Recordatorio::create($datos);

        return back()->with('exito', 'Recordatorio configurado correctamente.');
    }

    /**
     * Elimina un recordatorio (y sus notificaciones por cascade).
     */
    public function destroy(Animal $animal, Recordatorio $recordatorio)
    {
        $this->authorize_acceso($animal);

        if ($recordatorio->arete !== $animal->arete) {
            abort(403, 'Este recordatorio no pertenece al animal indicado.');
        }

        $recordatorio->delete();

        return back()->with('exito', 'Recordatorio eliminado.');
    }

    // ---------------------------------------------------------------
    // Helper privado — verifica que el usuario pueda gestionar el animal
    // ---------------------------------------------------------------

    private function authorize_acceso(Animal $animal): void
    {
        $usuario = auth()->user();

        // Admin ve todo
        if ($usuario->esAdmin()) {
            return;
        }

        // Ganadero: solo sus propias fincas
        if ($usuario->esGanadero()) {
            abort_unless(
                $animal->finca->cedula === $usuario->cedula,
                403,
                'No tenés permiso para gestionar este animal.'
            );
            return;
        }

        // Veterinario: solo fincas asignadas
        abort_unless(
            $animal->finca->veterinarios->contains('cedula', $usuario->cedula),
            403,
            'No tenés permiso para gestionar este animal.'
        );
    }
}
