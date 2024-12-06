<?php

namespace App\Services;

use App\Helpers\PriceHelper;
use App\Helpers\SelectorHelper;
use App\Helpers\UrlHelper;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class HttpService
{
    public Client $httpClient;
    protected $clientOptions = [
        RequestOptions::HEADERS => [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.5790.110 Safari/537.36',
        ],
    ];

    /**
     * Parse OLX price vis HTTP service
     */
    public function __construct()
    {
        $this->httpClient = new Client();
    }

    /**
     * @param array $urls
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function parsePrice(array $urls = []): array
    {
        $prices = [];
        foreach ($urls as $url) {
            if (!UrlHelper::isValid($url)) {
                $prices[$url] = null;
                continue;
            }
            try {
                $prices[$url] = $this->_parsePriceUsingHttp($url);
            } catch (\Exception $e) {
                logger()->error("Error for `$url`: " . $e->getMessage());
            }
        }
        return $prices;
    }

    /**
     * @param string $url
     * @return float|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function _parsePriceUsingHttp(string $url): ?float
    {
        // synchronous request
        $response = $this->httpClient->get($url, $this->clientOptions);

        $html = $response->getBody()->getContents();

        $dom = new \DOMDocument();
        @$dom->loadHTML($html);

        $xpath = new \DOMXPath($dom);
        $xpathSelector = SelectorHelper::getPriceSelector('xpath');
        $nodes = $xpath->query($xpathSelector);

        if ($nodes->length > 0) {
            $priceText = trim($nodes->item(0)->textContent);
            return PriceHelper::convertToFloat($priceText);
        }

        return null;
    }
}
