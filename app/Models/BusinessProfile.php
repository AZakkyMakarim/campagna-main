<?php

namespace App\Models;

use App\Traits\WithPictures;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessProfile extends Model
{
    use SoftDeletes, WithPictures;

    protected $fillable = [
        'name',
        'type',
        'address',
        'phone_number',
        'email',
        'npwp',
        'is_active'
    ];

    public function user(){
        return $this->belongsTo('user_id');
    }

    public function outlets(){
        return $this->hasMany(Outlet::class, 'business_id');
    }
}
