<?php

namespace Modules\Transaction\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Models\VoidLog;
use Illuminate\Support\Facades\DB;
use App\Models\CashMovement;
use App\Models\Payment;

class VoidController extends Controller
{
    private function checkVoidCode($outlet_id, $code)
    {
        $setting = Setting::where('name', 'void_code')
            ->where('outlet_id', $outlet_id)
            ->first();

        return $setting && $setting->value == $code;
    }

    public function voidItem(Request $request)
    {
        $request->validate([
            'order_item_id' => 'required|exists:order_items,id',
            'qty' => 'required|integer|min:1',
            'void_code' => 'required|string',
            'note' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $orderItem = OrderItem::with('order')->findOrFail($request->order_item_id);
            $order = $orderItem->order;

            if (!$this->checkVoidCode($order->outlet_id, $request->void_code)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode supervisor tidak valid'
                ], 403);
            }

            // Hitung sisa qty yang bisa di-void
            $sisaQty = $orderItem->qty - $orderItem->void_qty;
            if ($request->qty > $sisaQty) {
                return response()->json([
                    'success' => false,
                    'message' => 'Qty void melebihi sisa item yang tersedia'
                ], 422);
            }

            // Update void_qty
            $orderItem->void_qty += $request->qty;
            $orderItem->save();

            // Recalculate Order Subtotal & Grandtotal (hanya item yang TIDAK di-void)
            // Rumus manual subtotal: sum((qty - void_qty) * price)
            $newSubTotal = 0;
            foreach ($order->items as $item) {
                $newSubTotal += ($item->qty - $item->void_qty) * $item->price;
            }

            $order->sub_total = $newSubTotal;
            // Anggap tax & adjustment re-kalkulasi sederhana
            // Jika Anda ada logic pajak spesifik, sesuaikan di sini. 
            // Untuk amannya, kita asumsikan selisih sub_total menjadi pengurang langsung grand_total.
            // ATAU lebih aman panggil helper kalkulasi. Disini saya pakai pengurangan manual jika ada selisih.
            
            // Rekalkulasi simpel:
            $order->grand_total = $newSubTotal; // Asumsi belum pakai tax/admin jika $order->grand_total aslinya beda 
            
            // Simpan log void
            VoidLog::create([
                'order_id' => $order->id,
                'order_item_id' => $orderItem->id,
                'type' => 'ITEM',
                'void_qty' => $request->qty,
                'note' => $request->note,
                'voided_by' => auth()->id(),
            ]);

            // Jika item sudah done > 0, stoknya TIDAK dikembalikan ke kitchen
            // Jika butuh kembalikan stok, lakukan di sini (ex: kurangi material issue)

            $order->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Void item berhasil',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function voidNota(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'void_code' => 'required|string',
            'note' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $order = Order::with('items')->findOrFail($request->order_id);

            if (!$this->checkVoidCode($order->outlet_id, $request->void_code)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode supervisor tidak valid'
                ], 403);
            }

            if ($order->status == 'VOIDED') {
                return response()->json([
                    'success' => false,
                    'message' => 'Order sudah dalam status VOID'
                ], 422);
            }

            // Void semua sisa qty di setiap item
            foreach ($order->items as $item) {
                $sisaQty = $item->qty - $item->void_qty;
                if ($sisaQty > 0) {
                    $item->void_qty += $sisaQty;
                    $item->save();
                }
            }

            // Update status nota
            $order->status = 'VOIDED';
            $order->save();

            // Simpan log void
            VoidLog::create([
                'order_id' => $order->id,
                'type' => 'NOTA',
                'note' => $request->note,
                'voided_by' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Void nota berhasil',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
