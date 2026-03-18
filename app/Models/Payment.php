<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;

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

    public function scopeFilters(Builder $query){
        return $query->when(request('payment_method'), function ($query){
            $query->where('method', 'LIKE', '%'.request('payment_method').'%');
        })->when(request('date_range_order'), function ($query){
            $query->whereHasMorph('payable', [Order::class], function ($query){
                $date = get_start_and_end_date(request('date_range_order'));
                $startDate = Carbon::parse($date['start_date'])->startOfDay();
                $endDate = Carbon::parse($date['end_date'])->endOfDay();

                $query->whereBetween('created_at', [$startDate, $endDate]);
            });
        });
    }
}
