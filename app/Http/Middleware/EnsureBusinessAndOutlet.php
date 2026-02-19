<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureBusinessAndOutlet
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // 🔴 WAJIB PUNYA BUSINESS
        if (!$user->businessProfile) {
            toast('Silakan lengkapi profil bisnis terlebih dahulu.', 'warning');
            return redirect()->route('core.business-profile');
        }

        $business = $user->businessProfile;

        // 🔴 WAJIB PUNYA MINIMAL 1 OUTLET
        if ($business->outlets()->count() === 0) {
            toast('Silakan buat minimal 1 outlet terlebih dahulu.', 'warning');
            return redirect()->route('core.outlet');
        }

        // 🔵 SET ACTIVE OUTLET KE SESSION
        if (!session()->has('active_outlet_id')) {
            session([
                'active_outlet_id' => $business->outlets()->orderBy('id')->value('id')
            ]);
        }

        return $next($request);
    }
}
