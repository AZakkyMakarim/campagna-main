<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoidLog extends Model
{
    protected $fillable = [
        'order_id',
        'order_item_id',
        'type',
        'void_qty',
        'note',
        'voided_by',
    ];
}
