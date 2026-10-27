<?php

if (! function_exists('format_number')) {
    /**
     * Format angka dengan pemisah ribuan titik dan desimal koma.
     */
    function format_number(float|int|string|null $value, int $decimals = 2): string
    {
        return number_format((float) $value, $decimals, ',', '.');
    }
}

if (! function_exists('format_kg')) {
    function format_kg(float|int|string|null $value, int $decimals = 2): string
    {
        return format_number($value, $decimals).' kg CO₂e';
    }
}
