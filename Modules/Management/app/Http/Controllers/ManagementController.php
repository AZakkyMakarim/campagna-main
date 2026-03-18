<?php

namespace Modules\Management\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        // ==============================
        // DATE RANGE
        // ==============================

        if ($request->date_range) {
            $date = get_start_and_end_date($request->date_range, false);
            $startDate = Carbon::parse($date['start_date'])->startOfDay();
            $endDate = Carbon::parse($date['end_date'])->endOfDay();
        } else {
            $startDate = Carbon::today();
            $endDate   = Carbon::today()->endOfDay();
        }

        // ==============================
        // BASIC STATS
        // ==============================

        $transactionsToday = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('outlet_id', active_outlet_id())
            ->where('payment_status', 'PAID')
            ->count();

        $revenueToday = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('outlet_id', active_outlet_id())
            ->where('payment_status', 'PAID')
            ->sum('grand_total');

        $ongoingOrders = Order::whereIn('status', ['OPEN','IN_PROGRESS','READY'])
            ->where('outlet_id', active_outlet_id())
            ->count();

        $avgTransaction = $transactionsToday > 0
            ? round($revenueToday / $transactionsToday)
            : 0;

        // ==============================
        // SALES PER HOUR
        // ==============================

        $salesPerHour = Order::select(
            DB::raw('HOUR(created_at) as hour'),
            DB::raw('SUM(grand_total) as total')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('outlet_id', active_outlet_id())
            ->where('payment_status','PAID')
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->orderBy('hour')
            ->get();

        $labels = [];
        $values = [];

        foreach ($salesPerHour as $row) {
            $labels[] = str_pad($row->hour,2,'0',STR_PAD_LEFT).':00';
            $values[] = $row->total;
        }

        $chartData = [
            'labels'=>$labels,
            'values'=>$values
        ];

        // ==============================
        // SALES PER CATEGORY
        // ==============================

        $salesPerCategory = OrderItem::select(
                'menus.category',
                DB::raw('SUM(order_items.qty) as total')
            )
            ->join('menus','menus.id','=','order_items.menu_id')
            ->join('orders', function ($join) {
                $join->on('orders.id', '=', 'order_items.order_id')
                    ->where('orders.outlet_id', active_outlet_id());
            })
            ->whereBetween('order_items.created_at',[$startDate,$endDate])
            ->groupBy('menus.category')
            ->orderByDesc('total')
            ->get();

        $categoryChart = [
            'labels'=>$salesPerCategory->pluck('category'),
            'values'=>$salesPerCategory->pluck('total')
        ];

        // ==============================
        // TOP MENU
        // ==============================

        $topMenus = OrderItem::select(
                'menu_id',
                DB::raw('SUM(qty) as total_sold')
            )
            ->whereBetween('order_items.created_at',[$startDate,$endDate])
            ->join('orders', function ($join) {
                $join->on('orders.id', '=', 'order_items.order_id')
                    ->where('orders.outlet_id', active_outlet_id());
            })
            ->groupBy('menu_id')
            ->with('menu')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        // ==============================
        // LEAST MENU
        // ==============================

        $leastMenus = OrderItem::select(
                'menu_id',
                DB::raw('SUM(qty) as total_sold')
            )
            ->whereBetween('order_items.created_at',[$startDate,$endDate])
            ->join('orders', function ($join) {
                $join->on('orders.id', '=', 'order_items.order_id')
                    ->where('orders.outlet_id', active_outlet_id());
            })
            ->groupBy('menu_id')
            ->with('menu')
            ->orderBy('total_sold')
            ->limit(5)
            ->get();

        // ==============================
        // PAYMENT METHODS
        // ==============================

        $paymentMethods = Payment::select(
                'method',
                DB::raw('SUM(amount) as total')
            )
            ->whereBetween('paid_at',[$startDate,$endDate])
            ->join('orders', function ($join) {
                $join->on('orders.id', '=', 'payments.payable_id')
                    ->where('orders.outlet_id', active_outlet_id())
                    ->where('payments.payable_type', Order::class);
            })
            ->groupBy('method')
            ->get();

        $paymentMethods = $paymentMethods->map(function($p) use ($revenueToday){

            $percent = $revenueToday > 0
                ? round(($p->total / $revenueToday) * 100,1)
                : 0;

            return (object)[
                'method'=>$p->method,
                'total'=>$p->total,
                'percent'=>$percent
            ];
        });

        // ==============================
        // ONGOING ORDERS
        // ==============================

        $ongoingOrders = Order::with('items')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('outlet_id', active_outlet_id())
            ->whereIn('status',['OPEN','IN_PROGRESS','READY'])
            ->orderBy('opened_at')
            ->limit(10)
            ->get();

        // ==============================
        // STATS
        // ==============================

        $stats = [
            'revenue_today'=>$revenueToday,
            'transactions_today'=>$transactionsToday,
            'ongoing_orders'=>$ongoingOrders->count(),
            'avg_transaction'=>$avgTransaction
        ];

        return view('management::index', compact(
            'stats',
            'topMenus',
            'leastMenus',
            'paymentMethods',
            'chartData',
            'ongoingOrders',
            'categoryChart'
        ));
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
}
