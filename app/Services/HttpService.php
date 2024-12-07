<?php

namespace App\Services;

use App\Services\Contracts\ParserServiceInterface;
use App\Helpers\PriceHelper;
use App\Helpers\SelectorHelper;
use App\Helpers\UrlHelper;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class HttpService implements ParserServiceInterface
{
    public Client $httpClient;
    protected array $clientOptions = [];
    protected string $xpathSelector;

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
        $this->xpathSelector = SelectorHelper::getPriceSelector('xpath');
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
            if (!UrlHelper::isValid($url)) {
                $prices[] = [$url, config('parser.placeholders.invalid_url')];
                continue;
            }
            try {
                $prices[] = [$url, $this->_parsePriceUsingHttp($url)];
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
     */
    private function _parsePriceUsingHttp(string $url): float|string
    {
        // synchronous request
        $response = $this->httpClient->get($url, $this->clientOptions);

        $html = $response->getBody()->getContents();

        $dom = new \DOMDocument();
        @$dom->loadHTML($html);

        $xpath = new \DOMXPath($dom);
        $nodes = $xpath->query($this->xpathSelector);

        if ($nodes->length > 0) {
            $priceText = trim($nodes->item(0)->textContent);
            return PriceHelper::convertToFloat($priceText);
        }

        logger()->warning("No element with given selector found for url `$url`");
        return config('parser.placeholders.price_not_found');
    }
}
