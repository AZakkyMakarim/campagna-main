<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'cashier_id',
        'method',
        'amount',
        'paid_at',
        'reference',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
