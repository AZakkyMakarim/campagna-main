<?php

use App\Models\Closing;
use Carbon\Carbon;

if (!function_exists('get_months')) {
    function get_months()
    {
        return array(
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember',
        );
    }
}

if (!function_exists('get_month_simple')) {
    function get_month_simple($month)
    {
        $month = ((integer) $month) - 1;
        $array =  array(
            'JAN',
            'FEB',
            'MAR',
            'APR',
            'MEI',
            'JUN',
            'JUL',
            'AGU',
            'SEP',
            'OKT',
            'NOV',
            'DES',
        );

        return $array[$month];
    }
}

if (!function_exists('get_month_full_name')) {
    function get_month_full_name($month)
    {
        $month = ((integer) $month) - 1;
        $array =  array(
            'JANUARI',
            'FEBRUARI',
            'MARET',
            'APRIL',
            'MEI',
            'JUNI',
            'JULI',
            'AGUSTUS',
            'SEPTEMBER',
            'OKTOBER',
            'NOVEMBER',
            'DESEMBER',
        );

        return $array[$month];
    }
}

if (!function_exists('get_month_name')) {
    function get_month_name($month)
    {
        $m = $month - 1;
        return get_months()[$m] ?? 'Undefined';
    }
}

if (!function_exists('get_month_romawi')) {
    function get_month_roman()
    {
        switch (date('m')){
            case 1;
                return 'I';
            case 2;
                return 'II';
            case 3;
                return 'III';
            case 4;
                return 'IV';
            case 5;
                return 'V';
            case 6;
                return 'VI';
            case 7;
                return 'VII';
            case 8;
                return 'VIII';
            case 9;
                return 'IX';
            case 10;
                return 'X';
            case 11;
                return 'XI';
            case 12;
                return 'XII';
        }
    }
}

if (!function_exists('parse_date')) {
    function parse_date($date = null)
    {
        if($date == null){
            return '-';
        }
        \Carbon\Carbon::setLocale('id');
//        $date = \Carbon\Carbon::parse($date)->formatLocalized("%d %B %Y");
        $date = \Carbon\Carbon::parse($date);
        return $date->format('d').'-'.get_month_simple($date->format('m')).'-'.$date->format('Y');
    }
}

if (!function_exists('laundry_receipt_format')) {
    function laundry_receipt_format($date = null)
    {
        if($date == null){
            return '-';
        }
        \Carbon\Carbon::setLocale('id');
//        $date = \Carbon\Carbon::parse($date)->formatLocalized("%d %B %Y");
        $date = \Carbon\Carbon::parse($date);

        $localeDay = $date->format('l');
        $translatedDay = '';

        switch($localeDay)
        {
            case 'Monday':
                $translatedDay = "Senin";
                break;
            case 'Tuesday':
                $translatedDay = "Selasa";
                break;
            case 'Wednesday':
                $translatedDay = "Rabu";
                break;
            case 'Thursday':
                $translatedDay = "Kamis";
                break;
            case 'Friday':
                $translatedDay = "Jum'at";
                break;
            case 'Saturday':
                $translatedDay = "Sabtu";
                break;
            default:
                break;

        }

        return $translatedDay.', '.$date->format('d').' '.get_month_name($date->format('m')).' '.$date->format('Y');
    }
}

if (!function_exists('parse_date_string')) {
    function parse_date_string($date = null)
    {
        if($date == null){
            return '-';
        }
        \Carbon\Carbon::setLocale('id');
//        $date = \Carbon\Carbon::parse($date)->formatLocalized("%d %B %Y");
        $date = \Carbon\Carbon::parse($date);
        return $date->format('d').'-'.get_month_full_name($date->format('m')).'-'.$date->format('Y');
    }


}

if (!function_exists('parse_date_full')) {
    function parse_date_full($date = null)
    {
        if($date == null){
            return '-';
        }
        \Carbon\Carbon::setLocale('id');
//        $date = \Carbon\Carbon::parse($date)->formatLocalized("%d %B %Y");
        $date = \Carbon\Carbon::parse($date);
        return $date->translatedFormat('l').', '.$date->format('d').' '.get_month_name($date->format('m')).' '.$date->format('Y');
    }
}

if (!function_exists('parse_date_day')) {
    function parse_date_day($date = null)
    {
        if($date == null){
            return '-';
        }
        \Carbon\Carbon::setLocale('id');
        $date = \Carbon\Carbon::parse($date);
        return $date->isoFormat('dddd');
    }
}

if (!function_exists('parse_date_time')) {
    function parse_date_time($date = null)
    {
        if($date == null){
            return '-';
        }
        \Carbon\Carbon::setLocale('id');
//        $date = \Carbon\Carbon::parse($date)->formatLocalized("%d %B %Y, %I:%M:%S %p");
        $date = \Carbon\Carbon::parse($date);
        return $date->format('d').' '.get_month_name($date->format('m')).' '.$date->format('Y'). ', '.\Carbon\Carbon::parse($date)->translatedFormat('H:i');
//        return $date;
    }
}

if (!function_exists('parse_date_time_iso')) {
    function parse_date_time_iso($date = null)
    {
        if ($date instanceof DateTime) {
            \Carbon\Carbon::setLocale('id');
            return $date->isoFormat('LLLL');
        } else {
            if ($date == null) {
                return '-';
            } else {
                \Carbon\Carbon::setLocale('id');
                return \Carbon\Carbon::parse($date)->isoFormat('LLLL');
            }
        }
    }
}

if (!function_exists('parse_time')) {
    function parse_time($date = null)
    {
        if($date == null){
            return '-';
        }
        \Carbon\Carbon::setLocale('id');
        return \Carbon\Carbon::parse($date)->translatedFormat('H:i:s');
    }
}

if (!function_exists('parse_time_hm')) {
    function parse_time_hm($date = null)
    {
        if($date == null){
            return '-';
        }
        \Carbon\Carbon::setLocale('id');
        return \Carbon\Carbon::parse($date)->translatedFormat('H:i');
    }
}

if (!function_exists('parse_month_year')) {
    function parse_month_year($date = null)
    {
        if ($date == null) {
            return '-';
        }

        \Carbon\Carbon::setLocale('id');

        $date = \Carbon\Carbon::parse($date);
        return get_month_name($date->format('m')).' '.$date->format('Y');
    }
}

if (!function_exists('parse_month')) {
    function parse_month($date = null)
    {
        if ($date == null) {
            return '-';
        }

        \Carbon\Carbon::setLocale('id');

        $date = \Carbon\Carbon::parse($date);
        return get_month_name($date->format('m'));
    }
}

if (!function_exists('parse_year')) {
    function parse_year($date = null)
    {
        if ($date == null) {
            return '-';
        }

        \Carbon\Carbon::setLocale('id');

        $date = \Carbon\Carbon::parse($date);
        return $date->format('Y');
    }
}

if (!function_exists('parse_start_and_end_date')) {
    function parse_start_and_end_date($start, $end)
    {
        $start = Carbon::parse($start)->format('m/d/Y');
        $end = Carbon::parse($end)->format('m/d/Y');

        return $start ." - ". $end;
    }
}

if (!function_exists('parse_timezone_id')) {
    function parse_timezone_id($timezone) {
        if ($timezone == "Asia/Makassar") {
            $time_suffix = "WITA";
        } else if ($timezone == "Asia/Jayapura") {
            $time_suffix = "WIT";
        } else {
            $time_suffix = "WIB";
        }

        return $time_suffix;
    }
}

if (!function_exists('get_years')) {
    function get_years()
    {
        $year = collect();

        for($i = 2010; $i < 2050; $i ++){
            $year->push($i);
        }

        return $year->toArray();

    }
}

if (!function_exists('get_between_5_years')) {
    function get_between_5_years()
    {
        $currentYear = date("Y");
        $year = [];

        for ($i = -5; $i <= 5; $i++) {
            $year[] = $currentYear + $i;
        }

        return $year;

    }
}

if (!function_exists('get_start_to_end_date')) {
    function get_start_to_end_date($dates)
    {
        try {
            $range = explode(' - ', $dates);

            $start = Carbon::createFromFormat('m/d/Y', $range[0])->startOfDay();
            $end = Carbon::createFromFormat('m/d/Y', $range[1])->endOfDay();
            return [
                'start_date' => $start,
                'end_date' => $end
            ];
        } catch (\Exception $e) {
            return [
                'start_date' => null,
                'end_date' => null
            ];
        }
    }
}

if (!function_exists('get_start_and_end_date')) {
    function get_start_and_end_date($dates)
    {
        try {
            $range = preg_split('/\s+to\s+|\s+-\s+/', $dates);
            $start = Carbon::createFromFormat('Y-m-d', $range[0])->toDateString();
            $end = Carbon::createFromFormat('Y-m-d', $range[1] ?? $range[0])->toDateString();

            return [
                'start_date' => $start,
                'end_date' => $end
            ];
        } catch (\Exception $e) {
            return [
                'start_date' => null,
                'end_date' => null
            ];
        }
    }
}

if (!function_exists('get_diff_date')) {
    function get_diff_date($start, $end)
    {
        $start = Carbon::parse($start);
        $end = Carbon::parse($end);

        $diff = $start->diff($end);
        $diffNegative = $start->diffInDays($end, false);
        if ($diffNegative >= 0) {
            $result = $diff->d.' Hari '.$diff->m.' Bulan '.$diff->y.' Tahun';
        }else {
            $day = $diffNegative >= 0 ? $diff->d : 0;
            $month = $diffNegative >= 0 ? $diff->m : 0;
            $year = $diffNegative >= 0 ? $diff->y : 0;
            $result = $day.' Hari '.$month.' Bulan '.$year.' Tahun';
        }

        return $result;
    }
}

if (!function_exists('get_diff_days')) {
    function get_diff_days($start, $end)
    {
        $start = Carbon::parse($start);
        $end = Carbon::parse($end);

        $diff = $start->diffInDays($end, false);
        return $diff;
    }
}

if (!function_exists('get_date_ranges')) {
    function get_date_ranges($dates)
    {
        $range = explode(' - ', $dates);
        $start = Carbon::createFromFormat('m/d/Y', $range[0]);
        $end = Carbon::createFromFormat('m/d/Y', $range[1]);

        $dates = [];

        for($date = $start; $date->lte($end); $date->addDay()) {
            $dates[] = $date->format('Y-m-d');
        }

        return $dates;
    }
}

if (!function_exists('get_date_array')) {
    function get_date_array($start, $end)
    {
        $start = Carbon::parse($start);
        $end = Carbon::parse($end);

        $dates = [];

        for($date = $start; $date->lte($end); $date->addDay()) {
            $dates[] = $date->format('Y-m-d');
        }

        return $dates;
    }
}

if (!function_exists('get_check_in_date_range')) {
    function get_check_in_date_range($startDate, $duration, $duration_type, $subDay = false)
    {
        $endDate = get_end_date_reservation($startDate, $duration, $duration_type, $subDay);

        $dates = [];

        $start = Carbon::parse($startDate)->format('m/d/Y');
        $end = $endDate->format('m/d/Y');

        $start = Carbon::createFromFormat('m/d/Y', $start);
        $end = Carbon::createFromFormat('m/d/Y', $end);

        for($date = $start; $date->lte($end); $date->addDay()) {
            $dates[] = $date->format('Y-m-d');
        }

        return $dates;
    }
}

if (!function_exists('get_reservation_dates_array')) {
    function get_reservation_dates_array($startDate, $duration, $duration_type)
    {
        $endDate = calculate_check_out_date($startDate, $duration, $duration_type);

        $dates = [];

        $start = Carbon::parse($startDate)->format('m/d/Y');
        $end = $endDate->format('m/d/Y');

        $start = Carbon::createFromFormat('m/d/Y', $start);
        $end = Carbon::createFromFormat('m/d/Y', $end);

        for($date = $start; $date->lte($end); $date->addDay()) {
            $dates[] = $date->format('Y-m-d');
        }

        return $dates;
    }
}

if (!function_exists('calculate_check_out_date')) {
    function calculate_check_out_date($startDate, $duration, $duration_type)
    {
        switch ($duration_type){
            case \App\Constants\DurationType::BULANAN:
                return Carbon::parse($startDate)->addMonthsNoOverflow($duration);
            case \App\Constants\DurationType::MINGGUAN:
                return Carbon::parse($startDate)->addWeeks($duration);
            case \App\Constants\DurationType::HARIAN:
                return Carbon::parse($startDate)->addDays($duration);
            default:
                return false;
        }
    }
}

if (!function_exists('get_extended_reservation_date')) {
    function get_extended_reservation_date($ciDate, $coDate, $addDuration, $addDurationType)
    {
        $endDate = calculate_check_out_date($coDate, $addDuration, $addDurationType);

        $dates = [];

        $start = Carbon::parse($ciDate)->format('m/d/Y');
        $end = $endDate->format('m/d/Y');

        $start = Carbon::createFromFormat('m/d/Y', $start);
        $end = Carbon::createFromFormat('m/d/Y', $end);

        for($date = $start; $date->lte($end); $date->addDay()) {
            $dates[] = $date->format('Y-m-d');
        }

        return $dates;
    }
}

if (!function_exists('get_extend_date_range')) {
    function get_extend_date_range($startDate, $duration, $duration_type, $subDay = false)
    {
        $endDate = get_end_date_reservation($startDate, $duration, $duration_type, $subDay);

        $dates = [];

        $start = Carbon::parse($startDate)->format('m/d/Y');
        $end = $endDate->format('m/d/Y');

        $start = Carbon::createFromFormat('m/d/Y', $start);
        $end = Carbon::createFromFormat('m/d/Y', $end);

        for($date = $start; $date->lte($end); $date->addDay()) {
            $dates[] = $date->format('Y-m-d');
        }

        return $dates;
    }
}

if (!function_exists('check_date_type')) {
    function check_date_type($date)
    {
        $weekMap = [
            0 => 'SU',
            1 => 'MO',
            2 => 'TU',
            3 => 'WE',
            4 => 'TH',
            5 => 'FR',
            6 => 'SA',
        ];

        $dayStart = Carbon::parse($date);
        $dayOfTheWeek = $dayStart->dayOfWeek;
        return $weekMap[$dayOfTheWeek];
    }
}

if (!function_exists('is_weekend')) {
    function is_weekend($date): bool
    {
        $dateType = check_date_type($date);
        if ($dateType == 'FR' || $dateType == 'SA' || $dateType == 'SU') {
            return true;
        }

        return false;
    }
}

if (!function_exists('get_end_date_reservation')) {
    function get_end_date_reservation($startDate, $duration, $duration_type, $subDay = false)
    {
        switch ($duration_type){
            case \App\Constants\DurationType::BULANAN:
                return Carbon::parse($startDate)->addMonths($duration);
            case \App\Constants\DurationType::MINGGUAN:
                return Carbon::parse($startDate)->addWeeks($duration);
            case \App\Constants\DurationType::HARIAN:
                $end = Carbon::parse($startDate)->addDays($duration);
                return $subDay ? $end->subDay() : $end;
            default:
                return false;
        }
    }
}

if (!function_exists('get_rate_category')) {
    function get_rate_category()
    {
        $currentTime = date('H:i:s');
        switch ($currentTime){
            case $currentTime >= '07:00:00' && $currentTime < '13:00:00':
                return "A";

            case $currentTime >= '13:00:00' && $currentTime < '17:00:00':
                return "B";

            case $currentTime >= '17:00:00' && $currentTime < '21:00:00':
                return "C";

            case $currentTime >= '21:00:00' && $currentTime < '24:00:00':
                return "D";

            default:
                return false;
        }
    }
}

if (!function_exists('parse_day')) {
    function parse_day($date) {
        return Carbon::parse($date)->format('d');
    }
}

if (!function_exists('parse_date_day')) {
    function parse_date_day($date = null, $nullOperator = '-') {
        if(! $date) {
            return $nullOperator;
        }
        \Carbon\Carbon::setLocale('id');
        $date2 = Carbon::parse($date);
        $date3 = parse_date_full($date);

        return $date2->translatedFormat('l, ') . $date3;
    }
}

if (!function_exists('parse_date_only_date')) {
    function parse_date_only_date($date) {
        if (empty($date)){
            return '';
        }
        \Carbon\Carbon::setLocale('id');

        $date = \Carbon\Carbon::parse($date);

        return $date->format('d').' '.get_month_name($date->format('m')).' '.$date->format('Y');
    }
}

if (!function_exists('parse_date_numeric')) {
    function parse_date_numeric($date) {
        if (empty($date)){
            return '';
        }
        \Carbon\Carbon::setLocale('id');

        $date = \Carbon\Carbon::parse($date);
        $time = parse_time_hm($date);

        return $date->format('d').'/'.$date->format('m').'/'.$date->format('Y'). " Pukul " . $time;
    }
}

if (!function_exists('display_standarized_date_time')) {
    function display_standarized_date_time($date, $inline = false) {
        if (!$date) {
            echo "";
            return;
        }
        $tanggal = parse_date_day_new($date);
        $time = parse_time_hm($date);
        if ($inline) {
            echo $tanggal. " Pukul ". $time;
        } else {
            echo $tanggal. "<br /> Pukul ". $time;
        }
    }
}

if (!function_exists('display_standarized_date_time_version_2')) {
    function display_standarized_date_time_version_2($date, $inline = false) {
        if (!$date) {
            echo "";
        }
        $tanggal = parse_date_day_new($date);
        $time = parse_time_hm($date);
        if ($inline) {
            echo $tanggal. " | ". $time;
        } else {
            echo $tanggal. "<br /> | ". $time;
        }
    }
}

if (!function_exists('display_standarized_date_time_controller')) {
    function display_standarized_date_time_controller($date, $inline = false) {
        if (!$date) {
            return "";
        }
        $tanggal = parse_date_day_new($date);
        $time = parse_time_hm($date);
        if ($inline) {
            return $tanggal. " Pukul ". $time;
        } else {
            return $tanggal. "<br /> Pukul ". $time;
        }
    }
}

if (!function_exists('display_standarized_date_time_mobile')) {
    function display_standarized_date_time_mobile($date, $inline = false) {
        if (!$date) {
            return "";
        }
        $tanggal = parse_date_day_new($date);
        $time = parse_time_hm($date);
        if ($inline) {
            return $tanggal. " Pukul ". $time;
        } else {
            return $tanggal. " | ". $time;
        }
    }
}

if (!function_exists('parse_date_format')) {
    function parse_date_format($date) {
        return Carbon::parse($date)->format('Y-m-d');
    }
}

if (!function_exists('get_closing_date')) {
    function get_closing_date()
    {
        $date = Closing::query()
            ->latest('date')
            ->first();

        if($date) {
            return Carbon::parse($date->date)->format('Y-m-d');
        }

        return '';
    }
}

if (!function_exists('week_of_month')) {
    function week_of_month($qDate){
        $dt = strtotime($qDate);
        $day  = date('j',$dt);
        $month = date('m',$dt);
        $year = date('Y',$dt);
        $totalDays = date('t',$dt);
        $weekCnt = 1;
        $retWeek = 0;
        for($i=1;$i <= $totalDays;$i++) {
            $curDay = date("N", mktime(0,0,0,$month,$i,$year));
            if($curDay==7) {
                if($i==$day) {
                    $retWeek = $weekCnt+1;
                }
                $weekCnt++;
            } else {
                if($i==$day) {
                    $retWeek = $weekCnt;
                }
            }
        }
        return $retWeek;
    }
}

if (!function_exists('get_day')) {
    function get_day($date) {
        \Carbon\Carbon::setLocale('id');

        $date = \Carbon\Carbon::parse($date);

        return $date->translatedFormat('l');
    }
}

if (!function_exists('get_days')) {
    function get_days() {
        return array(
            'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'
        );
    }
}

if (!function_exists('translate_duration')) {
    function translate_duration(string $date, string $durationType) : string
    {
        switch ($durationType){
            case \App\Constants\DurationType::HARIAN:
                if(is_weekend($date))
                    return 'price_daily_we';

                return 'price_daily_wd';
            case \App\Constants\DurationType::MINGGUAN:
                return 'price_weekly';
            case \App\Constants\DurationType::BULANAN:
                return 'price_monthly';
        }
    }
}

if (!function_exists('second_to_time')) {
    function second_to_time($seconds) {
        if (isset($seconds)) {
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            $seconds = $seconds % 60;

            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }
    }
}

if (!function_exists('split_month_into_weeks')){
    function split_month_into_weeks($year, $month)
    {
        $totalDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $weeks = [];
        $startDay = 1;

        for ($week = 1; $week <= 4; $week++) {
            $endDay = $startDay + 6;

            if ($endDay > $totalDays) {
                $endDay = $totalDays;
            }

            $weeks[] = [
                'week' => $week,
                'start' => $startDay,
                'end' => $endDay,
            ];

            $startDay = $endDay + 1;

            if ($startDay > $totalDays) {
                break;
            }
        }

        return $weeks;
    }
}

if (!function_exists('parse_int_to_month')) {
    function parse_int_to_month($val) {
        $month = '';

        switch($val)
        {
            case 1:
                $month = 'JANUARI';
                break;
            case 2:
                $month = 'FEBRUARI';
                break;
            case 3:
                $month = 'MARET';
                break;
            case 4:
                $month = 'APRIL';
                break;
            case 5:
                $month = 'MEI';
                break;
            case 6:
                $month = 'JUNI';
                break;
            case 7:
                $month = 'JULI';
                break;
            case 8:
                $month = 'AGUSTUS';
                break;
            case 9:
                $month = 'SEPTEMBER';
                break;
            case 10:
                $month = 'OKTOBER';
                break;
            case 11:
                $month = 'NOVEMBER';
                break;
            case 12:
                $month = 'DESEMBER';
                break;
        }

        return $month;

    }
}

if (!function_exists('parse_str_to_month')) {
    function parse_str_to_month($val) {
        $month = '';

        switch($val)
        {
            case 'JANUARI':
                $month = 1;
                break;
            case 'FEBRUARI':
                $month = 2;
                break;
            case 'MARET':
                $month = 3;
                break;
            case 'APRIL':
                $month = 4;
                break;
            case 'MEI':
                $month = 5;
                break;
            case 'JUNI':
                $month = 6;
                break;
            case 'JULI':
                $month = 7;
                break;
            case 'AGUSTUS':
                $month = 8;
                break;
            case 'SEPTEMBER':
                $month = 9;
                break;
            case 'OKTOBER':
                $month = 10;
                break;
            case 'NOVEMBER':
                $month = 11;
                break;
            case 'DESEMBER':
                $month = 12;
                break;
        }

        return $month;

    }
}

if (!function_exists('parse_date_generic')) {
    function parse_date_generic($date) {
        if (empty($date)){
            return '';
        }
        \Carbon\Carbon::setLocale('id');

        $date = \Carbon\Carbon::parse($date);

        return $date->format('d').'/'.$date->format('m').'/'.$date->format('Y');
    }
}
