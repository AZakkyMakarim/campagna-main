<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderAdjustment extends Model
{
    protected $fillable = [
        'order_id',
        'type',
        'name',
        'method',
        'value',
        'amount',
        'is_addition',
    ];
}
