<?php

namespace Modules\Transaction\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CashierShift;
use App\Models\CashMovement;
use App\Models\Outlet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CashierShiftController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $outlet = Outlet::find(active_outlet_id());

        $rawCashierShift = CashierShift::where('outlet_id', $outlet->id)
            ->where('user_id', \auth()->user()->id);

        $cashierShift = (clone $rawCashierShift)->where('status', 'OPEN')->first();
        $histories = (clone $rawCashierShift)->whereDate('opened_at', now())->get();

        $cashIn = collect();
        $cashOut = collect();
        $pettyCash = collect();
        if ($cashierShift){
            $rawCash = CashMovement::where('cashier_shift_id', $cashierShift->id)
                ->where('outlet_id', active_outlet_id())
                ->where('user_id', \auth()->user()->id);

            $cashOut = (clone $rawCash)
                ->where('type', 'OUT')
                ->get();

            $cashIn = (clone $rawCash)
                ->whereIn('category', ['ORDER'])
                ->get();

            $pettyCash = (clone $rawCash)
                ->where('type', 'OUT')
                ->where('category', 'PETTY_CASH')
                ->get();
        }

        return view('transaction::shift.index', compact('outlet', 'cashierShift', 'cashIn', 'cashOut', 'pettyCash', 'histories'));
    }

    public function open()
    {
        $user = auth()->user();
        $outlet = Outlet::find(active_outlet_id());

        DB::beginTransaction();
        try {
            $activeShift = CashierShift::where('outlet_id', $outlet->id)
                ->where('user_id', $user->id)
                ->where('status', 'OPEN')
                ->first();

            if ($activeShift) {
                toast('Masih ada shift aktif. Tutup shift terlebih dahulu!', 'warning');
                return back();
            }

            $shift = CashierShift::create([
                'shift_code'          => uniqid(),
                'business_id'         => $user->business_id,
                'outlet_id'           => $outlet->id,
                'user_id'             => $user->id,
                'opened_at'           => now(),
                'opening_cash'        => $outlet->initial_cash,
                'opening_petty_cash'  => $outlet->petty_cash,
                'expected_cash'       => $outlet->initial_cash,
                'expected_petty_cash' => $outlet->petty_cash,
                'status'              => 'OPEN',
            ]);

            // 💰 CATAT CHANGE FUND
            CashMovement::create([
                'cashier_shift_id'   => $shift->id,
                'outlet_id'          => $outlet->id,
                'user_id'            => $user->id,
                'type'               => 'IN',
                'category'           => 'CHANGE_FUND',
                'amount'             => $outlet->initial_cash,
                'description'        => 'Modal awal shift',
            ]);

            // 💰 CATAT PETTY CASH AWAL
            CashMovement::create([
                'cashier_shift_id'   => $shift->id,
                'outlet_id'          => $outlet->id,
                'user_id'            => $user->id,
                'type'               => 'IN',
                'category'           => 'PETTY_CASH',
                'amount'             => $outlet->petty_cash,
                'description'        => 'Petty cash awal shift',
            ]);

            DB::commit();

            toast('Shift berhasil dibuka');
            return back();

        } catch (\Throwable $e) {
            DB::rollBack();

            toast('Gagal membuka shift', 'error');
            return back();
        }
    }

    public function close(Request $request, CashierShift $shift)
    {
        // 🔒 Pastikan shift masih OPEN
        if ($shift->status !== 'OPEN') {
            toast('Shift sudah ditutup', 'warning');
            return back();
        }

        $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        DB::beginTransaction();

        try {
            $amount      = (float) $request->amount;
            $nextModal   = (float) $shift->initial_cash;

            // 🔥 HITUNG
            $deposit        = max($amount - $nextModal, 0);

            $expectedCash = CashMovement::where('cashier_shift_id', $shift->id)
                ->where('type', 'IN')
                ->where('category', 'CHANGE_FUND')
                ->sum('amount');

            $actualPettyCash = CashMovement::where('cashier_shift_id', $shift->id)
                ->where('category', 'PETTY_CASH')
                ->selectRaw("COALESCE(SUM(CASE WHEN type='IN' THEN amount ELSE -amount END), 0) as bal")
                ->value('bal');

            $difference     = $amount - $expectedCash;

            // 🔥 UPDATE SHIFT
            $shift->update([
                'expected_cash'         => $expectedCash,
                'actual_cash'           => $amount,
                'cash_difference'       => $difference,
                'actual_petty_cash'     => $actualPettyCash,
                'expected_petty_cash'   => $actualPettyCash,
                'petty_cash_difference' => $shift->opening_petty_cash - $actualPettyCash,
                'ho_deposit'            => $deposit,
                'closed_at'             => now(),
                'status'                => 'CLOSED',
                'note'                  => $request->description,
            ]);

            // 🔥 CATAT SETORAN HO (kalau ada)
            if ($deposit > 0) {
                CashMovement::create([
                    'user_id'          => \auth()->user()->id,
                    'outlet_id'        => active_outlet_id(),
                    'cashier_shift_id' => $shift->id,
                    'type'             => 'HO_DEPOSIT',
                    'category'         => 'HO_DEPOSIT',
                    'amount'           => $deposit,
                    'description'      => 'Setoran HO saat tutup shift',
                ]);
            }

            // 🔥 CATAT SELISIH (audit)
            if ($difference != 0) {
                CashMovement::create([
                    'user_id'          => \auth()->user()->id,
                    'outlet_id'        => active_outlet_id(),
                    'cashier_shift_id' => $shift->id,
                    'type'             => $difference > 0 ? 'IN' : 'OUT',
                    'category'         => 'DIFFERENCE',
                    'amount'           => abs($difference),
                    'description'      => $difference > 0 ? 'Kelebihan kas' : 'Kekurangan kas',
                ]);
            }

            DB::commit();

            toast('Shift berhasil ditutup');
            return back();

        } catch (\Throwable $e) {
            DB::rollBack();

            dd($e);

            toast('Gagal menutup shift', 'error');
            return back();
        }
    }

    public function pettyCashOut(Request $request)
    {
        $request->validate([
            'amount'      => 'required|numeric|min:1',
            'description' => 'required|string|max:255',
        ]);

        $user = auth()->user();
        $outletId = active_outlet_id();

        DB::beginTransaction();
        try {
            $shift = CashierShift::where('outlet_id', $outletId)
                ->where('user_id', $user->id)
                ->where('status', 'OPEN')
                ->firstOrFail();

            if ($request->amount > $shift->expected_petty_cash) {
                toast('Saldo petty cash tidak mencukupi', 'warning');
                return back();
            }

            CashMovement::create([
                'cashier_shift_id'    => $shift->id,
                'outlet_id'           => $outletId,
                'user_id'             => $user->id,
                'type'                => 'OUT',
                'category'            => 'PETTY_CASH',
                'amount'              => $request->amount,
                'description'         => $request->description,
            ]);

            // 🔻 UPDATE EXPECTED PETTY CASH
            $shift->decrement('expected_petty_cash', $request->amount);

            DB::commit();

            toast('Pengeluaran petty cash berhasil dicatat', 'success');
            return back();

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            toast('Gagal mencatat pengeluaran petty cash', 'error');
            return back();
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('transaction::create');
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
        return view('transaction::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('transaction::edit');
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
