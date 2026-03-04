<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'outlet_id',
        'code',
        'customer_name',
        'phone',
        'reserved_at',
        'table',
        'status',
        'note',
    ];

    public function outlet(){
        return $this->belongsTo(Outlet::class);
    }

    public function orders() {
        return $this->hasMany(Order::class);
    }

    public function payments(){
        return $this->morphMany(Payment::class, 'payable');
    }
}
