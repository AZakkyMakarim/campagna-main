<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
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
        $businessId = Auth::user()->business_id;

        if (!$businessId) {
            toast('Business belum diatur', 'warning');
            return back();
        }

        Role::updateOrCreate(
            [
                'business_id' => $businessId,
                'name' => $request->name,
            ],
            [
                'description' => $request->description,
                'guard_name'  => 'web',
            ]
        );

        toast('Role berhasil dibuat!');
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
    public function updatePermission(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'array'
        ]);

        if ($role->business_id !== auth()->user()->business_id) {
            return response([
                'code' => 403,
                'message' => 'Unauthorized'
            ], 403);
        }

        $role->syncPermissions($request->permissions);

        return response([
            'code' => 200,
            'message' => 'Permission berhasil disimpan',
            'payload' => $role->permissions->pluck('name')
        ], 200);
    }

    public function getRolePermission(Role $role){
        return response()->json([
            'payload' => $role->permissions->pluck('name')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}
}
