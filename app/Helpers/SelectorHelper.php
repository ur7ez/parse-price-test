<?php

namespace App\Helpers;

/**
 * OLX advert price html element css- or xpath- selectors
 */
class SelectorHelper
{
    public const SELECTORS = [
        'price' => [
            'css' => '[data-testid="ad-price-container"] > h3',
            'xpath' => '//*[@data-testid="ad-price-container"]/h3',
        ],
    ];

    /**
     * @param string $type
     * @return string
     */
    public static function getPriceSelector(string $type = 'css'): string
    {
        return self::SELECTORS['price'][$type]
            ?? throw new \InvalidArgumentException("Invalid selector type: $type");
    }
}
