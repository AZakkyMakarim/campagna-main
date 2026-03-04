<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MenuSingleTemplateExport implements FromCollection, WithHeadings, WithStyles
{
    public function collection()
    {
        return collect([
            [1, 'Es Teh Manis', 'ETM-001', 'minuman', 8000, 'Teh Celup', 1],
            [1, 'Es Teh Manis', 'ETM-001', 'minuman', 8000, 'Gula', 15],
            [1, 'Es Teh Manis', 'ETM-001', 'minuman', 8000, 'Air', 200],
            [2, 'Nasi Goreng', 'NG-001', 'makanan', 15000, 'Beras', 150],
            [2, 'Nasi Goreng', 'NG-001', 'makanan', 15000, 'Telur', 1],
        ]);
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Menu',
            'SKU',
            'Kategori',
            'Harga Jual',
            'Nama Bahan',
            'Qty',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet->getStyle('A1:G1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF6600');

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $note = 'Catatan: Satu menu bisa punya banyak baris (banyak komponen). ' .
            'Isi SKU & Nama Menu yang sama di setiap baris komponen. ' .
            'Kategori: makanan / minuman.';

        $sheet->setCellValue('A8', $note);
        $sheet->mergeCells('A8:G8');
        $sheet->getStyle('A8')->getFont()->setItalic(true)->setColor(
            (new \PhpOffice\PhpSpreadsheet\Style\Color())->setARGB('FF666666')
        );
    }
}
