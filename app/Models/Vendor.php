<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $fillable = [
        'business_id',
        'name',
        'phone_number',
        'address',
        'link_maps',
        'is_active',
    ];

    public function vendorIngredients()
    {
        return $this->hasMany(VendorIngredient::class);
    }

    public function ingredients()
    {
        return $this->belongsToMany(
            Ingredient::class,
            'vendor_ingredients'
        )->wherePivot('is_active', true);
    }

    public function getVendorComponentsAttribute()
    {
        if (!$this->vendorIngredients) return collect();

        return $this->vendorIngredients()
            ->whereHas('ingredient', function ($query){
                $query->where('outlet_id', active_outlet_id());
            })
            ->get()
            ->map(fn ($item) => [
            'ingredient_id' => $item->ingredient_id,
            'name' => @$item->ingredient->name,
            'unit_id' => @$item->ingredient->base_unit_id,
            'unit_name' => @$item->ingredient->baseUnit->name,
        ]);
    }
}
