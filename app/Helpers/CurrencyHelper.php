<?php

if (!function_exists('idr_format')) {
    function idr_format($price, $zeroValue = 'IDR 0')
    {
        return (int) $price != 0 ? "IDR ". number_format($price,0,',','.') : $zeroValue;
    }
}

if (!function_exists('rp_format')) {
    function rp_format($price = 0, $zeroValue = 'Rp. 0')
    {
        return (int) $price != 0 ? "Rp ". number_format($price,0,',','.') : $zeroValue;
    }
}

if (!function_exists('nominal_format')) {
    function nominal_format($price = 0, $zeroValue = '0')
    {
        return (int) $price != 0 ? number_format($price,0,',','.') : $zeroValue;
    }
}

if (!function_exists('rp_format_decimal')) {
    function rp_format_decimal($price = 0, $decimal = 0, $zeroValue = 'Rp. 0')
    {
        return (int) $price != 0 ? "Rp ". number_format($price,$decimal,',','.') : $zeroValue;
    }
}

if (!function_exists('demask_input_integer')) {
    function demask_input_integer(string $value) : int
    {
        return (int) str_replace(',', '', $value);
    }
}

if (!function_exists('currency_format')) {
    function currency_format($price)
    {
        return (int) $price != 0 ? number_format($price,0,',','.') : $price;
    }
}

if (!function_exists('rp_format_mixed')) {
    function rp_format_mixed($price)
    {
        return (int) $price != 0 ? 'Rp'.number_format($price,0,',','.') : $price;
    }
}

if (!function_exists('terbilang')) {
    function terbilang($nilai)
    {
        if($nilai<0) {
            $hasil = "minus ". trim(penyebut($nilai));
        } else {
            $hasil = trim(penyebut($nilai));
        }
        return $hasil .' rupiah';
    }

    function penyebut($nilai) {
        $nilai = abs($nilai);
        $huruf = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
        $temp = "";
        if ($nilai < 12) {
            $temp = " ". $huruf[$nilai];
        } else if ($nilai <20) {
            $temp = penyebut($nilai - 10). " belas";
        } else if ($nilai < 100) {
            $temp = penyebut($nilai/10)." puluh". penyebut($nilai % 10);
        } else if ($nilai < 200) {
            $temp = " seratus" . penyebut($nilai - 100);
        } else if ($nilai < 1000) {
            $temp = penyebut($nilai/100) . " ratus" . penyebut($nilai % 100);
        } else if ($nilai < 2000) {
            $temp = " seribu" . penyebut($nilai - 1000);
        } else if ($nilai < 1000000) {
            $temp = penyebut($nilai/1000) . " ribu" . penyebut($nilai % 1000);
        } else if ($nilai < 1000000000) {
            $temp = penyebut($nilai/1000000) . " juta" . penyebut($nilai % 1000000);
        } else if ($nilai < 1000000000000) {
            $temp = penyebut($nilai/1000000000) . " milyar" . penyebut(fmod($nilai,1000000000));
        } else if ($nilai < 1000000000000000) {
            $temp = penyebut($nilai/1000000000000) . " trilyun" . penyebut(fmod($nilai,1000000000000));
        }
        return $temp;
    }

    function get_alphabet($number)
    {
        $alphabet = array('a', 'b', 'c', 'd', 'e',
                       'f', 'g', 'h', 'i', 'j',
                       'k', 'l', 'm', 'n', 'o',
                       'p', 'q', 'r', 's', 't',
                       'u', 'v', 'w', 'x', 'y',
                       'z'
                   );

        return $alphabet[$number];
    }
}
