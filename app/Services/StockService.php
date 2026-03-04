<?php

namespace App\Services;

use App\Http\Controllers\Controller;
use App\Models\IngredientBatch;
use App\Models\IngredientStock;
use App\Models\Picture;
use App\Models\StockMovement;
use App\Models\UnitConversion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Svg\Tag\Image;

class StockService extends Controller
{
    /**
     * Pastikan stok cukup (pakai snapshot, bukan sum ledger)
     */
    public function assertStockEnough(int $ingredientId, int $outletId, float $requiredQty): void
    {
        $stock = IngredientStock::where('ingredient_id', $ingredientId)
            ->where('outlet_id', $outletId)
            ->lockForUpdate()
            ->first();

        $available = $stock?->qty ?? 0;

        if ($available < $requiredQty) {
            throw new \RuntimeException("Stok tidak cukup untuk ingredient ID {$ingredientId} - {$stock->ingredient->name}. Tersedia: {$available}");
        }
    }

    /**
     * Consume stok pakai FIFO + catat stock_movements (OUT)
     * Return: total cost yang terpakai
     */
    public function consumeFifo($ingredientId, $qtyNeeded, $outletId, $movementableType, $movementableId)
    {
        $service = app(StockService::class);

        $remaining = (float) $qtyNeeded;
        $totalCost = 0;

        $batches = IngredientBatch::where('ingredient_id', $ingredientId)
            ->where('outlet_id', $outletId)
            ->where('qty_remaining', '>', 0)
            ->orderBy('received_at', 'asc')
            ->lockForUpdate()
            ->get();

        foreach ($batches as $batch) {
            if ($remaining <= 0) break;

            $take = min($remaining, (float) $batch->qty_remaining);

            // kurangi batch
            $batch->qty_remaining -= $take;
            $batch->save();

            $cost = $take * (float) $batch->cost_per_unit;
            $totalCost += $cost;

            // OUT movement per batch
            $service->applyMovement([
                'movementable_type' => $movementableType,
                'movementable_id'   => $movementableId,
                'business_id'       => auth()->user()->business_id,
                'ingredient_id'     => $ingredientId,
                'batch_id'          => $batch->id,
                'outlet_id'         => $outletId,
                'code'              => uniqid('OUT-'),
                'type'              => 'OUT',
                'qty'               => $take,
                'cost_per_unit'     => $batch->cost_per_unit,
                'user_id'           => auth()->id(),
            ]);

            $remaining -= $take;
        }

        if ($remaining > 0) {
            throw new \Exception("Stok bahan tidak cukup untuk ingredient_id={$ingredientId}");
        }

        return $totalCost; // buat hitung HPP produksi
    }

    /**
     * Tambah stok ke snapshot
     */
    public function increaseStock(int $ingredientId, int $outletId, float $qty): void
    {
        $stock = IngredientStock::where('ingredient_id', $ingredientId)
            ->where('outlet_id', $outletId)
            ->lockForUpdate()
            ->first();

        if (!$stock) {
            IngredientStock::create([
                'ingredient_id' => $ingredientId,
                'outlet_id'     => $outletId,
                'qty'           => $qty,
            ]);
        } else {
            $stock->qty += $qty;
            $stock->save();
        }
    }

    /**
     * Kurangi stok snapshot
     */
    public function decreaseStock(int $ingredientId, int $outletId, float $qty): void
    {
        $stock = IngredientStock::where('ingredient_id', $ingredientId)
            ->where('outlet_id', $outletId)
            ->lockForUpdate()
            ->first();

        if (!$stock || $stock->qty < $qty) {
            throw new RuntimeException("Snapshot stok tidak cukup untuk ingredient ID {$ingredientId}");
        }

        $stock->qty -= $qty;
        $stock->save();
    }

    /**
     * Untuk adjustment SO / manual (plus / minus)
     */
    public function adjustStock(
        int $ingredientId,
        int $outletId,
        float $qty,
        string $type, // IN | OUT
        string $movementableType,
        int $movementableId,
        float $costPerUnit = 0
    ): void {
        if ($type === 'IN') {
            $this->increaseStock($ingredientId, $outletId, $qty);
        } else {
            $this->decreaseStock($ingredientId, $outletId, $qty);
        }

        StockMovement::create([
            'movementable_type' => $movementableType,
            'movementable_id'   => $movementableId,
            'business_id'       => auth()->user()->business_id,
            'ingredient_id'     => $ingredientId,
            'batch_id'          => null,
            'outlet_id'         => $outletId,
            'code'              => uniqid('ADJ-', false),
            'type'              => $type,
            'qty'               => $qty,
            'cost_per_unit'     => $costPerUnit,
            'user_id'           => auth()->id(),
        ]);
    }

    /**
     * Core function: apply movement (IN / OUT / ADJ)
     * Semua update snapshot + ledger lewat sini
     */
    public function applyMovement(array $data)
    {
        return DB::transaction(function () use ($data) {

            $stock = IngredientStock::firstOrCreate(
                [
                    'ingredient_id' => $data['ingredient_id'],
                    'outlet_id'     => $data['outlet_id'],
                ],
                [
                    'qty'      => 0,
                    'avg_cost' => 0,
                ]
            );

            $qty  = (float) $data['qty'];
            $cost = (float) ($data['cost_per_unit'] ?? 0);

            if ($data['type'] === 'IN') {

                $oldQty = (float) $stock->qty;
                $oldAvg = (float) $stock->avg_cost;

                $newQty = $oldQty + $qty;

                if ($newQty > 0) {
                    $newAvg = (($oldQty * $oldAvg) + ($qty * $cost)) / $newQty;
                } else {
                    $newAvg = 0;
                }

                $stock->qty = $newQty;
                $stock->avg_cost = $newAvg;
            } else { // OUT
                $stock->qty = max(0, (float) $stock->qty - $qty);
                // avg_cost TIDAK diubah
            }

            $stock->save();

            // simpan ledger movement
            StockMovement::create($data);

            return $stock;
        });
    }

    /**
     * Shortcut helpers biar enak dipakai
     */
    public function stockIn(array $data): StockMovement
    {
        $data['type'] = 'IN';
        return $this->applyMovement($data);
    }

    public function stockOut(array $data): StockMovement
    {
        $data['type'] = 'OUT';
        return $this->applyMovement($data);
    }

    public function stockAdjust(array $data): StockMovement
    {
        $data['type'] = 'ADJUST';
        return $this->applyMovement($data);
    }
}
