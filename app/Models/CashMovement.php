<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashMovement extends Model
{
    protected $fillable = [
        'cashier_shift_id',
        'outlet_id',
        'user_id',
        'type',
        'category',
        'amount',
        'description',
    ];

    public function cashierShift(){
        return $this->belongsTo(CashierShift::class, 'cashier_shift_id');
    }

    public function outlet(){
        return $this->belongsTo(Outlet::class, 'outlet_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
