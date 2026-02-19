<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxRule extends Model
{
    protected $fillable = [
        'business_id',
        'name',
        'type',
        'calculation_type',
        'value',
        'is_active',
    ];

    public function businessProfile(){
        return $this->belongsTo(BusinessProfile::class, 'business_id', 'id');
    }
}
