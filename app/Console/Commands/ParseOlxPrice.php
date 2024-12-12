<?php

namespace App\Console\Commands;

use App\Helpers\SelectorHelper;
use App\Mail\PriceChanged;
use App\Models\PriceNotification;
use App\Models\Subscriber;
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
     * @var array $notifications
     * [ [subscriber_id] => [ 'url_id', 'url', 'previous_price', 'current_price', 'parsed_at'] ]
     */
    protected array $notifications = [];

    /**
     * @param UrlPrice $urlPrice
     */
    public function __construct(protected UrlPrice $urlPrice)
    {
        parent::__construct();
        // collect valid unique URLs to be parsed
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

        // print main results in console:
        // $this->table(['URL', 'Price, UAH'], $parsedPrices);

        // Process subscriptions and collect data for subscribers notifications:
        $this->_processSubscriptions($parserService->getAdData());

        // send email notifications to subscribers:
        $this->notifySubscribers();
        $this->info("Prices monitoring completed.");
        return 0;
    }

    /**
     * Process subscriptions and collect data for subscribers notifications
     * @param array $adDataPerUrl
     * @return void
     * @throws \JsonException
     */
    private function _processSubscriptions(array $adDataPerUrl): void
    {
        // Iterate over valid URLs from the query results
        foreach ($this->validUrls as $urlPrice) {
            /** @var UrlPrice $urlPrice */
            $url = $urlPrice->url;
            // Check if this URL has ad data in the response
            if (!isset($adDataPerUrl[$url])) {
                $this->warn("No data returned from parser service for URL: $url");
                // process only valid advert URLs, so
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
                'ad_data' => json_encode($priceData, JSON_THROW_ON_ERROR),
            ]);

            // Prepare data to notify subscribers if price has changed
            if ((float)$prevPrice !== (float)$currentPrice) {
                foreach ($urlPrice->subscribers as $subscriber) {
                    $this->notifications[$subscriber->id][] = [
                        'url_id' => $urlPrice->id,
                        'url' => $url,
                        'previous_price' => $prevPrice,
                        'current_price' => $currentPrice,
                        'parsed_at' => $urlPrice->parsed_at,
                    ];
                }
            }
        }
    }

    protected function notifySubscribers(): void
    {
        // send emails in bulk after processing
        foreach ($this->notifications as $subscriberId => $changes) {
            $subscriber = Subscriber::find($subscriberId);
            if (!$subscriber) {
                logger()->warning("Subscriber with ID `{$subscriberId}` not found");
                continue;
            }
            // Prepare email content
            $emailContent = view('emails.price_changed', [
                'priceChanges' => $changes,
                'subscriberLogin' => $subscriber->getEmailLogin(),
            ])->render();
            try {
                // queue email notification
                Mail::to($subscriber->email)
                    ->queue(new PriceChanged($changes, $subscriber->getEmailLogin()));
                logger()->info("Price change notification to `{$subscriber->email}` put in queue.");
            } catch (\Exception $e) {
                logger()->error("Failed to queue email for subscriber `{$subscriber->email}`. Error: " . $e->getMessage());
            }

            // save the notification
            $notification = PriceNotification::create([
                'subscriber_id' => $subscriber->id,
                'notification_content' => $emailContent,
                'queued_at' => now(),
            ]);

            // update subscriptions
            $subscriber->subscriptions()
                ->whereIn('url_price_id', array_column($changes, 'url_id'))
                ->update(['last_price_notification_id' => $notification->id]);
        }
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
}
