<?php

namespace App\Console\Commands;

use App\Helpers\SelectorHelper;
use App\Mail\PriceChanged;
use App\Models\UrlPrice;
use App\Services\Contracts\ParserServiceInterface;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Mail;

class ParseOlxPrice extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'parse:olx-price
                            {--M|method=http : parsing method (http (default) or selenium)}';
    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Porse OLX advert prices using the specified method';

    protected Collection $validUrls; // store valid URL query results
    protected array $urls = [];

    /**
     * @param UrlPrice $urlPrice
     */
    public function __construct(protected UrlPrice $urlPrice)
    {
        parent::__construct();
        // collect valid URLs to be parsed
        $this->validUrls = $this->urlPrice::validUrlsOnly()->get();
        $this->urls = $this->validUrls->pluck('url')->toArray();
    }

    /**
     * Execute the console command.
     * @return int
     * @throws \Throwable
     */
    public function handle(): int
    {
        if (empty($this->urls)) {
            $this->info('No URLs to parse.');
            return 0;
        }
        $method = $this->option('method')
            ?? config('parser.default_method'); // use default if not provided

        try {
            $parserService = $this->_resolveService($method);
        } catch (\InvalidArgumentException $e) {
            $this->fail("Unknown parsing method: $method");
        }

        $this->info("Parsing prices with $method...");
        // Parse prices for all unique URLs
        $parsedPrices = $parserService->parsePrice($this->urls);
        // print the results in console:
        //$this->table(['URL', 'Price, UAH'], $parsedPrices);

        // Process subscriptions
        $this->_processSubscriptions($parserService->getAdData());

        $this->info("Prices monitoring completed.");
        return 0;
    }

    /**
     * @param string $method
     * @return ParserServiceInterface
     */
    protected function _resolveService(string $method): ParserServiceInterface
    {
        $serviceClass = config("parser.methods.$method");
        if (!$serviceClass) {
            throw new \InvalidArgumentException("Unknown parsing method: $method");
        }

        return app($serviceClass);
    }

    private function _processSubscriptions(array $adDataPerUrl)
    {
        // Iterate over valid URLs from the query results
        foreach ($this->validUrls as $urlPrice) {
            /** @var UrlPrice $urlPrice */
            $url = $urlPrice->url;
            // Check if this URL has ad data in the response
            if (!isset($adDataPerUrl[$url])) {
                $this->warn("No data returned for URL: $url");
                continue;
            }

            $priceData = $adDataPerUrl[$url];
            $currentPrice = SelectorHelper::getPriceFromAdData($priceData);

            $prevPrice = $urlPrice->price;

            // Update the URL price record
            $urlPrice->update([
                'price' => $currentPrice,
                'is_valid' => $currentPrice !== null,
                'parsed_at' => now(),
                'ad_data' => $priceData,
            ]);
            // Notify user if price has changed
            if ((float)$prevPrice !== (float)$currentPrice) {
                $this->notifyUser($urlPrice, $prevPrice);
            }
        }
    }

    /**
     * @param UrlPrice $urlPrice
     * @param float|null $prevPrice
     * @return void
     */
    protected function notifyUser(UrlPrice $urlPrice, ?float $prevPrice): void
    {
        // Dispatch email notifications with queue
        foreach ($urlPrice->subscribers as $subscriber) {
            try {
                Mail::to($subscriber->email)
                    ->queue(new PriceChanged($urlPrice, $prevPrice));
                logger()->info("Price change notification sent to {$subscriber->email} for URL: {$urlPrice->url}");
            } catch (\Exception $e) {
                logger()->error("Failed to send email to {$subscriber->email} for URL: {$urlPrice->url}. Error: " . $e->getMessage());
            }
        }
    }
}
