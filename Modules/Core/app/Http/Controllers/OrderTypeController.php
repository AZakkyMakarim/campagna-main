<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\OrderType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class OrderTypeController extends Controller
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

        $orderTypes = OrderType::query()
            ->where('business_id', Auth::user()->business_id)
            ->latest()
            ->paginate();

        return view('core::order_type.index', compact('orderTypes'));
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
                'code' => [
                    'required',
                    Rule::unique('order_types', 'code')
                        ->where('business_id', $business->id),
                ],
                'name'          => 'required|string',
                'type'          => 'required|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            toast($e->validator->errors()->first(), 'error');
            return back();
        }

        OrderType::create([
            'business_id'   => $business->id,
            'code'          => $request->code,
            'name'          => $request->name,
            'type'          => $request->type,
            'description'   => $request->description,
        ]);

        toast('Jenis order berhasil ditambah!');
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
    public function update(OrderType $orderType, Request $request) {
        $orderType->update($request->all());

        if ($request->expectsJson()) {
            return api_status_ok($orderType);
        }

        toast('Jenis order berhasil diubah!', 'success');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}
}
