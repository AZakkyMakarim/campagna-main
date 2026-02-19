<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderType extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id',
        'code',
        'name',
        'type',
        'description',
        'is_active',
    ];

    public function business(){
        return $this->belongsTo(BusinessProfile::class, 'business_id', 'id');
    }
}
