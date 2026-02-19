<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->businessProfile){
            toast('Bisnis belum diatur!', 'warning');
            return redirect()->route('core.business-profile');
        }

        $paymentMethods = PaymentMethod::query()
            ->where('business_id', Auth::user()->businessProfile->id)
            ->latest()
            ->paginate();

        return view('core::payment_method.index', compact('paymentMethods'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('core::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        $business = Auth::user()->businessProfile;

        if (!$business) {
            toast('Bisnis belum dibuat!', 'warning');
            return back();
        }

        try {
            $request->validate([
                'name'          => 'required|string',
                'type'          => 'required|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            toast($e->validator->errors()->first(), 'error');
            return back();
        }

        PaymentMethod::create([
            'business_id'   => $business->id,
            'name'          => $request->name,
            'type'          => $request->type
        ]);

        toast('Metode pembayaran berhasil ditambah!');
        return back();
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('core::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('core::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PaymentMethod $paymentMethod, Request $request) {
        $paymentMethod->update($request->all());

        if ($request->expectsJson()) {
            return api_status_ok($paymentMethod);
        }

        toast('Metode pembayaran berhasil diubah!');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}
}
