<?php

namespace App\Support;

class Phone
{
    /** wa.me link for an Egyptian mobile (01xxxxxxxxx, +20…, 0020…), or null when the number is unusable. */
    public static function whatsapp(?string $phone, ?string $text = null): ?string
    {
        $phone = strtr((string) $phone, ['٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9']);
        $digits = preg_replace('/\D+/', '', $phone);
        $digits = preg_replace('/^00/', '', $digits);

        if (preg_match('/^01\d{9}$/', $digits)) {
            $digits = '2'.$digits;
        }

        if (! preg_match('/^\d{10,15}$/', $digits)) {
            return null;
        }

        return 'https://wa.me/'.$digits.($text !== null ? '?text='.rawurlencode($text) : '');
    }
}
