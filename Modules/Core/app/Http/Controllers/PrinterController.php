<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\PrintReceiptJob;
use App\Models\Printer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;

class PrinterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('core::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('core::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        Printer::create([
            'outlet_id'         => $request->outlet_id,
            'role'              => $request->role,
            'section'           => json_encode($request->section),
            'device_name'       => $request->device_name,
            'connection_type'   => $request->connection_type,
            'ip_address'        => $request->ip_address,
            'port'              => $request->port,
        ]);

        toast('Printer berhasil ditambahkan!');
        return back();
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('core::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('core::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Printer $printer, Request $request) {
        $printer->update($request->all());

        if ($request->expectsJson()) {
            return api_status_ok($printer);
        }

        toast('Printer berhasil diubah!');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}

    public function printTest(Printer $printer){
        $data = [
            'header' => 'TOKO '.Auth::user()->businessProfile->name,
            'items' => [
                ['name' => 'Test Item 1', 'price' => 1000],
                ['name' => 'Test Item 2', 'price' => 1500]
            ],
            'total' => 2500,
            'footer' => 'Terima kasih atas kunjungan anda.'
        ];

        $connector = new NetworkPrintConnector($printer->ip_address, $printer->port);
        $escpos = new \Mike42\Escpos\Printer($connector);

        try {
            $escpos->setJustification(\Mike42\Escpos\Printer::JUSTIFY_CENTER);
            $escpos->setEmphasis(true);
            $escpos->text(($data['header'] ?? 'TOKO') . "\n");
            $escpos->setEmphasis(false);
            $escpos->text("--------------------------------\n");

            $escpos->setJustification(\Mike42\Escpos\Printer::JUSTIFY_LEFT);
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

            /* FOOTER */
            $escpos->feed(1);
            $escpos->setJustification(\Mike42\Escpos\Printer::JUSTIFY_CENTER);
            $escpos->text(($data['footer'] ?? 'Terima kasih') . "\n");

            $escpos->cut();
            $escpos->close();
            return 'adsf';

        } catch (\Exception $e) {
            dd($e);
            Log::error("Failed to print receipt for printer ID {$printer->id}: " . $e->getMessage());
            // Return failure or notify failure
            return [
                'success' => false,
                'message' => 'Gagal mencetak struk, coba lagi!'
            ];
        }

//        PrintReceiptJob::dispatch([$printer->id], $data);

        return response()->json([
            'message' => 'Pencetakan struk dimulai, tunggu sebentar.'
        ]);
    }
}
