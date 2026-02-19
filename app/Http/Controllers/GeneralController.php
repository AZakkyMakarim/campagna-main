<?php

namespace App\Http\Controllers;

use App\Models\Picture;
use Illuminate\Http\Request;

class GeneralController extends Controller
{
    public function presignedPicture(Request $request){
        try {
            $picture = $request->file('file');
            $imageName = $picture->hashName();

            if ($picture->getRealPath() === false)
            {
                $img = \Image::make($picture->getPathName());
            } else
            {
                $img = \Image::make($picture->getRealPath());
            }

            $img->resize(null, 1000, function ($constraint) {
                $constraint->aspectRatio();
            });

            $resource = $img->stream()->detach();
            $folder = $request->folder;
            $location = strtolower(env('APP_NAME')).'/'.config('app.env').'/images/'.$folder;
            \Illuminate\Support\Facades\Storage::disk('spaces')->put($location.'/'.$imageName, $resource);

            $picture = Picture::create([
                'type'     => $request->type,
                'path'     => $location,
                'file_name' => $imageName,
            ]);

            return $picture;
        }catch(\Exception $e) {
            return $e;
        }
    }
}
