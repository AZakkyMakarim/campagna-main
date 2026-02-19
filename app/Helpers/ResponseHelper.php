<?php

if (!function_exists('success_response')) {
    function success_response(string $message, $data = null): array
    {
        return [
            'message' => $message,
            'status' => 'success',
            'data' => $data,
        ];
    }
}

if (!function_exists('warning_response')) {
    function warning_response(string $message, $data = null): array
    {
        return [
            'message' => $message,
            'status' => 'warning',
            'data' => $data,
        ];
    }
}

if (!function_exists('failed_response')) {
    function failed_response(string $message): array
    {
        return [
            'message' => $message,
            'status' => 'danger',
        ];
    }
}

if (!function_exists('api_status_ok')) {
    function api_status_ok($data, $message = "data successfully retrieved", $code = 200, $total_data = null, $total_page = null)
    {
        $response = [
            'url' => url()->full(),
            'method' => request()->getMethod(),
            'request' => request()->except(['password', 'file']),
            'code' => $code,
            'message' => $message,
            'total_data' => $total_data,
            'total_page' => $total_page,
            'payload' => $data
        ];

        return response($response, $code);
    }
}

if (!function_exists('api_status_warning')) {
    function api_status_warning($message = "Something went wrong", $code = 400)
    {
        $response = [
            'url' => url()->full(),
            'method' => request()->getMethod(),
            'request' => request()->except(['password']),
            'code' => $code,
            'message' => $message,
        ];

        return response($response, $code);
    }
}

if (!function_exists('api_status_error')) {
    function api_status_error(\Exception $exception)
    {
        throw_custom_exception($exception);

        $response = [
            'url' => url()->full(),
            'method' => request()->getMethod(),
            'request' => request()->except(['password', 'file']),
            'code' => $exception->getCode()
        ];

        if (config('app.debug') == true) {
            $response = array_merge($response, [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);
        } else {
            $response['message'] = 'Internal Server Error';
        }

        return response($response, $exception->getCode());
    }
}

if (!function_exists('api_status_many')) {
    function api_status_many(...$data)
    {
        $message = "Data successfully retrieved";
        $code = 200;

        $response = [
            'url' => url()->full(),
            'method' => request()->getMethod(),
            'request' => request()->except(['password', 'file']),
            'code' => $code,
            'message' => $message,
        ];

        $payload = [];
        foreach ($data as $key => $value) {
            $payload["data_$key"] = $value;
        }
        $response['payload'] = $payload;

        return response($response, $code);
    }
}
