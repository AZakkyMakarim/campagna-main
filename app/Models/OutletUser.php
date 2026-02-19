<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OutletUser extends Model
{
    protected $fillable = [
        'user_id',
        'outlet_id'
    ];
}
