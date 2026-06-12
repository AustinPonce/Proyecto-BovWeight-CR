<?php

namespace App\Http\Controllers;

use App\Exports\AnimalesExport;
use App\Exports\PesajesExport;
use App\Models\Animal;
use App\Models\Pesaje;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    // ===========================
    // PESAJES
    // ===========================

    public function pesajesPdf(Request $request)
    {
        $usuario = $request->user();
        $aretesVisibles = Animal::visibleFor($usuario)->pluck('arete');

        $query = Pesaje::with(['animal.finca', 'animal.raza'])
            ->whereIn('arete', $aretesVisibles)
            ->orderByDesc('fecha');

        if ($request->filled('animal')) {
            $query->where('arete', $request->input('animal'));
        }
        if ($request->filled('finca')) {
            $aretes = Animal::where('id_finca', $request->integer('finca'))->pluck('arete');
            $query->whereIn('arete', $aretes);
        }

        $pesajes = $query->get();

        $pdf = Pdf::loadView('reports.pesajes_pdf', compact('pesajes', 'usuario'))
            ->setPaper('A4', 'landscape');

        return $pdf->download('historial_pesajes_'.now()->format('Y-m-d').'.pdf');
    }

    public function pesajesCsv(Request $request)
    {
        $usuario = $request->user();
        $aretesVisibles = Animal::visibleFor($usuario)->pluck('arete');

        $query = Pesaje::with(['animal.finca'])
            ->whereIn('arete', $aretesVisibles)
            ->orderByDesc('fecha');

        if ($request->filled('animal')) {
            $query->where('arete', $request->input('animal'));
        }
        if ($request->filled('finca')) {
            $aretes = Animal::where('id_finca', $request->integer('finca'))->pluck('arete');
            $query->whereIn('arete', $aretes);
        }

        $pesajes = $query->get();
        $filename = 'historial_pesajes_'.now()->format('Y-m-d').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($pesajes) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['Fecha', 'Arete', 'Animal', 'Finca', 'Peso (kg)', 'Tipo']);
            foreach ($pesajes as $p) {
                fputcsv($out, [
                    Carbon::parse($p->fecha)->format('d/m/Y H:i'),
                    $p->arete,
                    $p->animal->nombre ?? '—',
                    $p->animal->finca->nombre ?? '—',
                    number_format($p->peso, 2, '.', ''),
                    $p->id_tipo_pesaje === 1 ? 'Fotografía' : 'Manual',
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    /** RF25 — Export pesajes a Excel .xlsx */
    public function pesajesXlsx(Request $request)
    {
        $usuario = $request->user();
        $aretesVisibles = Animal::visibleFor($usuario)->pluck('arete');

        $query = Pesaje::with(['animal.finca', 'animal.raza'])
            ->whereIn('arete', $aretesVisibles)
            ->orderByDesc('fecha');

        if ($request->filled('animal')) {
            $query->where('arete', $request->input('animal'));
        }
        if ($request->filled('finca')) {
            $aretes = Animal::where('id_finca', $request->integer('finca'))->pluck('arete');
            $query->whereIn('arete', $aretes);
        }

        $pesajes = $query->get();
        $filename = 'historial_pesajes_'.now()->format('Y-m-d').'.xlsx';

        return Excel::download(new PesajesExport($pesajes), $filename);
    }

    // ===========================
    // ANIMALES
    // ===========================

    public function animalesPdf(Request $request)
    {
        $usuario = $request->user();

        $query = Animal::visibleFor($usuario)
            ->with(['finca', 'raza', 'sexo', 'estado', 'pesajes'])
            ->orderBy('arete');

        if ($request->filled('finca')) {
            $query->where('id_finca', $request->integer('finca'));
        }

        $animales = $query->get();

        $pdf = Pdf::loadView('reports.animales_pdf', compact('animales', 'usuario'))
            ->setPaper('A4', 'landscape');

        return $pdf->download('mis_animales_'.now()->format('Y-m-d').'.pdf');
    }

    public function animalesCsv(Request $request)
    {
        $usuario = $request->user();

        $query = Animal::visibleFor($usuario)
            ->with(['finca', 'raza', 'sexo', 'estado', 'pesajes'])
            ->orderBy('arete');

        if ($request->filled('finca')) {
            $query->where('id_finca', $request->integer('finca'));
        }

        $animales = $query->get();
        $filename = 'mis_animales_'.now()->format('Y-m-d').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($animales) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['Arete', 'Nombre', 'Finca', 'Raza', 'Sexo', 'Estado', 'Total Pesajes', 'Último Peso (kg)']);
            foreach ($animales as $a) {
                $ultimoPeso = $a->pesajes->sortByDesc('fecha')->first();
                fputcsv($out, [
                    $a->arete,
                    $a->nombre ?? '—',
                    $a->finca->nombre ?? '—',
                    $a->raza->raza ?? '—',
                    $a->sexo->sexo ?? '—',
                    $a->estado->estado ?? '—',
                    $a->pesajes->count(),
                    $ultimoPeso ? number_format($ultimoPeso->peso, 2, '.', '') : '—',
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    /** RF25 — Export animales a Excel .xlsx */
    public function animalesXlsx(Request $request)
    {
        $usuario = $request->user();

        $query = Animal::visibleFor($usuario)
            ->with(['finca', 'raza', 'sexo', 'estado', 'pesajes'])
            ->orderBy('arete');

        if ($request->filled('finca')) {
            $query->where('id_finca', $request->integer('finca'));
        }

        $animales = $query->get();
        $filename = 'mis_animales_'.now()->format('Y-m-d').'.xlsx';

        return Excel::download(new AnimalesExport($animales), $filename);
    }
}
