<?php

namespace App\Services;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class DocumentService extends Controller
{
    public function upload($document, $model, $location, $caption, $type){
        $name = pathinfo($document->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = pathinfo($document->getClientOriginalName(), PATHINFO_EXTENSION);

        $filename = base64_encode(Carbon::now()."_".$name."_".uniqid()).".".$extension;
        Storage::disk('spaces')->put($location.'/'.$filename, file_get_contents($document), 'public');

        $document = $model->document()->create([
            'path' => $location,
            'file_name' => $filename,
            'caption' => $caption,
            'type' => $type
        ]);

        return $document;
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

    public function temporaryUpload($document, $model, $location, $caption, $type){
        $name = pathinfo($document->getFilename(), PATHINFO_FILENAME);
        $extension = pathinfo($document->getFilename(), PATHINFO_EXTENSION);

        $filename = base64_encode(Carbon::now()."_".$name."_".uniqid()).".".$extension;

        $stream = fopen($document->getRealPath(), 'r');
        Storage::disk('spaces')->put($location . '/' . $filename, $stream, 'public');
        fclose($stream);

        $document = $model->document()->create([
            'path' => $location,
            'file_name' => $filename,
            'caption' => $caption,
            'type' => $type
        ]);

        return $document;
    }
}
