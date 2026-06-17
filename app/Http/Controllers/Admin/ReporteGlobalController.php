<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\Finca;
use App\Models\Pesaje;
use App\Models\Usuario;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReporteGlobalController extends Controller
{
    public function index(Request $request): View
    {
        $desde = $request->filled('desde')
            ? Carbon::parse($request->input('desde'))->startOfDay()
            : Carbon::now()->subDays(30)->startOfDay();

        $hasta = $request->filled('hasta')
            ? Carbon::parse($request->input('hasta'))->endOfDay()
            : Carbon::now()->endOfDay();

        $stats = [
            'total_usuarios'   => Usuario::count(),
            'total_ganaderos'  => Usuario::where('id_tipo_usuario', Usuario::ROL_GANADERO)->count(),
            'total_vets'       => Usuario::where('id_tipo_usuario', Usuario::ROL_VETERINARIO)->count(),
            'total_fincas'     => Finca::count(),
            'total_animales'   => Animal::count(),
            'total_pesajes'    => Pesaje::count(),
            'pesajes_periodo'  => Pesaje::whereBetween('fecha', [$desde, $hasta])->count(),
            'peso_promedio'    => Pesaje::whereBetween('fecha', [$desde, $hasta])->avg('peso'),
            'peso_maximo'      => Pesaje::whereBetween('fecha', [$desde, $hasta])->max('peso'),
            'peso_minimo'      => Pesaje::whereBetween('fecha', [$desde, $hasta])->min('peso'),
        ];

        $pesajesPorDia = Pesaje::whereBetween('fecha', [$desde, $hasta])
            ->selectRaw('DATE(fecha) as dia, COUNT(*) as total, AVG(peso) as promedio')
            ->groupBy('dia')
            ->orderBy('dia')
            ->get();

        $topFincas = Finca::withCount(['animales', 'animales as pesajes_count' => function ($q) use ($desde, $hasta) {
            $q->join('Pesaje', 'Animal.arete', '=', 'Pesaje.arete')
              ->whereBetween('Pesaje.fecha', [$desde, $hasta]);
        }])
        ->orderByDesc('pesajes_count')
        ->limit(10)
        ->get();

        return view('admin.reportes.index', compact('stats', 'pesajesPorDia', 'topFincas', 'desde', 'hasta'));
    }

    public function exportarPdf(Request $request)
    {
        $desde = $request->filled('desde')
            ? Carbon::parse($request->input('desde'))->startOfDay()
            : Carbon::now()->subDays(30)->startOfDay();

        $hasta = $request->filled('hasta')
            ? Carbon::parse($request->input('hasta'))->endOfDay()
            : Carbon::now()->endOfDay();

        $stats = [
            'total_usuarios'  => Usuario::count(),
            'total_fincas'    => Finca::count(),
            'total_animales'  => Animal::count(),
            'total_pesajes'   => Pesaje::count(),
            'pesajes_periodo' => Pesaje::whereBetween('fecha', [$desde, $hasta])->count(),
            'peso_promedio'   => Pesaje::whereBetween('fecha', [$desde, $hasta])->avg('peso'),
        ];

        $pesajesPorDia = Pesaje::whereBetween('fecha', [$desde, $hasta])
            ->selectRaw('DATE(fecha) as dia, COUNT(*) as total, AVG(peso) as promedio')
            ->groupBy('dia')
            ->orderBy('dia')
            ->get();

        $pdf = Pdf::loadView('reports.reporte_global_pdf', compact('stats', 'pesajesPorDia', 'desde', 'hasta'));
        return $pdf->download('reporte_global_' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportarCsv(Request $request)
    {
        $desde = $request->filled('desde')
            ? Carbon::parse($request->input('desde'))->startOfDay()
            : Carbon::now()->subDays(30)->startOfDay();

        $hasta = $request->filled('hasta')
            ? Carbon::parse($request->input('hasta'))->endOfDay()
            : Carbon::now()->endOfDay();

        $pesajes = Pesaje::with(['animal.finca'])
            ->whereBetween('fecha', [$desde, $hasta])
            ->orderByDesc('fecha')
            ->get();

        $filename = 'reporte_global_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($pesajes) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
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
}
