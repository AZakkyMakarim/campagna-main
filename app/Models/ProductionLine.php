<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionLine extends Model
{
    protected $fillable = [
        'production_id',
        'ingredient_id',
        'qty',
        'unit_id'
    ];

    public function production(){
        return $this->belongsTo(Production::class, 'production_id');
    }

    public function ingredient(){
        return $this->belongsTo(Ingredient::class, 'ingredient_id');
    }

    public function unit(){
        return $this->belongsTo(Unit::class, 'unit_id');
    }
}
