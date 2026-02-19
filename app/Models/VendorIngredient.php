<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorIngredient extends Model
{
    protected $fillable = [
        'vendor_id',
        'ingredient_id',
        'is_active',
    ];

    public function vendor(){
        return $this->belongsTo(Vendor::class);
    }

    public function ingredient(){
        return $this->belongsTo(Ingredient::class);
    }
}
