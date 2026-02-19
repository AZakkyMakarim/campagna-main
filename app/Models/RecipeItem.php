<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecipeItem extends Model
{
    protected $fillable = [
        'recipe_id',
        'ingredient_id',
        'quantity',
        'unit_id',
    ];

    public function recipe(){
        return $this->belongsTo(Recipe::class, 'recipe_id');
    }

    public function ingredient(){
        return $this->belongsTo(Ingredient::class, 'ingredient_id');
    }

    public function unit(){
        return $this->belongsTo(Unit::class, 'unit_id');
    }
}
