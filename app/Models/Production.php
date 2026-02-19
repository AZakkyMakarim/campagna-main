<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Production extends Model
{
    protected $fillable = [
        'business_id',
        'outlet_id',
        'recipe_id',
        'code',
        'qty',
        'status',
        'notes',
    ];

    public function business(){
        return $this->belongsTo(BusinessProfile::class, 'business_id');
    }

    public function outlet(){
        return $this->belongsTo(Recipe::class, 'outlet_id');
    }

    public function recipe(){
        return $this->belongsTo(Recipe::class, 'recipe_id');
    }
}
