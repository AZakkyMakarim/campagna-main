<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    protected $fillable = [
        'business_id',
        'outlet_id',
        'name',
        'code',
        'type',
        'base_unit_id',
        'stock',
        'min_stock',
        'is_active',
        'is_sellable',
        'is_unlimited_stock'
    ];

    protected $appends = ['recipe_components', 'total_stock'];

    public function business(){
        return $this->belongsTo(BusinessProfile::class, 'business_id');
    }

    public function outlet(){
        return $this->belongsTo(Outlet::class, 'outlet_id');
    }

    public function baseUnit(){
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    public function recipe(){
        return $this->hasOne(Recipe::class, 'ingredient_id');
    }

    public function batches(){
        return $this->hasMany(IngredientBatch::class, 'ingredient_id');
    }

    public function unitConversions(){
        return $this->hasMany(UnitConversion::class, 'from_unit_id', 'base_unit_id')
            ->where('is_active', 1)
            ->with('toUnit');
    }

    public function vendors()
    {
        return $this->belongsToMany(
            Vendor::class,
            'vendor_ingredients'
        )->wherePivot('is_active', true);
    }

    public function convertedUnits()
    {
        return $this->hasManyThrough(
            Unit::class,
            UnitConversion::class,
            'from_unit_id', // FK di unit_conversions
            'id',           // PK di units
            'base_unit_id', // local key di ingredients
            'to_unit_id'    // FK tujuan di unit_conversions
        )->where('unit_conversions.is_active', 1);
    }

    public function getRecipeComponentsAttribute()
    {
        if (!$this->recipe) return [];

        return $this->recipe->items->map(fn ($item) => [
            'ingredient_id' => $item->ingredient_id,
            'name' => @$item->ingredient->name,
            'quantity' => $item->quantity,
            'unit_id' => $item->unit_id,
            'unit_name' => $item->unit->name,
        ]);
    }

    public function getTotalStockAttribute()
    {
        return $this->batches()->sum('qty_remaining');
    }
}
