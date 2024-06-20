<?php

if (!function_exists('convert_to_english_numbers')) {

    function convert_to_english_numbers(string|int $number): string
    {
        return strtr((string)$number, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
    }
}

if (!function_exists('normalize_card')) {
    function normalize_card(string|int $card): int
    {
        $normalized = convert_to_english_numbers($card);
        $normalized = preg_replace('/\D/', '', $normalized);
        return (int)$normalized;
    }
}

if (!function_exists('normalize_amount')) {

    function normalize_amount(string|float $amount): float
    {
        $amount = convert_to_english_numbers($amount);
        $amount = preg_replace('/[^\d.,]/', '', $amount);
        return (float)$amount;
    }
}

