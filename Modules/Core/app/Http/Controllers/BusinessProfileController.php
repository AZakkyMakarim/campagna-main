<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BusinessProfile;
use App\Models\TaxRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BusinessProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $profile = Auth::user()->businessProfile;

        return view('core::profile.index', compact('profile'));
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
        $user = Auth::user();

        $profile = $user->business_id
            ? BusinessProfile::findOrFail($user->business_id)
            : new BusinessProfile();

        $profile->fill([
            'name'          => $request->name,
            'type'          => $request->type,
            'address'       => $request->address,
            'phone_number'  => $request->phone,
            'email'         => $request->email,
            'npwp'          => $request->npwp,
        ]);

        $profile->save();

        if ($request->file('logo')){
            insert_picture($request->logo, $profile, 'logo');
        }

        if (!$user->business_id) {
            $user->update(['business_id' => $profile->id]);
        }

        $this->generateTaxRule($profile->id);

        toast('Profil bisnis berhasil dibuat!');
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
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}

    public function generateTaxRule($businessId){
        TaxRule::updateOrCreate([
            'business_id'       => $businessId,
            'name'              => 'PPN',
        ],[
            'type'              => 'tax',
            'calculation_type'  => 'percentage',
            'value'             => 10,
        ]);

        TaxRule::updateOrCreate([
            'business_id'       => $businessId,
            'name'              => 'Service Charge',
        ],[
            'type'              => 'charge',
            'calculation_type'  => 'percentage',
            'value'             => 5,
        ]);
    }
}
