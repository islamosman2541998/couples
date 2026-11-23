<?php

namespace App\Support;

class Money
{
    /** "700" for whole amounts, "199.50" otherwise. */
    public static function egp(float|int|string|null $amount): string
    {
        $value = round((float) $amount, 2);

        return fmod($value, 1.0) === 0.0 ? number_format($value, 0, '.', ',') : number_format($value, 2, '.', ',');
    }
}
