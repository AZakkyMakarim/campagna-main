<?php

namespace Modules\Transaction\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ListOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = Order::with(['items'])->where('outlet_id', active_outlet_id())->get();

        return view('transaction::list_order.index', compact('orders'));
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

    public function pay(Request $request)
    {
        DB::beginTransaction();

        try {
            $order = Order::with('payments')->findOrFail($request->order_id);

            $totalPaid = $order->payments()->sum('amount');
            $remaining = $order->grand_total - $totalPaid;

            if ($request->amount <= 0) {
                throw new \Exception('Nominal tidak valid');
            }

            if ($request->amount > $remaining) {
                throw new \Exception('Nominal melebihi sisa tagihan');
            }

            Payment::create([
                'payable_type'  => Order::class,
                'payable_id'    => $order->id,
                'cashier_id'    => auth()->id(),
                'type'          => 'ORDER',
                'method'        => $request->method,
                'amount'        => $request->amount,
                'paid_at'       => now(),
            ]);

            $newTotalPaid = $totalPaid + $request->amount;

            if ($newTotalPaid >= $order->grand_total) {
                $order->update([
                    'payment_status'    => 'PAID',
                    'status'            => 'COMPLETED',
                    'closed_at'         => now(),
                ]);
            } else {
                $order->update([
                    'payment_status'    => 'PARTIAL',
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'change' => max(0, $request->amount - $remaining),
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
