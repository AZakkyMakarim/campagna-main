<?php

namespace Modules\Transaction\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\ConsumeStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KitchenDisplayController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $outletId = active_outlet_id();

        $orders = Order::with([
            'items.menu',   // biar bisa filter menu dapur
        ])
            ->where('outlet_id', $outletId)
            ->whereIn('status', ['OPEN', 'IN_PROGRESS', 'READY']) // order yg masih jalan
            ->orderBy('opened_at', 'asc')
            ->get()
            ->map(function ($order) {
                $order->channel_display_name =
                    config('array.order.channel.' . $order->channel . '.display_name')
                    ?? $order->channel;

                return $order;
            });

        return view('transaction::kitchen_display.index', compact('orders'));
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

    public function updateItems(Request $request)
    {
        $consumeStockService = new ConsumeStockService();
        DB::beginTransaction();
        try {
            $orderId = null;

            foreach ($request->items as $item) {
                $orderItem = OrderItem::findOrFail($item['id']);

                $max = $orderItem->qty;
                if (($item['done_qty'] + $item['void_qty']) > $max) {
                    throw new \Exception('Qty melebihi pesanan');
                }

                $consumeStockService->consumeMenuRecursive($orderItem->menu, $item['done_qty']);

                $orderItem->update([
                    'done_qty' => $item['done_qty'],
                    'void_qty' => $item['void_qty'],
                ]);

                $orderId = $orderItem->order_id;
            }

            // =========================
            // CEK PROGRESS ORDER
            // =========================
            if ($orderId) {
                $order = Order::with('items')->findOrFail($orderId);

                $totalUnits = $order->items->sum('qty');
                $finishedUnits = $order->items->sum(function ($i) {
                    return ($i->done_qty ?? 0) + ($i->void_qty ?? 0);
                });

                if ($totalUnits > 0 && $finishedUnits >= $totalUnits) {
                    // Semua sudah selesai / di-void
                    $order->update([
                        'status' => 'COMPLETED', // atau 'READY' sesuai flow lu
                    ]);
                }
            }

            DB::commit();
            return response()->json(['success' => true]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
