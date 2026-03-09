<?php

namespace Modules\Management\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Outlet;
use App\Services\ExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class CategoryAnalysisController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function nota()
    {
        $raw = Order::where('outlet_id', active_outlet_id())->where('status', 'COMPLETED');

        $currentQueries = \request()->query();
        $xls = ['download' => 'XLS'];
        $xlsQ = array_merge($currentQueries, $xls);
        $xlsUrl = \request()->fullUrlWithQuery($xlsQ);

        if (request('download')) {
            if (\request()->download == 'XLS') {
                $title = 'Analisa Kategori - Nota';
                $outlet = Outlet::find(active_outlet_id())->name;

                $export = new ExportService($raw->get(), 'management::sales.category_analysis.nota.xls', ['thead_rows' => 3, 'outlet' => $outlet, 'title' => $title]);

                return Excel::download($export, $title.'.xls');
            }
        }

        $sales = $raw->paginate();

        return view('management::sales.category_analysis.nota.index', compact('sales', 'xlsUrl'));
    }

    public function menu(){
        $start = $request->start_date ?? now()->subMonth()->startOfMonth();
        $end   = $request->end_date ?? now()->endOfMonth();

        $raw = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('menus as m', 'm.id', '=', 'oi.menu_id')
            ->where('o.outlet_id', active_outlet_id())
            ->whereBetween('o.created_at', [$start, $end])
            ->where('o.status', 'COMPLETED') // atau PAID sesuai sistem lu
            ->select(
                'm.id',
                'm.name',
                'm.sku',
                'm.category',
                DB::raw('SUM(oi.qty) as qty_terjual'),
                DB::raw('SUM(oi.qty * oi.hpp) as total_hpp'),
                DB::raw('SUM(oi.subtotal) as total_harga_jual'),
                DB::raw('SUM(oi.subtotal - (oi.qty * oi.hpp)) as total_omzet')
            )
            ->groupBy('m.id', 'm.name', 'm.sku', 'm.category')
            ->orderByDesc('qty_terjual');


        $currentQueries = \request()->query();
        $xls = ['download' => 'XLS'];
        $xlsQ = array_merge($currentQueries, $xls);
        $xlsUrl = \request()->fullUrlWithQuery($xlsQ);

        if (request('download')) {
            if (\request()->download == 'XLS') {
                $title = 'Analisa Kategori - Menu';
                $outlet = Outlet::find(active_outlet_id())->name;

                $export = new ExportService($raw->get(), 'management::sales.category_analysis.menu.xls', ['thead_rows' => 3, 'outlet' => $outlet, 'title' => $title]);

                return Excel::download($export, $title.'.xls');
            }
        }

        $sales = $raw->paginate();

        return view('management::sales.category_analysis.menu.index', compact('sales', 'xlsUrl'));
    }

    public function paymentMethod(){
        $start = $request->start_date ?? now()->subMonth()->startOfMonth();
        $end   = $request->end_date ?? now()->endOfMonth();

        $raw = DB::table('payments as p')
            ->join('orders as o', function ($join) {
                $join->on('o.id', '=', 'p.payable_id')
                    ->where('p.payable_type', \App\Models\Order::class);
            })
            ->where('o.outlet_id', active_outlet_id())
            ->whereBetween('o.created_at', [$start, $end])
            ->where('o.status', 'COMPLETED')
            ->select(
                'p.method',
                DB::raw('COUNT(p.id) as jumlah_transaksi'),
                DB::raw('SUM(p.amount) as total_nominal')
            )
            ->groupBy('p.method')
            ->orderByDesc('total_nominal');

        $currentQueries = \request()->query();
        $xls = ['download' => 'XLS'];
        $xlsQ = array_merge($currentQueries, $xls);
        $xlsUrl = \request()->fullUrlWithQuery($xlsQ);

        if (request('download')) {
            if (\request()->download == 'XLS') {
                $title = 'Analisa Kategori - Metode Pembayaran';
                $outlet = Outlet::find(active_outlet_id())->name;

                $export = new ExportService($raw->get(), 'management::sales.category_analysis.payment_method.xls', ['thead_rows' => 3, 'outlet' => $outlet, 'title' => $title]);

                return Excel::download($export, $title.'.xls');
            }
        }

        $sales = $raw->paginate();

        return view('management::sales.category_analysis.payment_method.index', compact('sales', 'xlsUrl'));
    }

    public function order(){
        $start = $request->start_date ?? now()->subMonth()->startOfMonth();
        $end   = $request->end_date ?? now()->endOfMonth();

        $raw = DB::table('orders as o')
            ->join('payments as p', function ($join) {
                $join->on('o.id', '=', 'p.payable_id')
                    ->where('p.payable_type', \App\Models\Order::class);
            })
            ->where('o.outlet_id', active_outlet_id())
            ->whereBetween('o.created_at', [$start, $end])
            ->where('o.status', 'COMPLETED')
            ->select(
                'o.type as jenis_order',
                DB::raw('COUNT(DISTINCT o.id) as jumlah_transaksi'),
                DB::raw('SUM(p.amount) as total_nominal')
            )
            ->groupBy('o.type')
            ->orderByDesc('total_nominal');

        $currentQueries = \request()->query();
        $xls = ['download' => 'XLS'];
        $xlsQ = array_merge($currentQueries, $xls);
        $xlsUrl = \request()->fullUrlWithQuery($xlsQ);

        if (request('download')) {
            if (\request()->download == 'XLS') {
                $title = 'Analisa Kategori - Order';
                $outlet = Outlet::find(active_outlet_id())->name;

                $export = new ExportService($raw->get(), 'management::sales.category_analysis.order.xls', ['thead_rows' => 3, 'outlet' => $outlet, 'title' => $title]);

                return Excel::download($export, $title.'.xls');
            }
        }

        $sales = $raw->paginate();

        return view('management::sales.category_analysis.order.index', compact('sales', 'xlsUrl'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('management::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('management::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('management::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}

    public function getOrder($id){
        $order = Order::with([
            'items.menu',
            'adjustments',
            'reservation',
        ])
            ->where('outlet_id', active_outlet_id())
            ->findOrFail($id);

        return response()->json([
            'id'                => $order->id,
            'code'              => $order->code,
            'type'              => $order->type,
            'channel'           => $order->channel,
            'table_number'      => optional($order->reservation)->table_number,
            'sub_total'         => $order->sub_total,
            'grand_total'       => $order->grand_total,
            'status'            => $order->status,
            'payment_status'    => $order->payment_status,
            'hpp_total'         => $order->calculateHpp(),
            'adjustments' => $order->adjustments->map(function ($adj) {
                return [
                    'id' => $adj->id,
                    'name' => $adj->name,
                    'type' => $adj->type, // 'tax','discount','rounding','service'
                    'calculation_type' => $adj->calculation_type, // 'percent'|'fixed'
                    'value' => $adj->value,
                    'amount' => $adj->amount, // hasil final nominal
                    'is_active' => true,
                ];
            }),
            'items' => $order->items->map(function ($item) {
                return [
                    'menu_id'           => $item->menu_id,
                    'name'              => $item->menu->name ?? '-',
                    'qty'               => $item->qty,
                    'price'             => $item->price,
                    'subtotal'          => $item->qty * $item->price,
                    'note'              => $item->note,
                ];
            }),
        ]);
    }
}
