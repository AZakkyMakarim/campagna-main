<?php

namespace Modules\Management\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\Vendor;
use App\Models\VendorIngredient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VendorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vendors = Vendor::where('business_id', Auth::user()->business_id)->latest()->paginate();

        $ingredients = Ingredient::where('outlet_id', active_outlet_id())->latest()->get();

        return view('management::purchasing.vendor.index', compact('vendors', 'ingredients'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('management::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        DB::beginTransaction();
        $vendor = Vendor::create([
            'business_id'   => Auth::user()->business_id,
            'name'          => $request->name,
            'phone_number'  => $request->phone_number,
            'address'       => $request->address,
            'link_maps'     => $request->link_maps,
        ]);

        foreach (json_decode($request->components) as $component){
            VendorIngredient::create([
                'vendor_id'     => $vendor->id,
                'outlet_id'     => active_outlet_id(),
                'ingredient_id' => $component->ingredient_id,
            ]);
        }

        DB::commit();

        toast('Vendor berhasil dimasukan!');
        return back();
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('management::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('management::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vendor $vendor) {
        $vendor->update($request->all());

        $vendor->vendorIngredients()->delete();

        $components = json_decode($request->components, true) ?? [];

        foreach ($components as $component) {
            VendorIngredient::create([
                'vendor_id'     => $vendor->id,
                'ingredient_id' => $component['ingredient_id'],
            ]);
        }

        if ($request->expectsJson()) {
            return api_status_ok($vendor);
        }

        toast('Vendor berhasil diubah!');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}
}
