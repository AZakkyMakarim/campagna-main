<?php


namespace App\Traits;

use App\Models\Document;
use App\Models\Picture;
use Illuminate\Support\Facades\DB;

trait WithDocuments
{

    public function document()
    {
        return $this->morphOne(Document::class, 'documentable')->latest()->withDefault();
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function documentExist()
    {
        return $this->morphMany(Document::class, 'documentable')->count() > 0;
    }

    public function documentExistType($type)
    {
        return $this->morphMany(Document::class, 'documentable')->where('type', $type)->count() > 0;
    }

    public function documentType($type){
        return $this->documents()->where('type', $type)->latest()->first();
    }

    public function documentsType($type){
        return $this->documents()->where('type', $type);
    }
}
