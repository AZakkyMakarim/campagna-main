<?php

namespace App\Models;

use App\Traits\WithPictures;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use WithPictures;

    protected $fillable = [
        'business_id',
        'outlet_id',
        'name',
        'sku',
        'category',
        'type',
        'sell_price',
        'hpp',
        'is_active',
    ];

    public function calculateHppDynamic(): float
    {
        $this->loadMissing([
            'components.componentable',
        ]);

        $total = 0;

        foreach ($this->components as $component) {

            $componentHpp = $this->resolveComponentHppDynamic($component->componentable);

            $total += $componentHpp * $component->qty;
        }

        return $total;
    }

    public function components()
    {
        return $this->hasMany(MenuComponent::class);
    }

    public function recalcHpp()
    {
        $this->hpp = $this->components->sum(function ($c) {
            return ($c->componentable->hpp ?? 0) * $c->qty;
        });

        $this->save();
    }

    protected function resolveComponentHppDynamic($componentable): float
    {
        if (!$componentable) {
            return 0;
        }

        if ($componentable instanceof \App\Models\Ingredient) {
            return (float) ($componentable->ingredientStock->avg_cost ?? 0);
        }

        if ($componentable instanceof \App\Models\Recipe) {

            $componentable->loadMissing('items.ingredient.ingredientStock');

            $total = 0;

            foreach ($componentable->items as $item) {
                $avgCost = (float) ($item->ingredient->ingredientStock->avg_cost ?? 0);
                $total += $avgCost * $item->quantity;
            }

            return $total;
        }

        if ($componentable instanceof \App\Models\Menu) {
            if ($componentable->id === $this->id) {
                return 0;
            }

            return $componentable->calculateHppDynamic();
        }

        return 0;
    }
}
