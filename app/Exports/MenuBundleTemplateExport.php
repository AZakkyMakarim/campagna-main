<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MenuBundleTemplateExport implements FromCollection, WithHeadings, WithStyles
{
    public function collection()
    {
        return collect([
            ['Paket Sarapan', 'PKT-001', 'makanan', 25000, 'Nasi Goreng', 1],
            ['Paket Sarapan', 'PKT-001', 'makanan', 25000, 'Es Teh Manis', 1],
            ['Paket Makan Siang', 'PKT-002', 'makanan', 35000, 'Nasi Goreng', 1],
            ['Paket Makan Siang', 'PKT-002', 'makanan', 35000, 'Es Teh Manis', 2],
        ]);
    }

    public function headings(): array
    {
        return [
            'Nama Paket',
            'SKU',
            'Kategori',
            'Harga Jual',
            'Nama Menu',
            'Qty',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $sheet->getStyle('A1:F1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF6600');

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $note = 'Catatan: Satu paket bisa punya banyak baris (banyak menu). ' .
            'Isi SKU & Nama Paket yang sama di setiap baris. ' .
            'Nama Menu harus sudah ada di daftar Menu Single. ' .
            'Kategori: makanan / minuman.';

        $sheet->setCellValue('A7', $note);
        $sheet->mergeCells('A7:F7');
        $sheet->getStyle('A7')->getFont()->setItalic(true)->setColor(
            (new \PhpOffice\PhpSpreadsheet\Style\Color())->setARGB('FF666666')
        );
    }
}
