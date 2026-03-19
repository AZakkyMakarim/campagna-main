<?php

namespace Modules\Transaction\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ingredients = Ingredient::where('outlet_id', active_outlet_id())
            ->withSum('batches as stock', 'qty_remaining')
            ->withSum('batches as stock_value', DB::raw('qty_remaining * cost_per_unit'))
            ->latest()
            ->paginate(1000);

        return view('transaction::inventory.stock.index', compact('ingredients'));
    }

    public function card(Ingredient $ingredient)
    {
        $movements = StockMovement::where('ingredient_id', $ingredient->id)
            ->where('outlet_id', active_outlet_id())
            ->orderBy('created_at')
            ->get();

        $balance = 0;
        $rows = [];

        foreach ($movements as $m) {
            $opening = $balance;

            $in  = $m->type === 'IN'  ? $m->qty : 0;
            $out = $m->type === 'OUT' ? $m->qty : 0;

            $balance = $opening + $in - $out;

            $rows[] = [
                'date'              => parse_date_time($m->created_at),
                'opening'           => rp_format($opening),
                'in'                => number_format($in, 0,',', '.'),
                'out'               => number_format($out, 0,',', '.'),
                'closing'           => number_format($balance, 0,',', '.'),
                'type'              => $m->type,
                'cost_per_unit'     => rp_format($m->cost_per_unit),
                'code'              => $m->code ?? '',
                'total_price'       => rp_format($m->cost_per_unit * $m->qty),
                'updated_at'        => parse_date_time($m->updated_at),
                'pic'               => @$m->user->name ?? '',
//                'source'            => class_basename($m->movementable_type),
            ];
        }

        return response()->json([
            'ingredient' => $ingredient->name,
            'unit'       => $ingredient->baseUnit->symbol ?? '',
            'rows'       => $rows
        ]);
    }

    public function recap(Ingredient $ingredient)
    {
        $movements = StockMovement::where('ingredient_id', $ingredient->id)
            ->whereHas('batch', function ($query){
                $query->where('qty_remaining', '>', 0);
            })
            ->where('outlet_id', active_outlet_id())
            ->where('type', 'IN')
            ->orderBy('created_at')
            ->get();

        $balance = 0;
        $rows = [];

        foreach ($movements as $m) {
            $opening = $balance;

            $in  = $m->type === 'IN'  ? $m->qty : 0;
            $out = $m->type === 'OUT' ? $m->qty : 0;

            $balance = $opening + $in - $out;

            $rows[] = [
                'qty'               => number_format($m->batch->qty_remaining, 0, ',', '.'),
                'cost_per_unit'     => rp_format($m->cost_per_unit),
                'total_price'       => rp_format($m->batch->cost_per_unit * $m->batch->qty_remaining),
                'input_date'        => parse_date_time($m->created_at),
                'vendor'            => @$m->batch->vendor->name ?? 'Produksi',
                'pic'               => $m->user->name,
            ];
        }

        return response()->json([
            'ingredient' => $ingredient->name,
            'unit'       => $ingredient->baseUnit->name ?? '',
            'rows'       => $rows
        ]);
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
