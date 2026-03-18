<?php

namespace Modules\Management\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Outlet;
use App\Models\Payment;
use App\Services\ExportService;
use Carbon\Carbon;
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
        $raw = Order::where('outlet_id', active_outlet_id())
            ->filters()
            ->where('status', 'COMPLETED')
            ->latest();

        $currentQueries = \request()->query();
        $xls = ['download' => 'XLS'];
        $xlsQ = array_merge($currentQueries, $xls);
        $xlsUrl = \request()->fullUrlWithQuery($xlsQ);

        if (request('download')) {
            if (\request()->download == 'XLS') {
                $period = null;

                if (request('date_range_order')) {
                    $date = get_start_and_end_date(request('date_range_order'));

                    $start = parse_date($date['start_date']);
                    $end   = parse_date($date['end_date']);

                    $period = $start.' s.d '.$end;
                }

                $title = 'Analisa Kategori - Nota'.(!empty($period) ? '<br>Periode '.$period : '');

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

        if(\request('date_range_order')){
            $date = get_start_and_end_date(request('date_range_order'));
            $start = Carbon::parse($date['start_date'])->startOfDay();
            $end = Carbon::parse($date['end_date'])->endOfDay();
        }

        $raw = OrderItem::query()
            ->with(['menu:id,name,sku,category'])
            ->whereHas('order', function ($q) use ($start, $end) {
                $q->where('outlet_id', active_outlet_id())
                    ->whereBetween('created_at', [$start, $end])
                    ->where('status', 'COMPLETED');
            })
            ->filters()
            ->selectRaw("
                menu_id,
                SUM(qty) as qty_terjual,
                SUM(qty * hpp) as total_hpp,
                SUM(subtotal) as total_harga_jual,
                SUM(subtotal - (qty * hpp)) as total_omzet
            ")
            ->groupBy('menu_id')
            ->orderByDesc('qty_terjual');


        $currentQueries = \request()->query();
        $xls = ['download' => 'XLS'];
        $xlsQ = array_merge($currentQueries, $xls);
        $xlsUrl = \request()->fullUrlWithQuery($xlsQ);

        if (request('download')) {
            if (\request()->download == 'XLS') {
                $period = null;

                if (request('date_range_order')) {
                    $date = get_start_and_end_date(request('date_range_order'));

                    $start = parse_date($date['start_date']);
                    $end   = parse_date($date['end_date']);

                    $period = $start.' s.d '.$end;
                }

                $title = 'Analisa Kategori - Menu'.(!empty($period) ? '<br>Periode '.$period : '');
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

        $raw = Payment::query()
            ->filters()
            ->whereHasMorph('payable', Order::class, function ($query) use ($start, $end){
                $query->where('outlet_id', active_outlet_id())
                    ->whereBetween('created_at', [$start, $end])
                    ->where('status', 'COMPLETED');
                })
            ->join('orders', function ($join) {
                $join->on('orders.id', '=', 'payments.payable_id')
                    ->where('orders.outlet_id', active_outlet_id())
                    ->where('payments.payable_type', Order::class);
            })
            ->selectRaw("
                payments.method,
                COUNT(payments.id) as jumlah_transaksi,
                SUM(orders.grand_total) as total_nominal
            ")
            ->groupBy('payments.method')
            ->orderByDesc('total_nominal');

        $currentQueries = \request()->query();
        $xls = ['download' => 'XLS'];
        $xlsQ = array_merge($currentQueries, $xls);
        $xlsUrl = \request()->fullUrlWithQuery($xlsQ);

        if (request('download')) {
            if (\request()->download == 'XLS') {
                $period = null;

                if (request('date_range_order')) {
                    $date = get_start_and_end_date(request('date_range_order'));

                    $start = parse_date($date['start_date']);
                    $end   = parse_date($date['end_date']);

                    $period = $start.' s.d '.$end;
                }

                $title = 'Analisa Kategori - Metode Pembayaran'.(!empty($period) ? '<br>Periode '.$period : '');
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

        $sales = Order::with([
                'items:id,order_id,menu_id,qty,hpp,subtotal',
                'items.menu:id,name,sku,category'
            ])
            ->filters()
            ->whereHas('payments', function ($query){
                $query->hasMorph('payable', [Order::class]);
            })
            ->where('outlet_id', active_outlet_id())
            ->whereBetween('created_at', [$start, $end])
            ->where('status', 'COMPLETED')
            ->get()
            ->groupBy('type')->map(function ($group) {

                return (object)[
                    'jenis_order' => $group->first()->type,
                    'jumlah_transaksi' => $group->count(),
                    'total_nominal' => $group->flatMap->payments->sum('amount'),
                    'items' => $group->flatMap->items
                ];

            });

        $currentQueries = \request()->query();
        $xls = ['download' => 'XLS'];
        $xlsQ = array_merge($currentQueries, $xls);
        $xlsUrl = \request()->fullUrlWithQuery($xlsQ);

        if (request('download')) {
            if (\request()->download == 'XLS') {
                $period = null;

                if (request('date_range_order')) {
                    $date = get_start_and_end_date(request('date_range_order'));

                    $start = parse_date($date['start_date']);
                    $end   = parse_date($date['end_date']);

                    $period = $start.' s.d '.$end;
                }

                $title = 'Analisa Kategori - Order'.(!empty($period) ? '<br>Periode '.$period : '');
                $outlet = Outlet::find(active_outlet_id())->name;

                $export = new ExportService($sales, 'management::sales.category_analysis.order.xls', ['thead_rows' => 3, 'outlet' => $outlet, 'title' => $title]);

                return Excel::download($export, $title.'.xls');
            }
        }

        return view('management::sales.category_analysis.order.index', compact('sales', 'xlsUrl'));
    }

    public function detailOrder($type){
        $items = OrderItem::whereHas('order', function ($query) use ($type){
                $query->where('type', $type);
            })
            ->paginate();

        return view('management::sales.category_analysis.order.detail', compact('items'));
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
            'paid_amount'       => $order->paid_amount,
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
