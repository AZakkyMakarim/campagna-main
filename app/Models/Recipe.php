<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    protected $fillable = [
        'business_id',
        'outlet_id',
        'name',
        'ingredient_id',
        'unit_id',
        'quantity',
        'is_active',
    ];

    protected $appends = ['recipe_components'];

    public function business(){
        return $this->belongsTo(BusinessProfile::class, 'business_id');
    }

    public function outlet(){
        return $this->belongsTo(Outlet::class, 'outlet_id');
    }

    public function unit(){
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function ingredient(){
        return $this->belongsTo(Ingredient::class, 'ingredient_id');
    }

    public function items(){
        return $this->hasMany(RecipeItem::class);
    }

    public function getRecipeComponentsAttribute()
    {
        if (!$this->items) return collect();

        return $this->items->map(fn ($item) => [
            'ingredient_id' => $item->ingredient_id,
            'name' => @$item->ingredient->name,
            'quantity' => $item->quantity,
            'unit_id' => $item->unit_id,
            'unit_name' => $item->unit->name,
        ]);
    }
}
