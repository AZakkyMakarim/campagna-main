<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockOpnameItem extends Model
{
    protected $fillable = [
        'stock_opname_id',
        'ingredient_id',
        'batch_id',
        'system_qty',
        'physical_qty',
        'diff_qty',
        'cost_per_unit','note'
    ];

    public function ingredient() {
        return $this->belongsTo(Ingredient::class);
    }
}
