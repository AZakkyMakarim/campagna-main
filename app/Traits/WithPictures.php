<?php

namespace App\Traits;

use App\Models\Picture;

trait WithPictures
{
    public function picture()
    {
        return $this->morphOne(Picture::class, 'pictureable')->latest()->withDefault();
    }

    public function pictureExist()
    {
        return $this->morphMany(Picture::class, 'pictureable')->count() > 0;
    }

    public function pictureType($type){
        return $this->pictures()->where('type', $type)->first();
    }

    public function pictureCaption($caption){
        return $this->pictures()->where('caption', $caption);
    }

    public function pictures()
    {
        return $this->morphMany(Picture::class, 'pictureable');
    }
}
