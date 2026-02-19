<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuComponent extends Model
{
    protected $fillable = [
        'menu_id',
        'componentable_type',
        'componentable_id',
        'qty',
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function componentable()
    {
        return $this->morphTo();
    }
}
