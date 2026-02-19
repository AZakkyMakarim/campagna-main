<?php

namespace App\Models;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Document extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'documentable_type',
        'documentable_id',
        'path',
        'file_name',
        'caption',
        'is_local',
        'type'
    ];

    protected $appends = ['url'];
    public function documentable()
    {
        return $this->morphTo();
    }

    public function getUrlAttribute()
    {
        if (is_null($this->path)) {
            return null;
        }

        $fileName = "{$this->path}/{$this->file_name}";
        if (!$this->is_local) {
            return "https://djuragan.sgp1.cdn.digitaloceanspaces.com/{$fileName}";
        }

        return env('APP_ENV') === 'production' ? global_asset($fileName) : asset($fileName);
    }
}
