<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class QZController extends Controller
{
    public function sign(Request $request)
    {
        $data = $request->getContent();

        $privateKey = file_get_contents(storage_path('app/qz/private-key.pem'));

        openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        return response(base64_encode($signature));
    }

    public function cert()
    {
        $certificate = file_get_contents(storage_path('app/qz/digital-certificate.txt'));

        return $certificate;
    }
}
