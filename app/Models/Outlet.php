<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use function Symfony\Component\Translation\t;

class Outlet extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id',
        'code',
        'name',
        'phone_number',
        'type',
        'address',
        'opening_hours',
        'closing_hours',
        'initial_cash',
        'petty_cash',
        'is_active',
    ];

    public function business(){
        return $this->belongsTo(BusinessProfile::class, 'business_id', 'id');
    }

    public function users()
    {
        return $this->hasMany(User::class)->withTimestamps();
    }

    public function settings(){
        return $this->hasMany(Setting::class);
    }
}
