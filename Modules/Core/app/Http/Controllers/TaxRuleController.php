<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\TaxRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaxRuleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tax = TaxRule::query()
            ->where('business_id', Auth::user()->business_id)
            ->get();

        $ppn = $tax->where('name', 'PPN')->first();
        $service = $tax->where('name', 'Service Charge')->first();

        return view('core::tax_rule.index', compact('ppn', 'service'));
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
        $businessId = auth()->user()->business_id;

        $request->validate([
            'ppn_percentage'     => 'nullable|numeric|min:0|max:100',
            'service_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        TaxRule::updateOrCreate(
            [
                'business_id' => $businessId,
                'name'        => 'PPN',
            ],
            [
                'type'              => 'tax',
                'calculation_type'  => 'percent',
                'value'             => $request->ppn_percentage ?? 0,
                'is_active'         => $request->has('enable_ppn'),
            ]
        );

        $tax = TaxRule::updateOrCreate(
            [
                'business_id' => $businessId,
                'name'        => 'Service Charge',
            ],
            [
                'type'              => 'charge',
                'calculation_type'  => 'percent',
                'value'             => $request->service_percentage ?? 0,
                'is_active'         => $request->has('enable_service'),
            ]
        );

        return api_status_ok($tax, 'Pajak berhasil diubah!');
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
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}
}
