<?php

namespace App\Services;

use App\Http\Controllers\Controller;
use App\Models\Picture;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Svg\Tag\Image;

class PictureService extends Controller
{
    public function upload($picture, $model, $location, $caption, $type){

        try {
            $imageName = $picture->hashName();

            Storage::disk('spaces')->putFileAs(
                $location,
                $picture,
                $imageName,
                'public'
            );

            $picture = $model->picture()->create([
                'path'      => $location,
                'file_name'  => $imageName,
                'caption'   => $caption,
                'type'      => $type,
            ]);

            return $picture;
        }catch(\Exception $e) {
            dd($e);
        }
    }

    function save_base64_image($base64_image_string, $output_file_without_extension, $path_with_end_slash="" ) {
        $splited = explode(',', substr( $base64_image_string , 5 ) , 2);
        $mime=$splited[0];
        $data=$splited[1];

        $mime_split_without_base64=explode(';', $mime,2);
        $mime_split=explode('/', $mime_split_without_base64[0],2);
        if(count($mime_split)==2)
        {
            $extension=$mime_split[1];
            if($extension=='jpeg')$extension='jpg';
            $output_file_with_extension=$output_file_without_extension.'.'.$extension;
        }
        Storage::disk('spaces')->put($output_file_with_extension, base64_decode($data));

        return $output_file_with_extension;
    }

}
