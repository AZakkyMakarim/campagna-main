<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'movementable_type',
        'movementable_id',
        'business_id',
        'ingredient_id',
        'batch_id',
        'outlet_id',
        'batch_id',
        'code',
        'type',
        'qty',
        'cost_per_unit',
        'user_id',
    ];

    public function movementable(){
        return $this->morphTo();
    }

    public function business(){
        return $this->belongsTo(BusinessProfile::class, 'business_id');
    }

    public function ingredient(){
        return $this->belongsTo(Ingredient::class, 'ingredient_id');
    }

    public function outlet(){
        return $this->belongsTo(Outlet::class, 'outlet_id');
    }

    public function batch(){
        return $this->belongsTo(IngredientBatch::class, 'batch_id');
    }

    public function unit(){
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
