<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id',
        'name',
        'type',
        'is_active',
    ];

    public function business(){
        return $this->belongsTo(BusinessProfile::class, 'business_id', 'id');
    }
}
