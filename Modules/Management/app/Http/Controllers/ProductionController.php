<?php

namespace Modules\Management\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\IngredientBatch;
use App\Models\Recipe;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $batchs = IngredientBatch::where('outlet_id', active_outlet_id())->where('source', 'production')->latest()->get();

        $ingredients = Ingredient::with([
                'baseUnit',
                'recipe.items.ingredient.batches',
                'recipe.items.unit',
                'recipe.items.ingredient.stock',
            ])
            ->where('type', 'semi')
            ->whereHas('recipe')
            ->get();

        return view('management::purchasing.production.index', compact('batchs', 'ingredients'));
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
    public function store(Request $request)
    {
        $stockService = new StockService();

        DB::beginTransaction();

        try {
            $recipeIngredient = Ingredient::findOrFail($request->ingredient_id);
            $recipe = $recipeIngredient->recipe;

            // 1️⃣ VALIDASI: cek stok cukup dulu
            foreach (json_decode($request->components) as $item) {
                $stockService->assertStockEnough(
                    $item->ingredient_id,
                    active_outlet_id(),
                    $item->qty
                );
            }

            $totalCost = 0;

            // 2️⃣ CONSUME FIFO
            foreach (json_decode($request->components) as $item) {
                $totalCost += $stockService->consumeFifo(
                    $item->ingredient_id,
                    $item->qty,
                    active_outlet_id(),
                    Recipe::class,
                    $recipe->id
                );
            }

            // 3️⃣ HITUNG HASIL PRODUKSI
            $producedQty = $request->production_qty * $recipe->quantity;

            $costPerUnit = $producedQty > 0
                ? $totalCost / $producedQty
                : 0;

            // 4️⃣ BUAT BATCH BARU (HASIL PRODUKSI)
            $batch = IngredientBatch::create([
                'outlet_id'     => active_outlet_id(),
                'ingredient_id' => $recipe->ingredient_id,
                'qty_in'        => $producedQty,
                'qty_remaining' => $producedQty,
                'cost_per_unit' => $costPerUnit,
                'source'        => 'production',
                'received_at'   => now(),
            ]);

            // 5️⃣ CATAT STOCK MOVEMENT (IN)
            $stockService->applyMovement([
                'movementable_type' => Recipe::class,
                'movementable_id'   => $recipe->id,
                'business_id'       => auth()->user()->business_id,
                'ingredient_id'     => $recipe->ingredient_id,
                'batch_id'          => $batch->id,
                'outlet_id'         => active_outlet_id(),
                'code'              => uniqid('PRO-'),
                'type'              => 'IN',
                'qty'               => $producedQty,
                'cost_per_unit'     => $costPerUnit,
                'user_id'           => auth()->id(),
            ]);

            // 6️⃣ UPDATE SNAPSHOT STOCK (+)
            $stockService->increaseStock(
                ingredientId: $recipe->ingredient_id,
                outletId: active_outlet_id(),
                qty: $producedQty
            );

            DB::commit();
            toast('Resep berhasil diproduksi!');
        } catch (\Throwable $e) {
            DB::rollBack();
            toast($e->getMessage(), 'warning');
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

    public function consumeFifo(
        int $ingredientId,
        float $qtyNeeded,
        int $outletId,
        string $refType,
        int $refId
    ): float {

        if ($qtyNeeded <= 0) return 0;

        return DB::transaction(function () use (
            $ingredientId,
            $qtyNeeded,
            $outletId,
            $refType,
            $refId
        ) {

            $ingredient = Ingredient::findOrFail($ingredientId);

            // ===============================
            // UNLIMITED STOCK
            // ===============================
            if ($ingredient->is_unlimited_stock) {
                StockMovement::create([
                    'movementable_type' => $refType,
                    'movementable_id'   => $refId,
                    'business_id'       => Auth::user()->business_id,
                    'ingredient_id'     => $ingredientId,
                    'batch_id'          => null,
                    'outlet_id'         => $outletId,
                    'code'              => uniqid('PRO-', false),
                    'type'              => 'OUT',
                    'qty'               => $qtyNeeded,
                    'cost_per_unit'     => 0,
                    'user_id'           => Auth::user()->id,
                ]);

                return 0;
            }

            // ===============================
            // FIFO LOGIC
            // ===============================
            $remaining = $qtyNeeded;
            $totalCost = 0;

            $batches = IngredientBatch::where('ingredient_id', $ingredientId)
                ->where('outlet_id', $outletId)
                ->where('qty_remaining', '>', 0)
                ->orderBy('received_at')
                ->lockForUpdate()
                ->get();

            $i = 1;
            foreach ($batches as $batch) {

                if ($remaining <= 0) {
                    break; // kebutuhan sudah terpenuhi
                }

                $take = min($remaining, $batch->qty_remaining);

                $batch->decrement('qty_remaining', $take);

                $cost = $take * $batch->cost_per_unit;
                $totalCost += $cost;

//                if ($i == 2){
//                    dd($batch, $batch->cost_per_unit, $cost, $totalCost);
//                }

                StockMovement::create([
                    'movementable_type' => $refType,
                    'movementable_id'   => $refId,
                    'business_id'       => Auth::user()->business_id,
                    'ingredient_id'     => $ingredientId,
                    'batch_id'          => $batch->id,
                    'outlet_id'         => $outletId,
                    'code'              => uniqid('PRO-', false),
                    'type'              => 'OUT',
                    'qty'               => $take,
                    'cost_per_unit'     => $batch->cost_per_unit,
                    'user_id'           => Auth::user()->id,
                ]);

                $remaining -= $take;
                $i++;
            }

            // ===============================
            // FINAL CHECK
            // ===============================
            if ($remaining > 0) {
                throw new \Exception('Stok tidak mencukupi');
            }

            return $totalCost;
        });
    }
}
