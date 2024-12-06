<?php

namespace App\Helpers;

class PriceHelper
{
    /**
     * Convert price as text into float
     * @param string $priceText
     * @return float|null
     */
    public static function convertToFloat(string $priceText): ?float
    {
        $newPriceText = html_entity_decode($priceText, ENT_QUOTES | ENT_HTML5, 'UTF-8');  // decode from html
        // delete symbols except for numbers, dots and commas
        $cleaned = trim(preg_replace('/[^\d.,]/', '', $newPriceText), ',.');

        // if there is dot and comma
        if (str_contains($cleaned, ',') && str_contains($cleaned, '.')) {
            $cleaned = str_replace(',', '', $cleaned);
        } elseif (str_contains($cleaned, ',')) {
            $cleaned = str_replace(',', '.', $cleaned);
        }

        return is_numeric($cleaned)
            ? (float)$cleaned
            : sprintf('%s [%s]', config('parser.unknown_price_format'), $priceText);
    }
}
