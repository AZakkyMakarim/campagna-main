<?php

namespace Modules\Transaction\Http\Controllers;

use App\Events\OrderCreated;
use App\Http\Controllers\Controller;
use App\Models\CashMovement;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderAdjustment;
use App\Models\OrderItem;
use App\Models\Outlet;
use App\Models\Payment;
use App\Models\Printer;
use App\Models\TaxRule;
use App\Services\PrinterService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ListOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $shift = active_shift();

        $shiftStart = Carbon::parse($shift->opened_at);
        $shiftEnd = Carbon::parse(@$shift->closed_at ?? now());

        $orders = Order::with(['items', 'payments'])
            ->filters()
            ->where('outlet_id', active_outlet_id())
            ->whereBetween('created_at', [$shiftStart, $shiftEnd])
            ->latest()
            ->get();

        $summary = [
            'total_orders' => $orders->count(),
            'total_revenue' => $orders->sum('grand_total'),
            'revenue_by_method' => $orders
                ->groupBy(fn($order) => optional($order->payments->first())->method)
                ->map(function ($orders) {
                    return [
                        'jumlah_transaksi' => $orders->count(),
                        'total_nominal' => $orders->sum('grand_total')
                    ];
                })
        ];

        return view('transaction::list_order.index', compact('orders', 'summary'));
    }

    public function reOrder(Order $order){
        $outletId = active_outlet_id();

        $outlet = Outlet::findOrFail($outletId);
        $taxes = TaxRule::where('business_id', auth()->user()->business_id)->where('is_active', 1)->get();

        $raw = Menu::query()
            ->where('outlet_id', $outletId)
            ->where('is_active', true);

        $categories = (clone $raw)->pluck('category')->unique();

        $menus = (clone $raw)
            ->orderBy('category')
            ->orderBy('name')
            ->get();


        return view('transaction::list_order.reorder', compact('order', 'menus', 'categories', 'outlet', 'taxes'));
    }

    public function printStruck(){
        $servicePrinter = new PrinterService();

        $order = Order::find(\request('order_id'));

        $printers = Printer::where('outlet_id', active_outlet_id())
            ->where('role', 'cashier')
            ->where('is_active', 1)
            ->get();

        foreach ($printers as $printer) {
            $data = [
                'role' => $printer->role,
                'printer_connection_type' => $printer->connection_type,
                'printer_ip' => $printer->ip_address,
                'printer_port' => $printer->port,
                'order' => $order,
                'items' => $order->items,
            ];

            $servicePrinter->print($data);
        }

        return response()->json([
            'success' => true,
            'order' => $order->fresh(['items', 'adjustments']),
        ]);
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
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $order = Order::findOrFail($request->order_id);

            $menuIds = collect($request->items)->pluck('menu_id');

            $menus = Menu::whereIn('id', $menuIds)
                ->get()
                ->keyBy('id');

            $lastBatch = (int) $order->items()->max('batch');

            foreach ($request->items as $item) {
                $menu = $menus[$item['menu_id']];

                $doneQty = 0;
                if (!in_array($menu->category, [
                    'makanan',
                    'wedangan',
                    'minuman',
                    'jede sate',
                    'jede bakmi',
                    'paket nasi'
                ])) {
                    $doneQty = $item['qty'];
                }

                OrderItem::create([
                    'order_id'      => $order->id,
                    'menu_id'       => $menu->id,
                    'name_snapshot' => $menu->name,
                    'qty'           => $item['qty'],
                    'done_qty'      => $doneQty,
                    'hpp'           => $menu->calculateHppDynamic(),
                    'price'         => $menu->sell_price,
                    'subtotal'      => $menu->sell_price * $item['qty'],
                    'note'          => $item['note'] ?? null,
                    'batch'         => $lastBatch + 1,
                ]);
            }

            $this->recalculateOrderTotals($order);
            $this->printOrderKitchen($order, $lastBatch + 1);

            DB::commit();

            broadcast(new OrderCreated($order->fresh(['items', 'adjustments'])));

            return response()->json([
                'success' => true,
                'order' => $order->fresh(['items', 'adjustments']),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

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

    public function pay(Request $request)
    {
        DB::beginTransaction();

        try {
            $order = Order::with('payments')->findOrFail($request->order_id);
            $payments = $request->payments; // [{method, amount}, ...]

            if (empty($payments)) {
                throw new \Exception('Data pembayaran tidak boleh kosong');
            }

            $totalNewPayment = collect($payments)->sum('amount');
            if ($totalNewPayment <= 0) {
                throw new \Exception('Total pembayaran tidak valid');
            }

            foreach ($payments as $p) {
                Payment::create([
                    'payable_type' => Order::class,
                    'payable_id'   => $order->id,
                    'cashier_id'   => auth()->id(),
                    'type'         => 'ORDER',
                    'method'       => $p['method'],
                    'amount'       => $p['amount'],
                    'paid_at'      => now(),
                ]);

                CashMovement::create([
                    'cashier_shift_id' => active_shift()->id,
                    'outlet_id'        => active_outlet_id(),
                    'user_id'          => auth()->id(),
                    'type'             => 'IN',
                    'category'         => 'ORDER',
                    'amount'           => $p['amount'],
                    'description'      => 'Pembelian Produk - ' . $p['method'],
                ]);
            }

            $newTotalPaid = $order->fresh()->payments()->sum('amount');

            if ($newTotalPaid >= $order->grand_total) {
                $order->update([
                    'payment_status' => 'PAID',
                    'status'         => 'COMPLETED',
                    'closed_at'      => now(),
                ]);
            } else {
                $order->update(['payment_status' => 'PARTIAL']);
            }

            // Hitung kembalian dari metode Tunai
            $alreadyPaidNonCash = $order->fresh()->payments()
                ->whereNotIn('method', ['CASH', 'TUNAI'])
                ->sum('amount');
            $cashPaid = collect($payments)
                ->filter(fn($p) => in_array(strtoupper($p['method']), ['CASH', 'TUNAI']))
                ->sum('amount');
            
            $remaining = max(0, $order->grand_total - $alreadyPaidNonCash - $cashPaid);
            $change = max(0, $cashPaid - $remaining);

            DB::commit();

            return response()->json([
                'success' => true,
                'change'  => $change,
                'payment_status' => $order->fresh()->payment_status,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function recalculateOrderTotals(Order $order): void
    {
        $order->loadMissing('items', 'adjustments');

        $subTotal = (float) $order->items->sum('subtotal');

        $adjustmentTotal = 0;

        // =========================
        // HITUNG TAX / ADJUSTMENT
        // =========================
        foreach ($order->adjustments->where('type', '!=', 'ROUNDING') as $adj) {

            $amount = $adj->method === 'percent'
                ? $subTotal * $adj->value / 100
                : $adj->value;

            $adj->update([
                'amount' => abs($amount),
            ]);

            $adjustmentTotal += $adj->is_addition
                ? abs($amount)
                : -abs($amount);
        }

        // =========================
        // GRAND TOTAL SEMENTARA
        // =========================
        $grandBeforeRounding = $subTotal + $adjustmentTotal;

        // =========================
        // ROUNDING
        // =========================
        $rounding = calculate_rounding($grandBeforeRounding);

        $roundingAdj = $order->adjustments
            ->firstWhere('type', 'ROUNDING');

        if ($roundingAdj) {

            $roundingAdj->update([
                'amount' => abs($rounding),
                'is_addition' => $rounding > 0,
            ]);

        } elseif ($rounding != 0) {

            OrderAdjustment::create([
                'order_id' => $order->id,
                'type'     => 'ROUNDING',
                'name'     => 'Pembulatan',
                'method'   => 'auto',
                'value'    => 0,
                'amount'   => abs($rounding),
                'is_addition' => $rounding > 0,
            ]);
        }

        $adjustmentTotal += $rounding;

        // =========================
        // UPDATE ORDER
        // =========================
        $order->update([
            'sub_total' => $subTotal,
            'adjustment_total' => $adjustmentTotal,
            'grand_total' => $subTotal + $adjustmentTotal,
        ]);
    }

    private function printOrderKitchen($order, $batch)
    {
        $servicePrinter = new PrinterService();

        $order->load('items.menu');

        $printers = Printer::where('outlet_id', active_outlet_id())
            ->where('is_active', 1)
            ->get();

        foreach ($printers as $printer) {

            // === printer kasir ===
            if ($printer->role === 'cashier') {
                continue;
            }

            // === printer dapur/bar ===
            $sections = json_decode($printer->section) ?? [];

            $items = $order->items->where('batch', $batch)->filter(function ($item) use ($sections) {
                return in_array($item->menu->category, $sections);
            });

            if ($items->isEmpty()) {
                continue;
            }

            $data = [
                'role'                      => $printer->role,
                'printer_connection_type'   => $printer->connection_type,
                'printer_ip'                => $printer->ip_address,
                'printer_port'              => $printer->port,
                'order'                     => $order,
                'items'                     => $items,
            ];

            $servicePrinter->print($data);
        }
    }
}
