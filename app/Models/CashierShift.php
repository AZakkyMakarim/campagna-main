<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashierShift extends Model
{
    protected $fillable = [
        'business_id',
        'outlet_id',
        'user_id',
        'shift_code',
        'opened_at',
        'closed_at',
        'opening_cash',
        'opening_petty_cash',
        'expected_cash',
        'expected_petty_cash',
        'actual_cash',
        'actual_petty_cash',
        'cash_difference',
        'petty_cash_difference',
        'status',
        'note',
    ];

    public function business(){
        return $this->belongsTo(BusinessProfile::class, 'business_id');
    }

    public function outlet(){
        return $this->belongsTo(Outlet::class, 'outlet_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cashMovements(){
        return $this->hasMany(CashMovement::class, 'cashier_shift_id');
    }
}
