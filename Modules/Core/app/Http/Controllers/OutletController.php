<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class OutletController extends Controller
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

        $outlets = Outlet::query()
            ->with(['settings'])
            ->where('business_id', Auth::user()->businessProfile->id)
            ->latest()
            ->paginate();

        return view('core::outlet.index', compact('outlets'));
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
                    Rule::unique('outlets', 'code')
                        ->where('business_id', $business->id),
                ],
                'name'          => 'required|string',
                'address'       => 'required|string',
                'opening_hours' => 'required',
                'closing_hours' => 'required',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            toast($e->validator->errors()->first(), 'error');
            return back();
        }

        $outlet = Outlet::create([
            'business_id'   => $business->id,
            'code'          => $request->code,
            'name'          => $request->name,
            'phone_number'  => $request->phone_number,
            'type'          => $request->type,
            'address'       => $request->address,
            'opening_hours' => $request->opening_hours,
            'closing_hours' => $request->closing_hours,
            'initial_cash'  => $request->initial_cash,
            'petty_cash'    => $request->petty_cash,
        ]);

        $this->initialSetting($outlet->id, $request);

        toast('Outlet berhasil ditambah!');
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
    public function update(Outlet $outlet, Request $request) {
        $outlet->update($request->all());

        if ($request->expectsJson()) {
            return api_status_ok($outlet);
        }

        $this->initialSetting($outlet->id, $request);

        toast('Outlet berhasil diubah!', 'success');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}

    public function initialSetting($outlet, $request){
        Setting::updateOrCreate([
            'outlet_id' => $outlet,
            'name'      =>  'prefix transaction',
        ],[
            'value'     => $request->prefix_transaction ?? 'TRX-'
        ]);

        Setting::updateOrCreate([
            'outlet_id' => $outlet,
            'name'      =>  'reset transaction',
        ],[
            'value'     => $request->reset_transaction ?? 'bulanan'
        ]);

        Setting::updateOrCreate([
            'outlet_id' => $outlet,
            'name'      =>  'prefix queue',
        ],[
            'value'     => $request->queue_number ?? 'ANT-'
        ]);
    }
}
