<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IngredientStock extends Model
{
    protected $fillable = [
        'business_id',
        'outlet_id',
        'ingredient_id',
        'qty',
        'avg_cost'
    ];

    public function businesss() {
        return $this->belongsTo(BusinessProfile::class);
    }

    public function outlet() {
        return $this->belongsTo(Outlet::class);
    }

    public function ingredient() {
        return $this->belongsTo(Ingredient::class);
    }
}
