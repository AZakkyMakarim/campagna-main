<?php

namespace Modules\Transaction\Http\Controllers;

use App\Events\OrderCreated;
use App\Http\Controllers\Controller;
use App\Jobs\PrintReceiptJob;
use App\Models\CashMovement;
use App\Models\Ingredient;
use App\Models\IngredientBatch;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderAdjustment;
use App\Models\OrderItem;
use App\Models\OrderType;
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

        $categoryOrder = [
            'jajan pasar',
            'roti',
            'keripik',
            'minuman',
            'wedangan',
            'paket nasi',
            'makanan',
            'jede sate',
            'jede bakmi',
        ];

        $outlet = Outlet::findOrFail($outletId);

        $raw = Menu::query()
            ->with('picture')
            ->where('outlet_id', $outletId)
            ->where('is_active', true);

        $categories = (clone $raw)
            ->pluck('category')
            ->unique()
            ->sortBy(function ($cat) use ($categoryOrder) {

                $index = array_search(strtolower($cat), $categoryOrder);

                return $index === false ? 999 : $index;

            })
            ->values();

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

        $orderTypes = OrderType::where('is_active', 1)->get();

        return view('transaction::order.index', compact('outlet','menus', 'printers', 'taxes', 'categories', 'orderTypes'));
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

                $doneQty = 0;
                $excluded = ['makanan', 'wedangan', 'minuman', 'jede sate', 'jede bakmi', 'paket nasi'];

                if (active_outlet_id() == 2) {
                    $excluded = ['makanan', 'wedangan', 'minuman', 'paket nasi'];
                }

                if (!in_array($menu->category, $excluded)) {
                    $doneQty = $item['qty'];
                }

                OrderItem::create([
                    'order_id'      => $order->id,
                    'menu_id'       => $item['menu_id'],
                    'name_snapshot' => $menus[$item['menu_id']]->name,
                    'qty'           => $item['qty'],
                    'done_qty'      => $doneQty,
                    'hpp'           => $menu->calculateHppDynamic(),
                    'price'         => $menus[$item['menu_id']]->sell_price,
                    'subtotal'      => $menus[$item['menu_id']]->sell_price * $item['qty'],
                    'note'          => $item['note'] ?? null,
                ]);
            }

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

                CashMovement::create([
                    'cashier_shift_id'   => active_shift()->id,
                    'outlet_id'          => active_outlet_id(),
                    'user_id'            => $user->id,
                    'type'               => 'IN',
                    'category'           => 'ORDER',
                    'amount'             => $paidAmount,
                    'description'        => 'Pembelian Produk',
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
                'printer_name'              => $printer->device_name,
                'order'                     => $order,
                'items'                     => $items,
            ];

            $servicePrinter->print($data);
        }
    }
}
