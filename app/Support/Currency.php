<?php

namespace App\Support;

class Currency
{
    public static function format(float|int|string $amount, string $symbol = 'KSh'): string
    {
        return $symbol.' '.number_format((float) $amount, 2);
    }
}
