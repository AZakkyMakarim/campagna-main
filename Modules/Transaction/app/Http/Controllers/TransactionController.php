<?php

namespace Modules\Transaction\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // === STATS ===
        $transactionsToday = Order::whereDate('created_at', $today)->where('outlet_id', active_outlet_id())->count();

        $revenueToday = Order::whereDate('created_at', $today)
            ->where('outlet_id', active_outlet_id())
            ->where('payment_status', 'PAID')
            ->sum('grand_total');

        $ongoingOrders = Order::whereIn('status', ['OPEN', 'IN_PROGRESS'])->where('outlet_id', active_outlet_id())->count();

        $revenueYesterday = Order::whereDate('created_at', $yesterday)
            ->where('outlet_id', active_outlet_id())
            ->where('payment_status', 'PAID')
            ->sum('grand_total');

        $deltaPercent = $revenueYesterday > 0
            ? round((($revenueToday - $revenueYesterday) / $revenueYesterday) * 100, 1)
            : 100;

        $stats = [
            'transactions_today' => $transactionsToday,
            'revenue_today'      => $revenueToday,
            'ongoing_orders'     => $ongoingOrders,
            'delta_percent'      => $deltaPercent,
        ];

        // === TOP MENUS ===
        $topMenus = OrderItem::select('menu_id', DB::raw('SUM(qty) as total_sold'))
            ->whereHas('order', function ($query){
                $query->where('outlet_id', active_outlet_id());
            })
            ->whereDate('created_at', $today)
            ->groupBy('menu_id')
            ->orderByDesc('total_sold')
            ->with('menu')
            ->limit(5)
            ->get()
            ->map(function ($row) {
                return (object)[
                    'name' => $row->menu->name,
                    'total_sold' => $row->total_sold
                ];
            });

        // === LOW STOCK ===
        $lowStocks = Ingredient::withSum('batches as total_stock', 'qty_remaining')
            ->where('outlet_id', active_outlet_id())
            ->having('total_stock', '<', 10)
            ->orderBy('total_stock', 'asc')
            ->limit(5)
            ->get();

        // === PAYMENT METHODS ===
        $paymentMethods = Payment::select('method', DB::raw('SUM(amount) as total'))
            ->whereDate('paid_at', $today)   // atau created_at, tergantung lu simpan di mana
            ->whereHasMorph('payable', Order::class, function ($q) {
                $q->where('outlet_id', active_outlet_id())->where('payment_status', 'PAID');  // pastiin ordernya valid
            })
            ->groupBy('method')
            ->orderByDesc('total')
            ->get();

        // === CHART DATA (PER JAM) ===
        $salesPerHour = Order::select(
            DB::raw('HOUR(created_at) as hour'),
            DB::raw('SUM(grand_total) as total')
        )
            ->where('outlet_id', active_outlet_id())
            ->whereDate('created_at', $today)
            ->where('payment_status', 'PAID')
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->orderBy('hour')
            ->get();

        $ongoingOrders = Order::with('items')
            ->where('outlet_id', active_outlet_id())
            ->whereIn('status', ['OPEN', 'IN_PROGRESS', 'READY'])
            ->orderBy('opened_at', 'asc')
            ->limit(10) // ambil dikit aja, nanti di view dipotong 5
            ->get();

        $labels = [];
        $values = [];

        foreach ($salesPerHour as $row) {
            $labels[] = str_pad($row->hour, 2, '0', STR_PAD_LEFT) . ':00';
            $values[] = $row->total;
        }

        $chartData = [
            'labels' => $labels,
            'values' => $values,
        ];

        return view('transaction::index', compact(
            'stats',
            'topMenus',
            'lowStocks',
            'paymentMethods',
            'chartData',
            'ongoingOrders'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('transaction::create');
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
        return view('transaction::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('transaction::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}
}
