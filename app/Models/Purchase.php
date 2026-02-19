<?php

namespace App\Models;

use App\Traits\WithDocuments;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use WithDocuments;

    protected $fillable = [
        'code',
        'vendor_id',
        'outlet_id',
        'business_id',
        'total_cost',
        'description',
        'purchased_at',
        'created_by',
    ];

    public function vendor(){
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function outlet(){
        return $this->belongsTo(Outlet::class, 'vendor_id');
    }

    public function business(){
        return $this->belongsTo(BusinessProfile::class, 'business_id');
    }

    public function ingredientBatches(){
        return $this->hasMany(IngredientBatch::class, 'purchase_id');
    }

    public function createdBy(){
        return $this->belongsTo(User::class, 'created_by');
    }
}
