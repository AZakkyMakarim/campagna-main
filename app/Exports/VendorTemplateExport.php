<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VendorTemplateExport implements FromCollection, WithHeadings, WithStyles
{
    public function collection()
    {
        return collect([
            [1, 'Toko Sejahtera', '08123456789', 'Jl. Merdeka No. 1, Jakarta', 'https://maps.google.com/?q=...', 'Gula'],
            [1, 'Toko Sejahtera', '08123456789', 'Jl. Merdeka No. 1, Jakarta', 'https://maps.google.com/?q=...', 'Tepung Terigu'],
            [1, 'Toko Sejahtera', '08123456789', 'Jl. Merdeka No. 1, Jakarta', 'https://maps.google.com/?q=...', 'Garam'],
            [2, 'CV Maju Bersama', '0217654321', 'Jl. Pahlawan No. 5, Bandung', '', 'Biji Kopi'],
            [2, 'CV Maju Bersama', '0217654321', 'Jl. Pahlawan No. 5, Bandung', '', 'Susu'],
        ]);
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Vendor',
            'No Telp',
            'Alamat',
            'Link Maps',
            'Nama Bahan',
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

        $note = 'Catatan: Satu vendor bisa punya banyak baris (banyak bahan). ' .
            'Isi Nama Vendor & No Telp yang sama di setiap baris. ' .
            'Nama Bahan harus sudah ada di sistem. ' .
            'Link Maps boleh dikosongkan.';

        $sheet->setCellValue('A8', $note);
        $sheet->mergeCells('A8:F8');
        $sheet->getStyle('A8')->getFont()->setItalic(true)->setColor(
            (new \PhpOffice\PhpSpreadsheet\Style\Color())->setARGB('FF666666')
        );
    }
}
