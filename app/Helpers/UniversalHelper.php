<?php

if (!function_exists('active_outlet_id')) {
    function active_outlet_id()
    {
        return session('active_outlet_id');
    }
}

if (!function_exists('active_shift')) {
    function active_shift()
    {
        $shift = \App\Models\CashierShift::where('business_id', auth()->user()->business_id)
        ->where('outlet_id', active_outlet_id())
        ->where('user_id', auth()->user()->id)
        ->where('status', 'OPEN')
        ->first();

        return $shift;
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


if (!function_exists('sort_link')) {
    function sort_link($column) {
        $direction = request('direction') === 'asc' ? 'desc' : 'asc';
        return request()->fullUrlWithQuery([
            'sort' => $column,
            'direction' => $direction
        ]);
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
