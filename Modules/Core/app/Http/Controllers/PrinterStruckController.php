<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Outlet;
use App\Models\Printer;
use App\Services\PrinterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrinterStruckController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $outlets = Outlet::query()
            ->where('business_id', Auth::user()->business_id)
            ->get();

        $printers = Printer::query()
            ->where('outlet_id', active_outlet_id())
            ->latest()
            ->get();

        $rawMenus = Menu::where('outlet_id', active_outlet_id())->latest();

        $sections = (clone $rawMenus)->pluck('category')->unique();

        return view('core::printer_struck.index', compact('printers', 'outlets', 'sections'));
    }

    public function printTest(PrinterService $service, Printer $printer){
        $data = [
            'role'                      => 'test',
            'printer_connection_type'   => $printer->connection_type,
            'printer_ip'                => $printer->ip_address,
            'printer_port'              => $printer->port,
        ];

        $service->print($data);

        toast('Test Print Berhasil!');
        return back();
    }
}
