<?php

namespace Modules\Transaction\Http\Controllers;

use App\Events\OrderCreated;
use App\Http\Controllers\Controller;
use App\Jobs\PrintReceiptJob;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderAdjustment;
use App\Models\OrderItem;
use App\Models\Outlet;
use App\Models\Payment;
use App\Models\Printer;
use App\Models\Recipe;
use App\Models\Setting;
use App\Models\TaxRule;
use App\Services\PrinterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

        $categories = [
            'makanan' => 'Makanan',
            'minuman' => 'Minuman',
        ];

        // =========================
        // MENU LIST (READY JUAL)
        // =========================
        $menus = Menu::query()
            ->where('outlet_id', $outletId)
            ->where('is_active', true)
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
                'code'              => 'ORD-' . strtoupper(uniqid()),
                'queue_number'      => $prefixOrder->value . str_pad($orderCount, 3, '0', STR_PAD_LEFT),
                'type'              => $request->type,
                'channel'           => $request->channel,
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
//                $this->consumeStockFromOrder($order);

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

            $this->printOrderKitchenV2($order);
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
                $this->consumeIngredient($target, $needQty, $menu->name);
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
                $recipe->name
            );
        }
    }

    protected function consumeIngredient(\App\Models\Ingredient $ingredient, int|float $needQty, string $fromName)
    {
        // Lock row biar aman di concurrent order
        $ingredient = \App\Models\Ingredient::where('id', $ingredient->id)
            ->lockForUpdate()
            ->first();

        if ($ingredient->stock < $needQty) {
            throw new \Exception("Stok {$ingredient->name} tidak cukup untuk {$fromName}");
        }

        $ingredient->decrement('stock', $needQty);

        // Optional: simpan log pergerakan stok
        // StockMovement::create([...]);
    }

    private function printOrderKitchen($order){
        $printers = Printer::where('outlet_id', active_outlet_id())->where('role', 'kitchen')->get();

        foreach ($printers as $printer) {
            try {
                // kasih timeout biar ga nge-hang
                $connector = new NetworkPrintConnector($printer->ip_address, $printer->port, 5);
                $escpos = new \Mike42\Escpos\Printer($connector);

                $escpos->initialize();
                $escpos->setJustification(\Mike42\Escpos\Printer::JUSTIFY_CENTER);
                $escpos->setEmphasis(true);
                $escpos->setTextSize(2, 2);
                $escpos->text("KITCHEN ORDER\n");
                $escpos->setTextSize(1, 1);
                $escpos->setEmphasis(false);
                $escpos->text("--------------------------------\n");

                $escpos->setJustification(\Mike42\Escpos\Printer::JUSTIFY_LEFT);
                $escpos->text("ANTRIAN : " . $order->queue_number . "\n");
                $escpos->text("ORDER   : " . $order->code . "\n");
                if ($order->table_number) {
                    $escpos->text("MEJA    : " . $order->table_number . "\n");
                }
                $escpos->text("WAKTU   : " . now()->format('d/m/Y H:i') . "\n");
                $escpos->text("--------------------------------\n\n");

                foreach ($order->items as $item) {
                    $escpos->setEmphasis(true);
                    $escpos->setTextSize(2, 2);
                    $escpos->text($item->qty . "x " . strtoupper($item->name_snapshot) . "\n");
                    $escpos->setTextSize(1, 1);
                    $escpos->setEmphasis(false);

                    if (!empty($item->note)) {
                        $escpos->text("  - " . $item->note . "\n");
                    }

                    $escpos->feed(1);
                }

                if (!empty($order->note)) {
                    $escpos->text("--------------------------------\n");
                    $escpos->setEmphasis(true);
                    $escpos->text("CATATAN:\n");
                    $escpos->setEmphasis(false);
                    $escpos->text($order->note . "\n");
                }

                $escpos->cut();
                $escpos->close();

            } catch (\Throwable $e) {
                // ❗ Jangan bikin order gagal gara-gara printer
                Log::error("Kitchen printer failed: {$printer->ip_address}:{$printer->port}", [
                    'printer_id' => $printer->id,
                    'error' => $e->getMessage(),
                ]);

                // lanjut ke printer berikutnya
                continue;
            }
        }
    }

    private function printOrderKitchenV2($order){
        $servicePrinter = new PrinterService();
        $printers = Printer::where('outlet_id', active_outlet_id())->where('is_active', 1)->get();

        foreach ($printers as $printer) {
            $data = [
                'role'                      => $printer->role,
                'printer_connection_type'   => $printer->connection_type,
                'printer_ip'                => $printer->ip_address,
                'printer_port'              => $printer->port,
                'order'                     => $order,
            ];

            $servicePrinter->print($data);
        }
    }
}
