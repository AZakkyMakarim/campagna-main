<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
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
    ];

    public function order(){
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function menu(){
        return $this->belongsTo(Menu::class, 'menu_id');
    }
}
