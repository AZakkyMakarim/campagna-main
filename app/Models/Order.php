<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'business_id',
        'outlet_id',
        'cashier_id',
        'reservation_id',
        'customer_name',
        'customer_phone',
        'code',
        'queue_number',
        'type',
        'channel',
        'status',
        'payment_status',
        'sub_total',
        'adjustment_total',
        'grand_total',
        'note',
        'opened_at',
        'closed_at',
    ];

    public function outlet(){
        return $this->belongsTo(Outlet::class);
    }

    public function cashier(){
        return $this->belongsTo(User::class);
    }

    public function reservation() {
        return $this->belongsTo(Reservation::class);
    }

    public function items(){
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function adjustments()
    {
        return $this->hasMany(OrderAdjustment::class);
    }

    public function payments(){
        return $this->morphMany(Payment::class, 'payable');
    }

    public function getPaidAmountAttribute()
    {
        return $this->payments()->sum('amount');
    }

    public function getRemainingAmountAttribute()
    {
        return max(0, $this->grand_total - $this->paid_amount);
    }
}
