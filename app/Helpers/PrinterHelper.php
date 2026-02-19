<?php

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;

if (!function_exists('print_receipt')) {
    function print_receipt(array $printers, $data = null): array
    {
        foreach ($printers as $id){
            $printer = Printer::find($id);

            $connector = new NetworkPrintConnector($printer->ip_address, $printer->port);

            $escpos = new Printer($connector);

            $escpos->setJustification(Printer::JUSTIFY_CENTER);
            $escpos->setEmphasis(true);
            $escpos->text(($data['header'] ?? 'TOKO') . "\n");
            $escpos->setEmphasis(false);
            $escpos->text("--------------------------------\n");

            $escpos->setJustification(Printer::JUSTIFY_LEFT);
            foreach ($data['items'] ?? [] as $item) {
                $line = sprintf(
                    "%-20s %5s\n",
                    $item['name'],
                    number_format($item['price'])
                );
                $escpos->text($line);
            }

            $escpos->text("--------------------------------\n");
            $escpos->setEmphasis(true);
            $escpos->text("TOTAL : " . number_format($data['total'] ?? 0) . "\n");
            $escpos->setEmphasis(false);

            /* ================= FOOTER ================= */
            $escpos->feed(1);
            $escpos->setJustification(Printer::JUSTIFY_CENTER);
            $escpos->text(($data['footer'] ?? 'Terima kasih') . "\n");

            $escpos->cut();
            $escpos->close();
        }
        return [
            'success' => true,
            'message' => 'Struk berhasil dicetak'
        ];
    }
}
