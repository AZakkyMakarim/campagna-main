<?php

namespace App\Http\Middleware;

use App\Models\CashierShift;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveShift
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user) {
            abort(401, 'Unauthorized');
        }

        $activeShift = CashierShift::where('user_id', $user->id)
            ->where('outlet_id', active_outlet_id())
            ->whereNull('closed_at')
            ->where('status', 'OPEN')
            ->first();

        if (!$activeShift) {
            // AJAX
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Shift belum dibuka atau sudah ditutup'
                ], 403);
            }

            toast('Silakan buka shift terlebih dahulu', 'warning');
            return redirect()->route('transaction.shift');
        }

        if (!session()->has('active_shift_id')) {
            session([
                'active_shift_id' => $activeShift->id
            ]);
        }

        return $next($request);
    }
}
