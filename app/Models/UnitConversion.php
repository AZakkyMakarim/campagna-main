<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitConversion extends Model
{
    protected $fillable = [
        'business_id',
        'outlet_id',
        'from_unit_id',
        'to_unit_id',
        'multiplier',
        'is_active',
    ];

    public function business(){
        return $this->belongsTo(BusinessProfile::class, 'business_id');
    }

    public function outlet(){
        return $this->belongsTo(Outlet::class, 'outlet_id');
    }

    public function fromUnit(){
        return $this->belongsTo(Unit::class, 'from_unit_id');
    }

    public function toUnit(){
        return $this->belongsTo(Unit::class, 'to_unit_id');
    }
}
