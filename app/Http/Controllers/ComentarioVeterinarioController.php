<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\ComentarioVeterinario;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComentarioVeterinarioController extends Controller
{
    /** Listado de comentarios de un animal (visible para ganadero y vet asignado) */
    public function index(Animal $animal): View
    {
        $this->autorizarVer($animal);

        $animal->load([
            'finca',
            'comentariosVeterinario' => fn ($q) => $q->with('veterinario')->orderByDesc('fecha'),
        ]);

        return view('comentarios.index', compact('animal'));
    }

    /** Guardar comentario (solo veterinarios) */
    public function store(Request $request, Animal $animal): RedirectResponse
    {
        $usuario = $request->user();

        if (! $usuario->esVeterinario()) {
            abort(403, 'Solo los veterinarios pueden dejar comentarios.');
        }

        $this->autorizarVer($animal);

        $request->validate([
            'comentario' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        ComentarioVeterinario::create([
            'arete'              => $animal->arete,
            'cedula_veterinario' => $usuario->cedula,
            'comentario'         => $request->input('comentario'),
            'fecha'              => Carbon::now(),
        ]);

        return redirect()
            ->route('animales.comentarios', $animal)
            ->with('exito', 'Comentario guardado.');
    }

    /** Eliminar comentario (solo el vet que lo escribió o admin) */
    public function destroy(Animal $animal, ComentarioVeterinario $comentario): RedirectResponse
    {
        $u = auth()->user();

        if (! $u->esAdmin() && $comentario->cedula_veterinario !== $u->cedula) {
            abort(403, 'Solo el veterinario autor o un admin puede eliminar este comentario.');
        }

        $comentario->delete();

        return redirect()
            ->route('animales.comentarios', $animal)
            ->with('exito', 'Comentario eliminado.');
    }

    private function autorizarVer(Animal $animal): void
    {
        $u = auth()->user();

        if ($u->esAdmin()) return;

        // Ganadero dueño de la finca
        if ($u->esGanadero() && $animal->finca->cedula === $u->cedula) return;

        // Veterinario asignado a la finca del animal
        if ($u->esVeterinario()
            && $animal->finca->veterinarios()->where('Veterinario_Finca.cedula', $u->cedula)->exists()) {
            return;
        }

        abort(403, 'No tenés acceso a los comentarios de este animal.');
    }
}
