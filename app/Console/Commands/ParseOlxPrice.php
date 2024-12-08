<?php

namespace App\Console\Commands;

use App\Helpers\SelectorHelper;
use App\Mail\PriceChanged;
use App\Models\Subscription;
use App\Services\Contracts\ParserServiceInterface;
use Illuminate\Console\Command;
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

    protected array $urls = [
        'https://www.dfdggryty.ua/dosdsdsd.html',
        'https://www.olx.ua/d/uk/obyavlenie/warhIDDEZKk.html',

        'https://www.olx.ua/d/uk/obyavlenie/warhammer-40000-varhammer-abnett-inkvizitor-reyvenor-vsya-trilogiya-IDDEZKk.html',
        'https://www.olx.ua/d/uk/obyavlenie/moncler-leersie-novaya-kollektsiya-pyshneyshiy-meh-lisy-IDV0qTe.html',
        'https://www.olx.ua/d/uk/obyavlenie/bomber-ma-1-camo-rap-opium-IDVSs5d.html',
    ];

    public function __construct(Subscription $subscription)
    {
        parent::__construct();
        // Collect unique URLs
        $this->urls = $subscription::distinct()->pluck('url')->toArray();
    }

    /**
     * Execute the console command.
     * @throws \Throwable
     */
    public function handle()
    {
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

        if (empty($parsedPrices)) {
            $this->info("Could not retrieve any prices");
            return 2;
        }

        // Process subscriptions
        $this->_processSubscriptions($parserService->getAdData());

        // print the results:
        //$this->table(['URL', 'Price, UAH'], $parsedPrices);
        // logger()->info("Ad data per url:\n" . print_r($parserService->getAdData(), true));

        $this->info("Prices monitoring complete.");
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
        // Iterate over all subscriptions
        Subscription::all()->each(function ($subscription) use ($adDataPerUrl) {
            $url = $subscription->url;
            $adData = $adDataPerUrl[$url] ?? [];
            $currentPrice = SelectorHelper::getPriceFromAdData($adData);

            if (empty($adData) || $currentPrice === null) {
                $this->info("No price found for URL: $url");
                return;
            }

            // Notify user if price has changed
            if ((float)$subscription->actual_price !== (float)$currentPrice) {
                $this->notifyUser($subscription, $currentPrice);
            }

            // Update the database
            $subscription->last_known_price = $subscription->actual_price;
            $subscription->actual_price = $currentPrice;
            $subscription->save();
        });
    }

    protected function notifyUser(Subscription $subscription, float $newPrice): void
    {
        // Dispatch email notification
        Mail::to($subscription->email)
            ->send(new PriceChanged($subscription->url, $newPrice));
    }
}
