<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\Auditoria;
use App\Models\Transaccion;
use App\Services\AuditoriaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransaccionController extends Controller
{
    public function index(Request $request): View
    {
        $usuario = $request->user();
        $aretesVisibles = Animal::visibleFor($usuario)->pluck('arete');

        $query = Transaccion::with(['animal.finca', 'usuario'])
            ->whereIn('arete', $aretesVisibles)
            ->orderByDesc('fecha');

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->input('tipo'));
        }
        if ($request->filled('animal')) {
            $query->where('arete', $request->input('animal'));
        }

        $transacciones = $query->paginate(20)->withQueryString();
        $animales = Animal::visibleFor($usuario)->orderBy('arete')->get(['arete', 'nombre', 'id_finca']);

        return view('transacciones.index', compact('transacciones', 'animales'));
    }

    public function create(Request $request): View
    {
        $usuario = $request->user();
        $animales = Animal::visibleFor($usuario)
            ->with(['finca', 'pesajes' => fn ($q) => $q->orderByDesc('fecha')])
            ->orderBy('arete')
            ->get();

        return view('transacciones.create', compact('animales'));
    }

    public function store(Request $request): RedirectResponse
    {
        $usuario = $request->user();

        $datos = $request->validate([
            'tipo'               => ['required', 'in:compra,venta'],
            'arete'              => ['required', 'string', 'exists:Animal,arete'],
            'nombre_contraparte' => ['required', 'string', 'max:150'],
            'cedula_contraparte' => ['nullable', 'string', 'max:30'],
            'precio_por_kg'      => ['required', 'numeric', 'min:1', 'max:99999'],
            'peso_negociado'     => ['required', 'numeric', 'min:1', 'max:3000'],
            'notas'              => ['nullable', 'string', 'max:1000'],
        ]);

        // Verificar que el animal sea visible para el usuario
        if (! Animal::visibleFor($usuario)->where('arete', $datos['arete'])->exists()) {
            abort(403, 'No tenés permiso sobre este animal.');
        }

        $datos['monto_total'] = round($datos['precio_por_kg'] * $datos['peso_negociado'], 2);
        $datos['fecha']        = Carbon::now();
        $datos['cedula_usuario'] = $usuario->cedula;

        $transaccion = Transaccion::create($datos);

        // RF21 — Auditoría.
        AuditoriaService::registrar(
            accion:       Auditoria::ACCION_CREAR,
            modulo:       Auditoria::MODULO_TRANSACCIONES,
            descripcion:  "Transacción de '{$datos['tipo']}' registrada para arete '{$datos['arete']}'. Contraparte: '{$datos['nombre_contraparte']}'. Monto: \${$transaccion->monto_total}.",
            datosDespues: $transaccion->toArray(),
        );

        return redirect()
            ->route('transacciones.index')
            ->with('exito', 'Transacción registrada correctamente.');
    }

    public function exportarPdf(Request $request)
    {
        $usuario = $request->user();
        $aretesVisibles = Animal::visibleFor($usuario)->pluck('arete');

        $transacciones = Transaccion::with(['animal.finca', 'usuario'])
            ->whereIn('arete', $aretesVisibles)
            ->when($request->filled('tipo'), fn ($q) => $q->where('tipo', $request->input('tipo')))
            ->orderByDesc('fecha')
            ->get();

        $pdf = Pdf::loadView('reports.transacciones_pdf', compact('transacciones', 'usuario'));
        return $pdf->download('transacciones_' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportarCsv(Request $request)
    {
        $usuario = $request->user();
        $aretesVisibles = Animal::visibleFor($usuario)->pluck('arete');

        $transacciones = Transaccion::with(['animal.finca'])
            ->whereIn('arete', $aretesVisibles)
            ->orderByDesc('fecha')
            ->get();

        $filename = 'transacciones_' . now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($transacciones) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, ['Fecha', 'Tipo', 'Arete', 'Animal', 'Finca', 'Contraparte', 'Cédula', 'Precio/kg', 'Peso (kg)', 'Monto Total', 'Notas']);
            foreach ($transacciones as $t) {
                fputcsv($out, [
                    Carbon::parse($t->fecha)->format('d/m/Y H:i'),
                    ucfirst($t->tipo),
                    $t->arete,
                    $t->animal->nombre ?? '—',
                    $t->animal->finca->nombre ?? '—',
                    $t->nombre_contraparte,
                    $t->cedula_contraparte ?? '—',
                    number_format($t->precio_por_kg, 2, '.', ''),
                    number_format($t->peso_negociado, 2, '.', ''),
                    number_format($t->monto_total, 2, '.', ''),
                    $t->notas ?? '',
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
