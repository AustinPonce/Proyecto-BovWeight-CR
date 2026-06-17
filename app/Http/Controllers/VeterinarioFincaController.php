<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Finca;
use App\Models\Usuario;
use App\Services\AuditoriaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VeterinarioFincaController extends Controller
{
    /** Buscar veterinarios por nombre o cédula (AJAX) */
    public function buscar(Request $request): JsonResponse
    {
        $term = $request->input('q', '');

        $vets = Usuario::where('id_tipo_usuario', Usuario::ROL_VETERINARIO)
            ->where(function ($q) use ($term) {
                $q->where('nombre', 'like', "%{$term}%")
                  ->orWhere('cedula', 'like', "%{$term}%");
            })
            ->limit(10)
            ->get(['cedula', 'nombre', 'correo']);

        return response()->json($vets);
    }

    /** Asignar veterinario a una finca */
    public function store(Request $request, Finca $finca): RedirectResponse
    {
        $this->autorizarEdicion($finca);

        $request->validate([
            'cedula_veterinario' => ['required', 'string', 'exists:Usuario,cedula'],
        ]);

        $vet = Usuario::where('cedula', $request->input('cedula_veterinario'))
            ->where('id_tipo_usuario', Usuario::ROL_VETERINARIO)
            ->firstOrFail();

        // Evitar duplicados
        if (! $finca->veterinarios()->where('Veterinario_Finca.cedula', $vet->cedula)->exists()) {
            $finca->veterinarios()->attach($vet->cedula);
        }

        // RF21 — Auditoría.
        AuditoriaService::registrar(
            accion:      Auditoria::ACCION_ASIGNAR,
            modulo:      Auditoria::MODULO_VETERINARIOS,
            descripcion: "Veterinario '{$vet->nombre}' (cédula: {$vet->cedula}) asignado a finca '{$finca->nombre}'.",
            datosDespues: ['finca_id' => $finca->id_finca, 'cedula_veterinario' => $vet->cedula],
        );

        return redirect()
            ->route('fincas.show', $finca)
            ->with('exito', "Veterinario {$vet->nombre} asignado correctamente.");
    }

    /** Desasignar veterinario de una finca */
    public function destroy(Request $request, Finca $finca, string $cedula): RedirectResponse
    {
        $this->autorizarEdicion($finca);

        $finca->veterinarios()->detach($cedula);

        // RF21 — Auditoría.
        AuditoriaService::registrar(
            accion:      Auditoria::ACCION_DESASIGNAR,
            modulo:      Auditoria::MODULO_VETERINARIOS,
            descripcion: "Veterinario cédula '{$cedula}' desasignado de finca '{$finca->nombre}'.",
            datosAntes:  ['finca_id' => $finca->id_finca, 'cedula_veterinario' => $cedula],
        );

        return redirect()
            ->route('fincas.show', $finca)
            ->with('exito', 'Veterinario desasignado.');
    }

    private function autorizarEdicion(Finca $finca): void
    {
        $u = auth()->user();
        if ($u->esAdmin()) return;
        if ($u->esGanadero() && $finca->cedula === $u->cedula) return;
        abort(403, 'No tenés permiso para gestionar veterinarios de esta finca.');
    }
}
