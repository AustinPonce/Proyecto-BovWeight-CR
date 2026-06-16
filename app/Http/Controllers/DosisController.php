<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\ComentarioVeterinario;
use App\Models\Medicamento;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DosisController extends Controller
{
    public function calcular(Request $request): View
    {
        $usuario = $request->user();

        $medicamentos = Medicamento::where('activo', true)->orderBy('nombre')->get();

        $animales = Animal::visibleFor($usuario)
            ->with(['finca', 'pesajes'])
            ->orderBy('arete')
            ->get();

        $resultado = null;

        if ($request->isMethod('post')) {
            $datos = $request->validate([
                'arete'           => ['required', 'string', 'exists:Animal,arete'],
                'id_medicamento'  => ['required', 'integer', 'exists:Medicamento,id_medicamento'],
                'peso_referencia' => ['nullable', 'numeric', 'min:10', 'max:3000'],
                'comentario'      => ['nullable', 'string', 'max:1000'],
            ]);

            $animal = Animal::with(['pesajes' => fn ($q) => $q->orderByDesc('fecha')])->find($datos['arete']);
            $medicamento = Medicamento::find($datos['id_medicamento']);

            // Usar peso de referencia manual o el último pesaje del animal
            $peso = filled($datos['peso_referencia'])
                ? (float) $datos['peso_referencia']
                : (float) optional($animal->pesajes->first())->peso;

            if (! $peso) {
                return view('dosis.calcular', compact('medicamentos', 'animales'))
                    ->withErrors(['peso_referencia' => 'Este animal no tiene pesajes. Ingresá el peso manualmente.']);
            }

            $dosisTotal = round($medicamento->dosis_por_kg * $peso, 4);

            $resultado = [
                'animal'      => $animal,
                'medicamento' => $medicamento,
                'peso'        => $peso,
                'dosis_total' => $dosisTotal,
                'formula'     => "{$medicamento->dosis_por_kg} {$medicamento->unidad}/kg × {$peso} kg = {$dosisTotal} {$medicamento->unidad}",
            ];

            // Si el vet deja un comentario, guardarlo
            if ($usuario->esVeterinario() && filled($datos['comentario'])) {
                ComentarioVeterinario::create([
                    'arete'              => $animal->arete,
                    'cedula_veterinario' => $usuario->cedula,
                    'comentario'         => "[Dosis {$medicamento->nombre}: {$dosisTotal} {$medicamento->unidad}]\n{$datos['comentario']}",
                    'fecha'              => Carbon::now(),
                ]);
            }
        }

        return view('dosis.calcular', compact('medicamentos', 'animales', 'resultado'));
    }
}
