<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('core::index');
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
                'email'         => 'required|email',
                'password'      => 'required',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            toast($e->validator->errors()->first(), 'error');
            return back();
        }

        User::create([
            'business_id'   => $business->id,
            'name'          => $request->name,
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
        ]);

        toast('User berhasil ditambah!');
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
}
