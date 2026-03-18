<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

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
        'table_number',
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

    public function payment(){
        return $this->morphOne(Payment::class, 'payable')->latest();
    }

    public function getPaidAmountAttribute()
    {
        return $this->payments()->sum('amount');
    }

    public function getRemainingAmountAttribute()
    {
        return max(0, $this->grand_total - $this->paid_amount);
    }

    public function calculateHpp(): float
    {
        $this->loadMissing('items.menu');

        return $this->items->sum(function ($item) {

            $menuHpp = (float) ($item->menu->hpp ?? 0);
            $qty = (float) ($item->qty ?? 0);

            return $menuHpp * $qty;
        });
    }

    public function scopeFilters(Builder $query){
        return $query
            ->when(request('code'), function ($query){
                $query->where('code', 'LIKE', '%'.request('code').'%');
            })
            ->when(request('table_number'), function ($query){
                $query->where('table_number', 'LIKE', '%'.request('table_number').'%');
            })
            ->when(request('type'), function ($query){
                $query->where('type', request('type'));
            })
            ->when(request('status'), function ($query){
                $query->where('status', request('status'));
            })
            ->when(request('payment_status'), function ($query){
                $query->where('payment_status', request('payment_status'));
            })
            ->when(request('order_types'), function ($query){
                $query->whereIn('type', request('order_types'));
            })
            ->when(request('payment_methods'), function ($query){
                $query->whereHas('payment', function ($query){
                    $query->whereIn('method', request('payment_methods'));
                });
            })
            ->when(request('date_range_order'), function ($query){
                $date = get_start_and_end_date(request('date_range_order'));
                $startDate = Carbon::parse($date['start_date'])->startOfDay();
                $endDate = Carbon::parse($date['end_date'])->endOfDay();

                $query->whereBetween('created_at', [$startDate, $endDate]);
            });
    }
}
