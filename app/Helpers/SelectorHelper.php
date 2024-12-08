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
        'ad_data' => [
            'css' => "script[type='application/ld+json'][data-rh='true']",
            'xpath' => "//script[@type='application/ld+json' and @data-rh='true']",
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

    /**
     * @param string $type
     * @return string
     */
    public static function getAdDataSelector(string $type = 'css'): string
    {
        return self::SELECTORS['ad_data'][$type]
            ?? throw new \InvalidArgumentException("Invalid selector type: $type");
    }

    public static function isAdDataValid(array $adData): bool
    {
        return isset($adData['offers']['price']);
    }

    public static function getPriceFromAdData(array $adData): ?int
    {
        return $adData['offers']['price'] ?? null;
    }

}
