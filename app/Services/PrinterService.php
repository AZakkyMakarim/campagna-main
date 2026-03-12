<?php

namespace App\Services;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class PrinterService extends Controller
{
    public function print(array $data){
        switch ($data['role']){
            case 'cashier':
                $receipt = $this->build_cashier_receipt($data['order']);
                break;
            case 'kitchen':
                $receipt = $this->build_kitchen_receipt($data['order'], $data['items']);
                break;
            case 'test':
                $receipt = $this->build_dummy_receipt();
                break;
        }

        $res = Http::post('http://127.0.0.1:3333/print', [
            'type'          => strtoupper($data['printer_connection_type']),
            'printer_ip'    => $data['printer_ip'],
            'printer_port'  => $data['printer_port'],
            'receipt'       => base64_encode($receipt)
        ]);

        if (!$res->ok() || !$res->json('success')) {
            throw new \Exception('Gagal print');
        }
    }

    public function checkConnection(): bool
    {
        try {
            $res = Http::timeout(3)->get('http://127.0.0.1:3333/');

            return $res->ok();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function list(){
        $res = Http::get('http://127.0.0.1:3333/printers');

//        if (!$res->ok() || !$res->json('success')) {
//            throw new \Exception('Gagal print');
//        }

        return $res->json();
    }

    function build_cashier_receipt(Order $order): string
    {
        // pastikan relasi keload
        $order->loadMissing(['items', 'payments', 'adjustments', 'outlet', 'cashier']);

        $outletName    = $order->outlet->name ?? 'TOKO';
        $outletAddress = $order->outlet->address ?? null;
        $outletPhoneNumber = $order->outlet->phone_number ?? null;
        $cashierName   = $order->cashier->name ?? '-';

        $paidAmount = $order->paid_amount ?? $order->payments->sum('amount');
        $change     = max(0, $paidAmount - $order->grand_total);

        $line = "--------------------------------\n";

        $text = '';

        // INIT
        $text .= "\x1B\x40";      // ESC @ (init)
        $text .= "\x1B\x61\x01";  // CENTER

        // HEADER TOKO
        $text .= $this->center_text(strtoupper($outletName));
        if (!empty($outletAddress)) {
            $text .= $this->center_text($outletAddress);
        }
        if (!empty($outletPhoneNumber)) {
            $text .= $this->center_text($outletPhoneNumber) . "\n";
        }
        $text .= "\n";

        // META ORDER
        $text .= "\x1B\x61\x00"; // LEFT
        $text .= "Order       : " . ($order->code ?? '-') . "\n";
        $text .= "Tanggal     : " . $order->created_at->format('d/m/Y H:i') . "\n";
        $text .= "Kasir       : " . $cashierName . "\n";
        if (!empty($order->table_number)) {
            $text .= "Nomor Meja  : " . $order->table_number . "\n";
        }
        $text .= $line;

        // =====================
        // LIST ITEM
        // =====================
        foreach ($order->items as $item) {
            $name     = $item->name_snapshot;
            $qty      = (int) $item->qty;
            $price    = (float) $item->price;
            $subtotal = (float) $item->subtotal;

            // Nama menu
            $text .= $this->sanitize_print_text($name) . "\n";

            // Qty x Harga (kiri) + Subtotal (kanan)
            $left  = "  {$qty} @ " . rp_format($price);
            $right = rp_format($subtotal);

            // padding biar kanan rata (32 char printer 58mm)
            $space = max(1, 32 - strlen($left) - strlen($right));
            $text .= $left . str_repeat(' ', $space) . $right . "\n";

            // Note per item
            if (!empty($item->note)) {
                $text .= "  - " . $this->sanitize_print_text($item->note) . "\n";
            }
        }

        $text .= $line;

        // =====================
        // TOTAL
        // =====================
        $text .= $this->pad_line('Subtotal', rp_format($order->sub_total));

        // Adjustment (tax, rounding, dll)
        foreach ($order->adjustments as $adj) {
            $label = $adj->name;
            $amount = ($adj->is_addition ? '' : '-') . rp_format($adj->amount);
            $text .= $this->pad_line($label, $amount);
        }

        $text .= $this->pad_line('TOTAL', rp_format($order->grand_total), true);
        $text .= $line;

        // =====================
        // PEMBAYARAN
        // =====================
        $lastPayment = $order->payments->last();

        $text .= "Pembayaran: " . ($lastPayment->method ?? '-') . "\n";
        $text .= $this->pad_line('Dibayar', rp_format($paidAmount));
        $text .= $this->pad_line('Kembali', rp_format($change));
        $text .= "\n";

        // FOOTER
        $text .= "\x1B\x61\x01"; // CENTER
        $text .= $this->center_text("Terima kasih");
        $text .= $this->center_text("Powered by Campagna POS")."\n\n";
        $text .= $this->center_text("SSID Wifi : CAMPAGNA");
        $text .= $this->center_text("Password Wifi : indonesia")."\n\n";

        // CUT + OPEN DRAWER
        $text .= "\x1D\x56\x00";          // GS V 0 (cut)
        $text .= "\x1B\x70\x00\x19\xFA";  // ESC p 0 25 250 (open cash drawer)

        return $text;
    }

    function build_kitchen_receipt(Order $order, $items): string
    {
        $line = "--------------------------------\n";

        $text = '';

        // RESET PRINTER (WAJIB di Epson)
        $text .= "\x1B\x40";        // ESC @
        $text .= "\x1B\x33\x18";    // ESC 3 24 -> line spacing normal

        // CENTER
        $text .= "\x1B\x61\x01";

        // TITLE (big)
        $text .= "\x1D\x21\x11";    // double w+h
        $text .= "KITCHEN ORDER\n";
        $text .= "\x1D\x21\x00";    // normal

        $text .= $line;

        // META (left)
        $text .= "\x1B\x61\x00";
//        $text .= "ANTRIAN : " . ($order->queue_number ?? '-') . "\n";
        $text .= "ORDER   : " . ($order->code ?? '-') . "\n";
        if (!empty($order->table_number)) {
            $text .= "MEJA    : " . $order->table_number . "\n";
        }
        $text .= "WAKTU   : " . Carbon::parse($order->created_at)->format('d/m/Y H:i') . "\n";
        $text .= $line . "\n";

        // ITEMS
        foreach ($items as $item) {
            $qty = rtrim(rtrim(number_format((float) ($item->qty ?? 0), 2, '.', ''), '0'), '.');
            $name = strtoupper((string) ($item->name_snapshot ?? $item->menu?->name ?? '-'));

            // big line
            $text .= "\x1D\x21\x11";
            $text .= "[ ] {$qty}x {$name}\n";
            $text .= "\x1D\x21\x00";

            if (!empty($item->note)) {
                $text .= "  - " . $this->sanitize_print_text($item->note) . "\n";
            }

            $text .= "\n";
        }

        // ORDER NOTE
        if (!empty($order->note)) {
            $text .= $line;
            $text .= "CATATAN:\n";
            $text .= $this->sanitize_print_text($order->note) . "\n";
        }

        // FEED SEDIKIT + CUT (JANGAN KEBANYAKAN)
        $text .= "\n\n\n\n\n\n\n\n\n\n";
//        $text .= "\x1D\x56\x01"; // GS V 1 = partial cut (paling rapi di Epson)

        return $text;
    }

    function build_dummy_receipt(): string
    {
        $outletName    = 'TOKO TEST';
        $outletAddress = 'ALAMAT TESTER';
        $cashierName   = 'KASIR TEST';

        $paidAmount = 1200000;
        $change     = 0;

        $line = "--------------------------------\n";

        $text = '';

        // INIT
        $text .= "\x1B\x40";      // ESC @ (init)
        $text .= "\x1B\x61\x01";  // CENTER

        // HEADER TOKO
        $text .= strtoupper($outletName) . "\n";
        if (!empty($outletAddress)) {
            $text .= $outletAddress . "\n";
        }
        $text .= "\n";

        // META ORDER
        $text .= "\x1B\x61\x00"; // LEFT
        $text .= "Order  : ORD-TEST \n";
        $text .= "Kasir  : " . $cashierName . "\n";
        $text .= "Tanggal: " . now()->format('d/m/Y H:i') . "\n";
        $text .= "Meja   : TST 001 \n";

        $text .= $line;

        // =====================
        // LIST ITEM
        // =====================
        // Nama menu
        $text .= " Ayam Tester \n";

        // Qty x Harga (kiri) + Subtotal (kanan)
        $left  = "  1 @ " . rp_format(10000);
        $right = rp_format(100000);

        // padding biar kanan rata (32 char printer 58mm)
        $space = max(1, 32 - strlen($left) - strlen($right));
        $text .= $left . str_repeat(' ', $space) . $right . "\n";

        // Note per item
        if (!empty($item->note)) {
            $text .= "  - " . $this->sanitize_print_text('Ayam Production') . "\n";
        }

        $text .= $line;

        // =====================
        // TOTAL
        // =====================
        $text .= $this->pad_line('Subtotal', rp_format(10000));

        // Adjustment (tax, rounding, dll)
        $label = 'PPN';
        $amount = rp_format(2000);
        $text .= $this->pad_line($label, $amount);

        $text .= $this->pad_line('TOTAL', rp_format(12000), true);
        $text .= $line;

        // =====================
        // PEMBAYARAN
        // =====================
        $lastPayment = 12000;

        $text .= "Pembayaran: CASH \n";
        $text .= $this->pad_line('Dibayar', rp_format($paidAmount));
        $text .= $this->pad_line('Kembali', rp_format($change));
        $text .= "\n";

        // FOOTER
        $text .= "\x1B\x61\x01"; // CENTER
        $text .= "Terima kasih\n";
        $text .= "Powered by Campagna POS\n\n";

        // CUT + OPEN DRAWER
        $text .= "\x1D\x56\x00";          // GS V 0 (cut)
        $text .= "\x1B\x70\x00\x19\xFA";  // ESC p 0 25 250 (open cash drawer)

        return $text;
    }

    /**
     * Biar aman: buang karakter aneh (emoji dll) supaya gak bikin encoding error di JSON/print.
     */
    protected function sanitize_print_text(string $text): string
    {
        // buang emoji / non-ascii (printer dapur umumnya gak kuat unicode)
        $text = preg_replace('/[^\x09\x0A\x0D\x20-\x7E]/', '', $text);
        return trim($text);
    }

    function pad_line(string $left, string $right, bool $bold = false): string
    {
        $lineWidth = 32;

        if ($bold) {
            $left  = strtoupper($left);
            $right = strtoupper($right);
        }

        $space = max(1, $lineWidth - strlen($left) - strlen($right));
        return $left . str_repeat(' ', $space) . $right . "\n";
    }

    private function center_text(string $text, int $width = 32): string
    {
        $text = trim($text);
        $padding = max(0, floor(($width - strlen($text)) / 2));
        return str_repeat(' ', $padding) . $text . "\n";
    }
}
