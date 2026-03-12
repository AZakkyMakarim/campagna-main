<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IngredientBatch extends Model
{
    protected $fillable = [
        'code',
        'purchase_id',
        'vendor_id',
        'ingredient_id',
        'outlet_id',
        'qty_in',
        'qty_remaining',
        'cost_per_unit',
        'source',
        'received_at',
    ];

    public function ingredient(){
        return $this->belongsTo(Ingredient::class);
    }

    public function vendor(){
        return $this->belongsTo(Vendor::class);
    }

    public function outlet(){
        return $this->belongsTo(Outlet::class);
    }
}
