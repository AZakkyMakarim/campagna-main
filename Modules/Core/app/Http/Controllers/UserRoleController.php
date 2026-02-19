<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\Fluent\Concerns\Has;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserRoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::query()
            ->where('business_id', Auth::user()->business_id)
            ->latest()
            ->get();

        $roles = Role::query()
            ->where('business_id', Auth::user()->business_id)
            ->get();

        $outlets = Outlet::query()
            ->where('business_id', Auth::user()->business_id)
            ->latest()
            ->get();

        $permissions = Permission::all()
            ->groupBy(fn ($p) => explode('.', $p->name)[0])
            ->map(fn ($items) => $items->values());

        return view('core::user_role.index', compact('users', 'roles', 'outlets', 'permissions'));
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
    public function store(Request $request) {}

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
    public function update(User $user, Request $request) {
        $user->update([
            'name' => $request->name,
            'email' => $request->email
        ]);

        if (!empty($request->password)){
            $user->update([
                'password' => Hash::make($request->password)
            ]);
        }

        $user->outlets()->sync($request->outlet_ids);

        $role = Role::find($request->role);
        $user->syncRoles([$role]);

        toast('User berhasil diupdate!');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}
}
