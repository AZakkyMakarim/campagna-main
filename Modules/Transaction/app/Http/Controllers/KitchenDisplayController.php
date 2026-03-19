<?php

namespace Modules\Transaction\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
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

        $categories = [
            'makanan',
            'wedangan',
            'minuman',
            'sate',
            'bakmi',
            'jede sate',
            'jede bakmi',
            'paket nasi'
        ];

        $orders = Order::
            with([
                'items' => function ($q) use ($categories) {
                    $q->whereHas('menu', function ($q) use ($categories) {
                        $q->whereIn(DB::raw('LOWER(category)'), $categories);
                    })->with('menu');
                }
            ])
            ->filters()
            ->where('outlet_id', $outletId)
            ->whereIn('status', ['OPEN', 'IN_PROGRESS', 'READY'])
            ->orderBy('opened_at', 'asc')
//            ->latest()
            ->get()
            ->map(function ($order) {

                $order->channel_display_name =
                    config('array.order.channel.' . $order->channel . '.display_name')
                    ?? $order->channel;

                // 🔥 FIX: assign hasil map ke items
                $order->items = $order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name_snapshot' => $item->name_snapshot,
                        'qty' => $item->qty,
                        'note' => $item->note,
                        'done_qty' => $item->done_qty ?? 0,
                        'void_qty' => $item->void_qty ?? 0,
                        'category' => strtolower($item->menu->category ?? 'lainnya')
                    ];
                })->values();

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

            $orderId = $request->order_id;

            foreach ($request->items as $item) {

                $orderItem = OrderItem::findOrFail($item['id']);

                // set done = qty
                $doneQty = $orderItem->qty;

                // consume stock
                $consumeStockService->consumeMenuRecursive(
                    $orderItem->menu,
                    $doneQty
                );

                $orderItem->update([
                    'done_qty' => $doneQty,
                    'void_qty' => 0,
                ]);
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

                if ($totalUnits > 0 && $finishedUnits >= $totalUnits && $order->payment_status == 'PAID') {

                    $order->update([
                        'status' => 'COMPLETED'
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);

        }
    }
}
