<?php

namespace App\Services;

use App\Models\EmissionFactor;

/**
 * Perhitungan emisi otomatis (KF-20): jumlah aktivitas x faktor emisi.
 */
class EmissionCalculator
{
    public function calculate(EmissionFactor $factor, float $amount): float
    {
        return round($amount * (float) $factor->factor_value, 4);
    }
}
