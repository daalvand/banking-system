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
    function normalize_card(string|int $card): string
    {
        return convert_to_english_numbers($card);
    }
}

if (!function_exists('normalize_amount')) {

    function normalize_amount(string|float|int $amount): string
    {
        return convert_to_english_numbers($amount);
    }
}


if (!function_exists('is_valid_card')) {
    function is_valid_card(int|string $cardNo): bool
    {
        $strCardNo = (string)$cardNo;
        if (strlen($strCardNo) !== 16 || !ctype_digit($strCardNo)) {
            return false;
        }
        $sum = 0;
        foreach (str_split($strCardNo) as $index => $value) {
            $value = (int)$value;
            if ($index % 2 === 0) {
                $sum += (($value * 2 > 9) ? ($value * 2) - 9 : ($value * 2));
            } else {
                $sum += $value;
            }
        }
        return $sum % 10 === 0;
    }
}


if (!function_exists('card_generator')) {
    function card_generator(): int
    {
        $card = null;
        if (!$card || $card > 10 ** 16 - 1 || $card < 10 ** 15) {
            $card = random_int(10 ** 15, 10 ** 16 - 1);
        }
        while (true) {
            if (is_valid_card($card)) {
                return $card;
            }
            $card++;
        }
    }
}
