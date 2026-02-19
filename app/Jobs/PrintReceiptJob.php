<?php

namespace App\Jobs;

use App\Models\Printer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;

class PrintReceiptJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public array $printers;
    public array $data;

    public function __construct(array $data, array $printers)
    {
        $this->printers = $printers;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        foreach ($this->printers as $id) {
            $printer = Printer::find($id);
            $connector = new NetworkPrintConnector($printer->ip_address, $printer->port);
            $escpos = new \Mike42\Escpos\Printer($connector);

            try {
                $line = str_repeat('-', 32) . "\n"; // 58mm = 32 char

                // =====================
                // INIT
                // =====================
                $escpos->initialize();

                // =====================
                // HEADER (CENTER)
                // =====================
                $escpos->setJustification(\Mike42\Escpos\Printer::JUSTIFY_CENTER);
                $escpos->setEmphasis(true);
                $escpos->text(($this->data['outlet']['name'] ?? 'TOKO') . "\n");
                $escpos->setEmphasis(false);

                if (!empty($this->data['outlet']['address'])) {
                    $escpos->text($this->data['outlet']['address'] . "\n");
                }

                $escpos->feed(1);

                // =====================
                // META ORDER (LEFT)
                // =====================
                $escpos->setJustification(\Mike42\Escpos\Printer::JUSTIFY_LEFT);

                $order   = $this->data['order'] ?? [];
                $cashier = $this->data['cashier'] ?? '-';

                $escpos->text("Order  : " . ($order['code'] ?? '-') . "\n");
                $escpos->text("Kasir  : " . $cashier . "\n");
                $escpos->text("Tanggal: " . ($order['date'] ?? '-') . "\n");

                if (!empty($order['table'])) {
                    $escpos->text("Meja   : " . $order['table'] . "\n");
                }

                $escpos->text($line);

                // =====================
                // LIST ITEM
                // =====================
                foreach ($this->data['items'] ?? [] as $item) {
                    // Baris 1: Nama
                    $escpos->text($item['name'] . "\n");

                    // Baris 2: "  qty @ harga" (kiri) + "subtotal" (kanan)
                    $left  = "  " . $item['qty'] . " @ " . number_format($item['price']);
                    $right = number_format($item['subtotal']);

                    // padding biar kanan rata (32 char)
                    $space = max(1, 32 - strlen($left) - strlen($right));
                    $escpos->text($left . str_repeat(' ', $space) . $right . "\n");
                }

                $escpos->text($line);

                // =====================
                // TOTAL
                // =====================
                $subTotal        = $order['sub_total'] ?? 0;
                $adjustmentTotal = $order['adjustment_total'] ?? 0;
                $grandTotal      = $order['grand_total'] ?? 0;

                // helper buat pad kiri-kanan
                $padLine = function ($label, $value) {
                    $left = $label;
                    $right = number_format($value);
                    $space = max(1, 32 - strlen($left) - strlen($right));
                    return $left . str_repeat(' ', $space) . $right . "\n";
                };

                $escpos->text($padLine('Subtotal', $subTotal));

                if ($adjustmentTotal != 0) {
                    $escpos->text($padLine('Penyesuaian', $adjustmentTotal));
                }

                $escpos->setEmphasis(true);
                $escpos->text($padLine('TOTAL', $grandTotal));
                $escpos->setEmphasis(false);

                $escpos->text($line);

                // =====================
                // PEMBAYARAN
                // =====================
                $payment = $this->data['payment'] ?? [];

                $escpos->text("Pembayaran: " . ($payment['method'] ?? '-') . "\n");
                $escpos->text($padLine('Dibayar', $payment['paid'] ?? 0));
                $escpos->text($padLine('Kembali', $payment['change'] ?? 0));

                $escpos->feed(1);

                // =====================
                // FOOTER (CENTER)
                // =====================
                $escpos->setJustification(\Mike42\Escpos\Printer::JUSTIFY_CENTER);
                $escpos->text("Terima kasih \n");
                $escpos->text("Powered by Campagna POS\n");

                // =====================
                // CUT
                // =====================
                $escpos->feed(2);
                $escpos->cut();
                $escpos->close();

            } catch (\Exception $e) {
                Log::error("Failed to print receipt for printer ID {$printer->id}: " . $e->getMessage());

                return [
                    'success' => false,
                    'message' => 'Gagal mencetak struk, coba lagi!'
                ];
            }
        }

        return [
            'success' => true,
            'message' => 'Struk berhasil dicetak'
        ];
    }
}
