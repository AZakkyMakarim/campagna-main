<?php

namespace Modules\Management\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\IngredientBatch;
use App\Models\IngredientStock;
use App\Models\Purchase;
use App\Models\StockMovement;
use App\Models\Unit;
use App\Models\Vendor;
use App\Services\UnitConversionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $units = Unit::latest()->get();
        $purchases = Purchase::where('outlet_id', active_outlet_id())->latest()->paginate();
        $ingredients = Ingredient::where('outlet_id', active_outlet_id())
            ->with(['baseUnit', 'unitConversions.toUnit'])
            ->latest()
            ->get()
            ->groupBy(function ($item) {
                if ($item->type === 'raw') return 'Bahan Baku';
                if ($item->type === 'semi') return 'Bahan 1/2 Jadi';
                if ($item->type === 'finished') return 'Bahan Jadi';
                return 'Lainnya';
            });

        $ingredientPayload = [];
        $ingredientGroups  = [];

        foreach ($ingredients as $group => $items) {

            $ingredientGroups[$group] = [];

            foreach ($items as $i) {

                $ingredientGroups[$group][] = [
                    'id' => $i->id,
                    'name' => $i->name,
                    'base_unit' => [
                        'id' => $i->baseUnit->id,
                        'name' => $i->baseUnit->name,
                        'symbol' => $i->baseUnit->symbol,
                    ]
                ];

                $ingredientPayload[$i->id] = [
                    'name' => $i->name,
                    'base_unit' => [
                        'id' => $i->baseUnit->id,
                        'name' => $i->baseUnit->name,
                        'symbol' => $i->baseUnit->symbol,
                    ],
                    'conversions' => $i->unitConversions->map(function ($c) {
                        return [
                            'from_unit_id' => $c->from_unit_id,
                            'to_unit_id'   => $c->to_unit_id,
                            'multiplier'   => $c->multiplier,
                            'to_unit' => [
                                'id' => $c->toUnit->id,
                                'name' => $c->toUnit->name,
                                'symbol' => $c->toUnit->symbol,
                            ],
                        ];
                    })->values(),
                ];
            }
        }

        $vendors = Vendor::where('business_id', Auth::user()->business_id)
            ->where('is_active', 1)
            ->get();

        return view('management::purchasing.purchase.index', compact('ingredients', 'ingredientPayload', 'purchases', 'vendors', 'units', 'ingredientGroups'));
    }

    public function detail(Purchase $purchase){
        foreach ($purchase->ingredientBatches as $batch) {
            $rows[] = [
                'ingredient'       => $batch->ingredient->name,
                'ingredient_type'  => $batch->ingredient->type,
                'qty'              => number_format($batch->qty_in, 0, ',', '.'),
                'unit'             => $batch->ingredient->baseUnit->name,
                'cost_per_unit'    => rp_format($batch->cost_per_unit),
                'total_price'      => rp_format($batch->qty_in * $batch->cost_per_unit),
            ];
        }

        return response()->json([
            'code'          => $purchase->code,
            'created_by'    => @$purchase->createdBy->name,
            'purchased_at'  => parse_date_time($purchase->purchased_at),
            'vendor'        => @$purchase->vendor->name,
            'total_price'   => rp_format($purchase->total_cost),
            'description'   => $purchase->description,
            'nota'          => @$purchase->documentType('NOTA')->url,
            'rows'          => $rows
        ]);
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
        try {
            $conversion = new UnitConversionService();

            $code = uniqid('PUR-');
            $purchase = Purchase::create([
                'code'        => $code,
                'vendor_id'   => $request->vendor_id,
                'outlet_id'   => active_outlet_id(),
                'business_id' => auth()->user()->business_id,
                'total_cost'  => collect($request->items)->sum('cost'),
                'description' => $request->description,
                'purchased_at'=> now(),
                'created_by'  => auth()->id(),
            ]);

            if ($request->attachment){
                insert_document($request->attachment, $purchase, 'NOTA');
            }

            foreach ($request->items as $item){
                $ingredient = Ingredient::find($item['ingredient_id']);

                $qtyBase = $conversion->toBase($item['qty'], $item['unit_id'], $ingredient->base_unit_id, active_outlet_id());

                $costPerUnit = $item['cost'] / $qtyBase;

                $batch = IngredientBatch::create([
                    'code'          => $code,
                    'purchase_id'   => $purchase->id,
                    'vendor_id'     => $request->vendor_id,
                    'ingredient_id' => $ingredient->id,
                    'outlet_id'     => active_outlet_id(),
                    'qty_in'        => $qtyBase,
                    'qty_remaining' => $qtyBase,
                    'cost_per_unit' => $item['cost'] / $qtyBase,
                    'source'        => 'purchase',
                    'received_at'   => now(),
                ]);

                $stock = IngredientStock::firstOrCreate([
                    'ingredient_id' => $ingredient->id,
                    'outlet_id'     => active_outlet_id(),
                ],[
                    'business_id' => Auth::user()->business_id,
                    'qty'         => 0,
                    'avg_cost'    => 0
                ]);

                $newQty = $stock->qty + $qtyBase;

                // weighted average cost
                $newAvgCost = (
                        ($stock->qty * $stock->avg_cost) +
                        ($qtyBase * $costPerUnit)
                    ) / max($newQty, 1);

                $stock->update([
                    'qty' => $newQty,
                    'avg_cost' => $newAvgCost
                ]);

                StockMovement::create([
                    'movementable_type' => IngredientBatch::class,
                    'movementable_id'   => $batch->id, // atau purchase_id kalau ada
                    'business_id'       => auth()->user()->business_id,
                    'ingredient_id'     => $ingredient->id,
                    'batch_id'          => $batch->id,
                    'outlet_id'         => active_outlet_id(),
                    'code'              => $code,
                    'type'              => 'IN',
                    'qty'               => $qtyBase,
                    'cost_per_unit'     => $batch->cost_per_unit,
                    'user_id'           => Auth::user()->id,
                ]);
            }

            toast('Stok berhasil ditambah!');
            DB::commit();
        }catch (\Exception $exception){
            dd($exception);
            toast($exception);
            DB::rollBack();
        }

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
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}
}
