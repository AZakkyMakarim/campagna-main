<?php

if (!function_exists('insert_document')) {
    function insert_document($document, $model, $type = null, $caption = null, $base64 = false) {
        $service = new \App\Services\DocumentService();
        $folder = class_basename($model);
        $location = 'dparagon/'.config('app.env').'/document/'.$folder;

        if($base64 == true){
            $pos  = strpos($document, ';');
            $documentType = explode(':', substr($document, 0, $pos))[1];

            $extension = explode('/', $documentType)[1];
            if($extension=='jpeg')$extension='jpg';

            $name = uniqid();

            $path = $location.'/' .$name;

            $service->save_base64_image($document, $path);

            $picture = $model->document()->create([
                'path' => $location,
                'file_name' => $name .'.'. $extension,
                'caption' => $caption,
                'type' => $type
            ]);

            return $picture;

        }

        return $service->upload($document, $model, $location, $caption, $type);
    }
}


if (!function_exists('insert_documents')) {
    function insert_documents($documents, $model) {
        $uploaded = collect();

        foreach ($documents as $document) {
            $doc = insert_document($document, $model);
            $uploaded->push($doc);
        }

        return $uploaded;
    }
}

if (!function_exists('insert_temporary_document')) {
    function insert_temporary_document($document, $model, $caption = null, $type = null) {
        $service = new \App\Services\DocumentService();
        $folder = class_basename($model);
        $location = 'dparagon/'.config('app.env').'/document/'.$folder;

        $tempDir = storage_path('app/temp');
        $tempFilePath = $tempDir . '/' . uniqid() . '.pdf';

        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        file_put_contents($tempFilePath, $document);
        $tempFile = new \SplFileObject($tempFilePath);

        $upload = $service->temporaryUpload($tempFile, $model, $location, $caption, $type);
        unlink($tempFilePath);

        return $upload;
    }
}

