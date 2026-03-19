<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Picture extends Model
{
    protected $fillable = [
        'pictureable_type',
        'pictureable_id',
        'type',
        'path',
        'file_name',
        'caption',
    ];

    protected $appends = ['url'];

    public function pictureable()
    {
        return $this->morphTo();
    }

    public function getUrlAttribute()
    {
        if (is_null($this->path)) {
            return '';
        }

        $fileName = "{$this->path}/{$this->file_name}";
        if (!$this->is_local) {
            return "https://djuragan.sgp1.cdn.digitaloceanspaces.com/{$fileName}";
        }

        return env('APP_ENV') === 'production'
            ? global_asset($fileName)
            : asset($fileName);

    }
}
