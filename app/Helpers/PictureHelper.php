<?php

if (!function_exists('insert_picture')) {
    function insert_picture($picture, $model, $type = null, $caption = null, $base64 = false, ) {
        $service = new \App\Services\PictureService();
        $folder = class_basename($model);
        $location = 'campagna/'.config('app.env').'/images/'.$folder;

        if($base64 == true){
            $pos  = strpos($picture, ';');
            $type = explode(':', substr($picture, 0, $pos))[1];

            $extension = explode('/', $type)[1];
            if($extension=='jpeg')$extension='jpg';

            $name = uniqid();


            $path = $location.'/' .$name;

            $service->save_base64_image($picture, $path);

            $picture = $model->picture()->create([
                'path' => $location,
                'file_name' => $name .'.'. $extension,
                'caption' => $caption,
                'type' => $type
            ]);

            return $picture;

        }


        return $service->upload($picture, $model, $location, $caption, $type);
    }
}


if (!function_exists('insert_pictures')) {
    function insert_pictures($pictures, $model, $caption = null) {
        $uploaded = collect();

        foreach ($pictures as $picture) {
            $pic = insert_picture($picture, $model, $caption);
            $uploaded->push($pic);
        }

        return $uploaded;
    }
}


if (!function_exists('get_picture_html')) {
    function get_picture_html($url, $class=null, $height = null, $style = "") {
        return '<img src="'.$url.'"
                     onerror="this.onerror=null;this.src=\' '.asset('images/404.jpg').' \';"
                     class="'.$class.'"
                     style="'.$style.'"
                     width='.$height.'>';
    }
}

if (!function_exists('getBase64ImageFromUrl')) {
    function getBase64ImageFromUrl($imageUrl) {
        $fileExtension = explode('?', pathinfo($imageUrl, PATHINFO_EXTENSION))[0];

        $i = 0; $imageSize = false;
        while ($i < 3 && !$imageSize) {
            $imageSize = @getimagesize($imageUrl);
            $i++;
        }

        if ($imageSize) {
            $imageData = file_get_contents($imageUrl);
            $base64Image = base64_encode($imageData);

            switch (strtolower($fileExtension)) {
                case 'jpg':
                case 'jpeg':
                    return 'data:image/jpeg;base64,' . $base64Image;
                case 'png':
                    return 'data:image/png;base64,' . $base64Image;
                case 'gif':
                    return 'data:image/gif;base64,' . $base64Image;
                case 'bmp':
                    return 'data:image/bmp;base64,' . $base64Image;
                case 'webp':
                    return 'data:image/webp;base64,' . $base64Image;
                default:
                    throw new Exception('Unsupported image format.');
            }
        } else {
            throw new Exception('Unable to fetch image from the URL: ' . $imageUrl);
        }
    }
}
