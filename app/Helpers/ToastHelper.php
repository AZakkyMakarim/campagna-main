<?php

if (!function_exists('toast')) {
    function toast(string $message, string $type = 'success', $redirect = null)
    {
        $map = [
            'danger' => 'error',
            'error'  => 'error',
            'success'=> 'success',
            'warning'=> 'warning',
            'info'   => 'info',
        ];

        $icon = $map[$type] ?? $type;

        return ($redirect ?? back())->with([
            'toast.message' => $message,
            'toast.type'    => $icon,
        ]);
    }
}
