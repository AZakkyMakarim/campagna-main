<?php

namespace App\Services;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ExportService implements FromView, ShouldAutoSize, WithEvents, WithColumnFormatting
{
    protected $data;
    protected $view;
    protected $optional;

    public function __construct($data, $view, $optional = null)
    {
        $this->data     = $data;
        $this->view     = $view;
        $this->optional = $optional;
    }

    public function view(): View
    {
        $view      = $this->view;
        $optional  = $this->optional;
        $data      = $this->data;

        return view($view, compact( 'data', 'optional'));
    }

    public function columnFormats(): array
    {
        return [
            'A' => '#',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet       = $event->sheet->getDelegate();
                $highestCol  = $sheet->getHighestColumn();
                $highestRow  = $sheet->getHighestRow();

                $headerRows = @$this->optional['thead_rows'] ?? 0;

                // Styling Header
                $headerRange = "A1:{$highestCol}{$headerRows}";
                $sheet->getStyle($headerRange)->applyFromArray([
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFC2410C'],
                    ],
                    'font' => [
                        'color' => ['argb' => 'FFFFFFFF'], // putih
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['argb' => 'FF5C6FA3'],
                        ],
                    ],
                ]);

                $sheet->freezePane("A" . ($headerRows + 3));

                // Styling Body
                if ($highestRow > $headerRows) {
                    $bodyStart = $headerRows + 1;
                    $bodyRange = "A{$bodyStart}:{$highestCol}{$highestRow}";
                    $sheet->getStyle($bodyRange)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color'       => ['argb' => 'FF000000'],
                            ],
                        ],
                    ]);
                }
            },
        ];
    }
}
