<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Printer extends Model
{
    protected $fillable = [
        'outlet_id',
        'device_name',
        'connection_type',
        'ip_address',
        'port',
        'role', // cashier | kitchen | bar
        'section', // all | makanan | minuman
        'is_active'
    ];

    public function outlet(){
        return $this->belongsTo(Outlet::class, 'outlet_id', 'id');
    }
}
