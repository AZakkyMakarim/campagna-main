<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
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
}
