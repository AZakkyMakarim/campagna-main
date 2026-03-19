<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_id',
        'menu_id',
        'name_snapshot',
        'note',
        'qty',
        'done_qty',
        'void_qty',
        'hpp',
        'price',
        'subtotal',
        'batch',
    ];

    public function order(){
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function menu(){
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    public function scopeFilters(Builder $query){
        return $query->when(request('name_snapshot'), function ($query){
            $query->where('name_snapshot', 'LIKE', '%'.request('name_snapshot').'%');
        })->when(request('sku') || request('category'), function ($query){
            $query->whereHas('menu', function ($query){
                $query->when(request('sku'), function ($query){
                    $query->where('sku', 'LIKE', '%'.request('sku').'%');
                })
                ->when(request('category'), function ($query){
                    $query->where('category', 'LIKE', '%'.request('category').'%');
                });
            });
        })
            ->when(request('date_range_order'), function ($query){
                $query->whereHas('order', function ($query){
                    $query
                        ->when(request('date_range_order'), function ($query){
                            $date = get_start_and_end_date(request('date_range_order'));
                            $startDate = Carbon::parse($date['start_date'])->startOfDay();
                            $endDate = Carbon::parse($date['end_date'])->endOfDay();

                            $query->whereBetween('created_at', [$startDate, $endDate]);
                        });
                });
            });
    }
}
