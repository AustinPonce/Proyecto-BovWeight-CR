<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * RF25 — Exportación de animales a Excel (.xlsx).
 */
class AnimalesExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithStyles
{
    public function __construct(
        protected $animales
    ) {}

    public function collection()
    {
        return $this->animales;
    }

    public function headings(): array
    {
        return [
            'Arete',
            'Nombre',
            'Finca',
            'Raza',
            'Sexo',
            'Estado',
            'Total Pesajes',
            'Último Peso (kg)',
        ];
    }

    public function map($animal): array
    {
        $ultimoPeso = $animal->pesajes->sortByDesc('fecha')->first();

        return [
            $animal->arete,
            $animal->nombre ?? '—',
            $animal->finca->nombre ?? '—',
            $animal->raza->raza ?? '—',
            $animal->sexo->sexo ?? '—',
            $animal->estado->estado ?? '—',
            $animal->pesajes->count(),
            $ultimoPeso ? number_format((float) $ultimoPeso->peso, 2, '.', '') : '—',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF065F46']],
            ],
        ];
    }
}
