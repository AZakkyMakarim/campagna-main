<?php

namespace Modules\Transaction\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RerservationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reservations = Reservation::where('outlet_id', active_outlet_id())
            ->latest()
            ->get();

        return view('transaction::reservation.index', compact('reservations'));
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
    public function store(Request $request) {
        $reservation = Reservation::create([
            'outlet_id'     => active_outlet_id(),
            'code'          => uniqid('RSV-'),
            'customer_name' => $request->name,
            'phone'         => $request->phone,
            'reserved_at'   => Carbon::createFromFormat('Y-m-d H:i', $request->date.' '.$request->time),
//            'table'         =>,
            'status'        => 'RESERVED',
            'note'          => $request->note,
        ]);

        Payment::create([
            'payable_type'  => Reservation::class,
            'payable_id'    => $reservation->id,
            'cashier_id'    => auth()->id(),
            'type'          => 'DP',
            'method'        => $request->pay_method,
            'amount'        => $request->pay_amount,
            'paid_at'       => now(),
        ]);

//        Payment::create([
//            'order_id'   => $order->id,
//            'cashier_id' => $user->id,
//            'method'     => $request->payment['method'],
//            'amount'     => $paidAmount,
//            'paid_at'    => now(),
//        ]);

        toast('Reservasi berhasil dibuat!');
        return back();
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
}
