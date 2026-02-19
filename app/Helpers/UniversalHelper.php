<?php

if (!function_exists('active_outlet_id')) {
    function active_outlet_id()
    {
        return session('active_outlet_id');
    }
}

if (!function_exists('subdomain')) {
    function subdomain()
    {
        $host = request()->getHost();
        $subdomain = explode('.', $host)[0];

        return $subdomain;
    }
}

if (!function_exists('calculate_rounding')) {
    function calculate_rounding(int $amount): int
    {
        $lastTwo = $amount % 100;

        if ($lastTwo < 50) {
            return -$lastTwo; // ke bawah
        }

        return 100 - $lastTwo; // ke atas
    }
}
