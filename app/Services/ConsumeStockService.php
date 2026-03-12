<?php

namespace App\Services;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\IngredientBatch;
use App\Models\IngredientStock;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Picture;
use App\Models\Recipe;
use App\Models\StockMovement;
use App\Models\UnitConversion;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Svg\Tag\Image;

class ConsumeStockService extends Controller
{
    public function consumeStockFromOrder(Order $order)
    {
        // eager load biar gak N+1
        $order->load('items.menu.components.componentable');

        foreach ($order->items as $orderItem) {
            $this->consumeMenuRecursive($orderItem->menu, $orderItem->qty);
        }
    }

    public function consumeMenuRecursive(Menu $menu, int $orderQty, array &$visited = [])
    {
        if (in_array($menu->id, $visited)) {
            throw new \Exception("Circular menu component detected: {$menu->name}");
        }

        $visited[] = $menu->id;

        $menu->loadMissing('components.componentable');

        foreach ($menu->components as $component) {
            $target = $component->componentable;
            if (!$target) continue;

            $needQty = $component->qty * $orderQty;

            if ($target instanceof Menu) {
                $this->consumeMenuRecursive($target, $needQty, $visited);
            } elseif ($target instanceof Recipe) {
                $this->consumeRecipe($target, $needQty);
            } elseif ($target instanceof Ingredient) {
                $this->consumeIngredient($target, $needQty, $menu);
            }
        }
    }

    public function consumeRecipe(Recipe $recipe, int $orderQty)
    {
        $recipe->loadMissing('items.ingredient');

        foreach ($recipe->items as $item) {
            if (!$item->ingredient) continue;

            $needQty = $item->quantity * $orderQty;

            $this->consumeIngredient(
                $item->ingredient,
                $needQty,
                $recipe
            );
        }
    }

    public function consumeIngredient(Ingredient $ingredient, float $needQty, $model)
    {
        $remaining = $needQty;

        $batches = IngredientBatch::where('ingredient_id', $ingredient->id)
            ->where('outlet_id', active_outlet_id())
            ->where('qty_remaining', '>', 0)
            ->orderBy('received_at') // FIFO
            ->lockForUpdate()
            ->get();

        foreach ($batches as $batch) {

            if ($remaining <= 0) break;

            $take = min($batch->qty_remaining, $remaining);

            $batch->decrement('qty_remaining', $take);

            StockMovement::create([
                'movementable_type' => get_class($model),
                'movementable_id'   => (int) $model->id,
                'business_id'       => auth()->user()->business_id ?? null,
                'ingredient_id'     => $ingredient->id,
                'batch_id'          => $batch->id,
                'outlet_id'         => active_outlet_id(),
                'code'              => uniqid('USE-'),
                'type'              => 'OUT',
                'qty'               => $take,
                'cost_per_unit'     => $batch->cost_per_unit,
                'user_id'           => auth()->id(),
            ]);

            $remaining -= $take;
        }

        // jika FIFO tidak cukup → buat negative batch
        if ($remaining > 0) {

            $negativeBatch = IngredientBatch::create([
                'code'          => uniqid('USE-'),
                'ingredient_id' => $ingredient->id,
                'outlet_id'     => active_outlet_id(),
                'qty_in'        => 0,
                'qty_remaining' => -$remaining,
                'cost_per_unit' => 0,
                'source'        => 'auto_negative',
                'received_at'   => now(),
            ]);

            StockMovement::create([
                'movementable_type' => get_class($model),
                'movementable_id'   => (int) $model->id,
                'business_id'       => auth()->user()->business_id ?? null,
                'ingredient_id'     => $ingredient->id,
                'batch_id'          => $negativeBatch->id,
                'outlet_id'         => active_outlet_id(),
                'code'              => uniqid('USE-'),
                'type'              => 'OUT',
                'qty'               => $remaining,
                'cost_per_unit'     => 0,
                'user_id'           => auth()->id(),
            ]);
        }
    }
}
