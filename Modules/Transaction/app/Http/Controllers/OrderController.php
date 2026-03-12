<?php

namespace Modules\Transaction\Http\Controllers;

use App\Events\OrderCreated;
use App\Http\Controllers\Controller;
use App\Jobs\PrintReceiptJob;
use App\Models\Ingredient;
use App\Models\IngredientBatch;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderAdjustment;
use App\Models\OrderItem;
use App\Models\Outlet;
use App\Models\Payment;
use App\Models\Printer;
use App\Models\Recipe;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\TaxRule;
use App\Services\PrinterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $outletId = active_outlet_id();

        $outlet = Outlet::findOrFail($outletId);

        $raw = Menu::query()
            ->where('outlet_id', $outletId)
            ->where('is_active', true);


        $categories = (clone $raw)->pluck('category')->unique();

        // =========================
        // MENU LIST (READY JUAL)
        // =========================
        $menus = (clone $raw)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $taxes = TaxRule::where('business_id', auth()->user()->business_id)->where('is_active', 1)->get();

        $printers = Printer::where('role', 'cashier')
            ->whereHas('outlet', function ($query){
                $query->where('business_id', auth()->user()->business_id);
            })
            ->get();

        return view('transaction::order.index', compact('outlet','menus', 'printers', 'taxes', 'categories'));
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
            $user = auth()->user();
            $outletId = active_outlet_id();
            $settings = Setting::where('outlet_id', $outletId);


            if (empty($request->items)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Keranjang kosong'
                ], 422);
            }

            // =========================
            // HITUNG SUBTOTAL
            // =========================
            $menuIds = collect($request->items)->pluck('menu_id');
            $menus = Menu::whereIn('id', $menuIds)->get()->keyBy('id');

            $subTotal = 0;

            foreach ($request->items as $item) {
                if (!isset($menus[$item['menu_id']])) {
                    throw new \Exception('Menu tidak ditemukan');
                }

                $subTotal += $menus[$item['menu_id']]->sell_price * $item['qty'];
            }

            // =========================
            // HITUNG TAX
            // =========================
            $taxTotal = 0;
            $taxes = TaxRule::where('business_id', $user->business_id)
                ->where('is_active', true)
                ->get();

            foreach ($taxes as $tax) {
                if ($tax->calculation_type === 'percent') {
                    $taxTotal += $subTotal * $tax->value / 100;
                } else {
                    $taxTotal += $tax->value;
                }
            }

            // =========================
            // GRAND TOTAL + ROUNDING
            // =========================
            $grossTotal = $subTotal + $taxTotal;
            $rounding = calculate_rounding($grossTotal);
            $finalTotal = $grossTotal + $rounding;

            // =========================
            // PAYMENT LOGIC
            // =========================
            $paymentType = $request->payment_type; // PAY | DRAFT
            $paymentMode = $request->payment['mode'] ?? null; // FULL | DP
            $paidAmount  = $request->payment['amount'] ?? 0;

            $isDraft = $paymentType === 'DRAFT';

            if ($isDraft) {
                $orderStatus = 'OPEN';
                $paymentStatus = 'UNPAID';
            } else {
                if ($paymentMode === 'FULL') {
                    if ($paidAmount < $finalTotal) {
                        throw new \Exception('Pembayaran kurang');
                    }
                    $orderStatus = 'OPEN';
                    $paymentStatus = 'PAID';
                } else {
                    // DP
                    if ($paidAmount <= 0) {
                        throw new \Exception('Nominal DP tidak valid');
                    }
                    $orderStatus = 'OPEN';
                    $paymentStatus = 'PARTIAL';
                }
            }

            // =========================
            // CREATE ORDER
            // =========================
            $prefixOrder = (clone $settings)->where('name', 'prefix queue')->first();
            $prefixCode = (clone $settings)->where('name', 'prefix transaction')->first();
            $resetTransaction = (clone $settings)->where('name', 'reset transaction')->first();

            if ($resetTransaction == 'harian'){
                $orderCount = Order::whereDate('created_at', now())->count();
            }else{
                $orderCount = Order::whereMonth('created_at', now()->month())->whereYear('created_at', now()->year)->count();
            }

            $order = Order::create([
                'business_id'       => $user->business_id,
                'outlet_id'         => $outletId,
                'cashier_id'        => $user->id,
                'customer_name'     => $isDraft ? $request->customer_name : null,
                'customer_phone'    => $isDraft ? $request->customer_phone : null,
                'code'              => $prefixCode->value . now()->format('Ym') . '-' . strtoupper(Str::random(4)),
                'queue_number'      => $prefixOrder->value . str_pad($orderCount, 3, '0', STR_PAD_LEFT),
                'type'              => $request->type,
                'channel'           => $request->channel,
                'table_number'      => $request->table_number,
                'status'            => $orderStatus,
                'payment_status'    => $paymentStatus,
                'sub_total'         => $subTotal,
                'adjustment_total'  => $taxTotal + abs($rounding),
                'grand_total'       => $finalTotal,
                'note'              => $request->note,
                'opened_at'         => now(),
                'closed_at'         => $paymentStatus === 'PAID' ? now() : null,
            ]);

            // =========================
            // ITEMS
            // =========================
            foreach ($request->items as $item) {
                $menu = Menu::find($item['menu_id']);
                OrderItem::create([
                    'order_id'      => $order->id,
                    'menu_id'       => $item['menu_id'],
                    'name_snapshot' => $menus[$item['menu_id']]->name,
                    'qty'           => $item['qty'],
                    'hpp'           => $menu->calculateHppDynamic(),
                    'price'         => $menus[$item['menu_id']]->sell_price,
                    'subtotal'      => $menus[$item['menu_id']]->sell_price * $item['qty'],
                    'note'          => $item['note'] ?? null,
                ]);
            }

            // =========================
            // TAX & ROUNDING → ADJUSTMENT
            // =========================
            foreach ($taxes as $tax) {
                OrderAdjustment::create([
                    'order_id' => $order->id,
                    'type'     => 'TAX',
                    'name'     => $tax->name,
                    'method'   => $tax->calculation_type,
                    'value'    => $tax->value,
                    'amount'   => $tax->calculation_type === 'percent'
                        ? $subTotal * $tax->value / 100
                        : $tax->value,
                    'is_addition' => true,
                ]);
            }

            if ($rounding != 0) {
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

            // =========================
            // PAYMENT
            // =========================
            if ($paymentType === 'PAY') {
                $this->consumeStockFromOrder($order);

                Payment::create([
                    'payable_type'  => Order::class,
                    'payable_id'    => $order->id,
                    'cashier_id'    => auth()->id(),
                    'type'          => 'ORDER',
                    'method'        => $request->payment['method'],
                    'amount'        => $paidAmount,
                    'paid_at'       => now(),
                ]);
            }

            DB::commit();

            broadcast(new OrderCreated($order->load('items')));

            $this->printOrderKitchen($order);
            return response()->json([
                'success' => true,
                'order' => $order->load('items'),
                'change' => max(0, $paidAmount - $finalTotal),
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    protected function consumeStockFromOrder(Order $order)
    {
        // eager load biar gak N+1
        $order->load('items.menu.components.componentable');

        foreach ($order->items as $orderItem) {
            $this->consumeMenuRecursive($orderItem->menu, $orderItem->qty);
        }
    }

    protected function consumeMenuRecursive(Menu $menu, int $orderQty, array &$visited = [])
    {
        if (in_array($menu->id, $visited)) {
            throw new \Exception("Circular menu component detected: {$menu->name}");
        }

        $visited[] = $menu->id;

        $menu->loadMissing('components.componentable');

        foreach ($menu->components as $component) {
            $target = $component->componentable;
            if (!$target) continue;

            $needQty = $component->qty * $orderQty;

            if ($target instanceof Menu) {
                $this->consumeMenuRecursive($target, $needQty, $visited);
            } elseif ($target instanceof Recipe) {
                $this->consumeRecipe($target, $needQty);
            } elseif ($target instanceof Ingredient) {
                $this->consumeIngredient($target, $needQty, $menu);
            }
        }
    }

    protected function consumeRecipe(Recipe $recipe, int $orderQty)
    {
        $recipe->loadMissing('items.ingredient');

        foreach ($recipe->items as $item) {
            if (!$item->ingredient) continue;

            $needQty = $item->quantity * $orderQty;

            $this->consumeIngredient(
                $item->ingredient,
                $needQty,
                $recipe
            );
        }
    }

    protected function consumeIngredient(Ingredient $ingredient, float $needQty, $model)
    {
        $remaining = $needQty;

        $batches = IngredientBatch::where('ingredient_id', $ingredient->id)
            ->where('outlet_id', active_outlet_id())
            ->where('qty_remaining', '>', 0)
            ->orderBy('received_at') // FIFO
            ->lockForUpdate()
            ->get();

        foreach ($batches as $batch) {

            if ($remaining <= 0) break;

            $take = min($batch->qty_remaining, $remaining);

            $batch->decrement('qty_remaining', $take);

            StockMovement::create([
                'movementable_type' => get_class($model),
                'movementable_id'   => (int) $model->id,
                'business_id'       => auth()->user()->business_id ?? null,
                'ingredient_id'     => $ingredient->id,
                'batch_id'          => $batch->id,
                'outlet_id'         => active_outlet_id(),
                'code'              => uniqid('USE-'),
                'type'              => 'OUT',
                'qty'               => $take,
                'cost_per_unit'     => $batch->cost_per_unit,
                'user_id'           => auth()->id(),
            ]);

            $remaining -= $take;
        }

        // jika FIFO tidak cukup → buat negative batch
        if ($remaining > 0) {

            $negativeBatch = IngredientBatch::create([
                'code'          => uniqid('USE-'),
                'ingredient_id' => $ingredient->id,
                'outlet_id'     => active_outlet_id(),
                'qty_in'        => 0,
                'qty_remaining' => -$remaining,
                'cost_per_unit' => 0,
                'source'        => 'auto_negative',
                'received_at'   => now(),
            ]);

            StockMovement::create([
                'movementable_type' => get_class($model),
                'movementable_id'   => (int) $model->id,
                'business_id'       => auth()->user()->business_id ?? null,
                'ingredient_id'     => $ingredient->id,
                'batch_id'          => $negativeBatch->id,
                'outlet_id'         => active_outlet_id(),
                'code'              => uniqid('USE-'),
                'type'              => 'OUT',
                'qty'               => $remaining,
                'cost_per_unit'     => 0,
                'user_id'           => auth()->id(),
            ]);
        }
    }

    private function printOrderKitchen($order)
    {
        $servicePrinter = new PrinterService();

        $order->load('items.menu');

        $printers = Printer::where('outlet_id', active_outlet_id())
            ->where('is_active', 1)
            ->get();

        foreach ($printers as $printer) {

            // === printer kasir ===
            if ($printer->role === 'cashier') {

                $data = [
                    'role'                      => $printer->role,
                    'printer_connection_type'   => $printer->connection_type,
                    'printer_ip'                => $printer->ip_address,
                    'printer_port'              => $printer->port,
                    'order'                     => $order,
                    'items'                     => $order->items,
                ];

                $servicePrinter->print($data);
                continue;
            }

            // === printer dapur/bar ===
            $sections = json_decode($printer->section) ?? [];

            $items = $order->items->filter(function ($item) use ($sections) {
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
