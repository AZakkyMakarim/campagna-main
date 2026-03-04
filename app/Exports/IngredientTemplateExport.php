<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IngredientTemplateExport implements FromCollection, WithHeadings, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Return a sample row
        return collect([
            [
                '1',
                'Air',
                'raw',
                'mililiter',
                10
            ],
            [
                '2',
                'Keju Cheddar',
                'semi',
                'gram',
                500
            ],
            [
                '3',
                'Kopi Susu',
                'finished',
                'pieces',
                0
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'No', // Just a placeholder, logic usually ignores this or uses it as ID/Index
            'Nama Bahan',
            'Tipe (raw/semi/finished)',
            'Satuan',
            'Stok Minimum'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],
        ];
    }
}
