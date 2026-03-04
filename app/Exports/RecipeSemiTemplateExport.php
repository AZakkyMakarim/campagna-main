<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RecipeSemiTemplateExport implements FromCollection, WithHeadings, WithStyles
{
    /**
     * Sample rows for the import template.
     */
    public function collection()
    {
        return collect([
            [
                '1',
                'Saus Tomat',
                1000,
                'ml',
                'Tomat Segar',
                200,
                'gram',
            ],
            [
                '1',
                'Saus Tomat',
                1000,
                'ml',
                'Bawang Putih',
                50,
                'gram',
            ],
            [
                '2',
                'Stock Ayam',
                500,
                'ml',
                'Tulang Ayam',
                300,
                'gram',
            ],
            [
                '2',
                'Stock Ayam',
                500,
                'ml',
                'Air',
                500,
                'ml',
            ],
        ]);
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Semi',
            'Qty Hasil',
            'Satuan Hasil',
            'Bahan Komponen',
            'Qty Komponen',
            'Satuan Komponen',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
