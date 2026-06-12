<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use App\Models\Usuario;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * AuditoriaController — Panel de auditoría del sistema (RF21).
 *
 * Exclusivo para administradores (protegido por middleware 'rol:admin' en web.php).
 *
 * Funcionalidades:
 *   - Listado paginado con filtros: usuario, módulo, fechas, búsqueda textual
 *   - Exportación a PDF
 *   - Exportación a CSV
 */
class AuditoriaController extends Controller
{
    /**
     * Tabla paginada con todos los registros de auditoría.
     * Soporta filtros: cedula, modulo, desde, hasta, buscar.
     */
    public function index(Request $request): View
    {
        $query = Auditoria::with('usuario')
            ->deUsuario($request->input('cedula'))
            ->deModulo($request->input('modulo'))
            ->entreFechas($request->input('desde'), $request->input('hasta'))
            ->conBusqueda($request->input('buscar'))
            ->orderByDesc('created_at');

        $registros = $query->paginate(30)->withQueryString();

        // Datos para los filtros desplegables.
        $usuarios = Usuario::orderBy('nombre')->get(['cedula', 'nombre']);
        $modulos = [
            Auditoria::MODULO_AUTH => 'Autenticación',
            Auditoria::MODULO_FINCAS => 'Fincas',
            Auditoria::MODULO_ANIMALES => 'Animales',
            Auditoria::MODULO_PESAJES => 'Pesajes',
            Auditoria::MODULO_TRANSACCIONES => 'Transacciones',
            Auditoria::MODULO_VETERINARIOS => 'Veterinarios',
            Auditoria::MODULO_USUARIOS => 'Usuarios',
            Auditoria::MODULO_CATALOGOS => 'Catálogos',
        ];

        return view('admin.auditoria.index', compact('registros', 'usuarios', 'modulos'));
    }

    /**
     * Exporta los registros filtrados a PDF.
     */
    public function exportarPdf(Request $request)
    {
        $registros = $this->queryFiltrada($request)->get();

        $pdf = Pdf::loadView('admin.auditoria.pdf', compact('registros'))
            ->setPaper('A4', 'landscape');

        return $pdf->download('auditoria_'.now()->format('Y-m-d_His').'.pdf');
    }

    /**
     * Exporta los registros filtrados a CSV.
     */
    public function exportarCsv(Request $request)
    {
        $registros = $this->queryFiltrada($request)->with('usuario')->get();
        $filename = 'auditoria_'.now()->format('Y-m-d_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($registros) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8

            fputcsv($out, ['Fecha/Hora', 'Usuario', 'Cédula', 'Módulo', 'Acción', 'Descripción', 'IP']);

            foreach ($registros as $r) {
                fputcsv($out, [
                    Carbon::parse($r->created_at)->format('d/m/Y H:i:s'),
                    $r->usuario?->nombre ?? 'Sistema',
                    $r->cedula_usuario ?? '—',
                    $r->modulo,
                    $r->accion,
                    $r->descripcion,
                    $r->ip ?? '—',
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ------------------------------------------------------------------
    // Helper privado
    // ------------------------------------------------------------------

    /** Construye la query base con los filtros del request. */
    private function queryFiltrada(Request $request)
    {
        return Auditoria::deUsuario($request->input('cedula'))
            ->deModulo($request->input('modulo'))
            ->entreFechas($request->input('desde'), $request->input('hasta'))
            ->conBusqueda($request->input('buscar'))
            ->orderByDesc('created_at');
    }
}
