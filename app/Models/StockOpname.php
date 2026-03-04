<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockOpname extends Model
{
    protected $fillable = [
        'code',
        'date',
        'business_id',
        'outlet_id',
        'user_id',
        'status',
        'note'
    ];

    public function items() {
        return $this->hasMany(StockOpnameItem::class);
    }

    public function movements() {
        return $this->morphMany(StockMovement::class, 'movementable');
    }
}
