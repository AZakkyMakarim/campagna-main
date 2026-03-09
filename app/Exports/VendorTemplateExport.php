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
            [1, 'Toko Sejahtera', '08123456789', 'Jl. Merdeka No. 1, Jakarta', 'https://maps.google.com/?q=...', 'BCA', '1234567890', 'Budi Santoso'],
            [2, 'CV Maju Bersama', '0217654321', 'Jl. Pahlawan No. 5, Bandung', '', 'MANDIRI', '0987654321', 'Ahmad Rizal'],
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
            'Nama Bank',
            'Nomor Rekening',
            'Nama Penerima',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->getStyle('A1:H1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFF6600');

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $note = 'Catatan: ' .
            'Link Maps dan info bank boleh dikosongkan.';

        $sheet->setCellValue('A5', $note);
        $sheet->mergeCells('A5:H5');
        $sheet->getStyle('A5')->getFont()->setItalic(true)->setColor(
            (new \PhpOffice\PhpSpreadsheet\Style\Color())->setARGB('FF666666')
        );
    }
}
