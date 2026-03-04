<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'payable_type',
        'payable_id',
        'cashier_id',
        'type',
        'method',
        'amount',
        'paid_at',
        'reference',
    ];

    public function payable()
    {
        return $this->morphTo();
    }
}
