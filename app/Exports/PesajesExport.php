<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * RF25 — Exportación de pesajes a Excel (.xlsx).
 *
 * Implementa:
 *   FromCollection  → provee los datos desde una colección Laravel.
 *   WithHeadings    → fila de encabezados en negrita (fila 1).
 *   WithMapping     → mapea cada Pesaje a un array de celdas.
 *   ShouldAutoSize  → ajusta ancho de columnas automáticamente.
 *   WithStyles      → aplica estilos (negrita en encabezados, color verde BovWeight).
 */
class PesajesExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        protected $pesajes
    ) {}

    public function collection()
    {
        return $this->pesajes;
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Arete',
            'Nombre Animal',
            'Finca',
            'Raza',
            'Peso (kg)',
            'Tipo',
        ];
    }

    public function map($pesaje): array
    {
        return [
            Carbon::parse($pesaje->fecha)->format('d/m/Y H:i'),
            $pesaje->arete,
            $pesaje->animal->nombre ?? '—',
            $pesaje->animal->finca->nombre ?? '—',
            $pesaje->animal->raza->raza ?? '—',
            number_format((float) $pesaje->peso, 2, '.', ''),
            $pesaje->id_tipo_pesaje === 1 ? 'Fotografía IA' : 'Manual',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Fila 1 = encabezados: negrita + fondo verde BovWeight
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF065F46']],
            ],
        ];
    }
}
