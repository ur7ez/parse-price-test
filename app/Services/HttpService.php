<?php

namespace App\Services;

use App\Services\Contracts\ParserServiceInterface;
use App\Helpers\PriceHelper;
use App\Helpers\SelectorHelper;
use App\Helpers\UrlHelper;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

/**
 * Use if the data is embedded in the server-generated HTML.
 */
class HttpService implements ParserServiceInterface
{
    public Client $httpClient;
    protected array $clientOptions = [];
    protected string $priceSelector;
    protected string $dataSelector;
    private array $_adData = [];  // ad data from ld+json script

    /**
     * Parse OLX price vis HTTP service
     */
    public function __construct()
    {
        $this->httpClient = new Client();
        $this->clientOptions = [
            RequestOptions::HEADERS => [
                'User-Agent' => config('parser.http.user_agent'),
            ],
        ];
        $this->priceSelector = SelectorHelper::getPriceSelector('xpath');
        $this->dataSelector = SelectorHelper::getAdDataSelector('xpath');
    }

    /**
     * @param array $urls
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function parsePrice(array $urls): array
    {
        $prices = [];
        foreach ($urls as $url) {
            $this->_adData[$url] = null;
            if (!UrlHelper::isValid($url)) {
                $prices[] = [$url, config('parser.placeholders.invalid_url')];
                continue;
            }
            try {
                $prices[] = [$url, $this->_parseUsingHttp($url)];
            } catch (\Exception $e) {
                $prices[] = [$url, config('parser.placeholders.price_not_found')];
                logger()->error("Error for `$url`: " . $e->getMessage());
            }
        }
        return $prices;
    }

    /**
     * @param string $url
     * @return float|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     */
    private function _parseUsingHttp(string $url): float|string
    {
        $response = $this->httpClient->get($url, $this->clientOptions);
        // get HTML content:
        $html = $response->getBody()->getContents();
        // Load the HTML into DOMDocument
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $xPath = new \DOMXPath($dom);

        // get ad data (and price) via script ld+json tag
        $scriptTag = $xPath->query($this->dataSelector);
        if ($scriptTag && $scriptTag->length > 0) {
            $ldJsonContent = $scriptTag->item(0)->nodeValue;
            try {
                // store ad data
                $adData = json_decode($ldJsonContent, true, 512, JSON_THROW_ON_ERROR);
                $this->_adData[$url] = $adData;

                return $adData['offers']['price']
                    ?? config('parser.placeholders.price_not_found');
            } catch (\JsonException $e) {
                logger()->error("Error getting ad data from `$url`: " . $e->getMessage());
            }
        }

        // alternative way: get price directly on page content
        /*$nodes = $xPath->query($this->priceSelector);
        if ($nodes && $nodes->length > 0) {
            $priceText = $nodes->item(0)->textContent;
            return PriceHelper::convertToFloat($priceText);
        }*/

        logger()->warning("No element with given selector found for url `$url`");
        return config('parser.placeholders.price_not_found');
    }

    /**
     * get ad page ld+json script data
     * @return array
     */
    public function getAdData(): array
    {
        return $this->_adData;
    }
}
