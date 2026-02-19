<?php

namespace App\Services;

use App\Http\Controllers\Controller;
use App\Models\Picture;
use App\Models\UnitConversion;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Svg\Tag\Image;

class UnitConversionService extends Controller
{
    public static function toBase(float $qty, int $fromUnitId, int $baseUnitId, int $outletId): float {
        if ($fromUnitId === $baseUnitId) {
            return $qty;
        }

        $conversion = UnitConversion::where('outlet_id', $outletId)
            ->where('from_unit_id', $fromUnitId)
            ->where('to_unit_id', $baseUnitId)
            ->where('is_active', true)
            ->first();

        if (!$conversion) {
            throw new \Exception('Konversi unit belum diset untuk outlet ini');
        }

        if ($conversion->multiplier <= 0) {
            throw new \Exception('Multiplier unit tidak valid');
        }

        return round($qty * $conversion->multiplier, 6);
    }
}
