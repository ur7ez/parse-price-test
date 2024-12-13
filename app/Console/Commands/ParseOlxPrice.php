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
            $this->info('No URLs ready to be parsed.');
            return 0;
        }
        $method = $this->option('method')
            ?? config('parser.default_method'); // use default if not provided
        try {
            $parserService = $this->_resolveService($method);
        } catch (\InvalidArgumentException $e) {
            $this->fail("Unknown parsing method: $method");
        }
        $this->info(sprintf("Parsing prices for %d URL(s) using %s service ...", count($this->urls), strtoupper($method)));

        // Parse prices for all unique URLs
        $parsedPrices = $parserService->parsePrice($this->urls);

        // print main results in console:
        // $this->table(['URL', 'Price, UAH'], $parsedPrices);

        // Process subscriptions and collect data for subscribers notifications:
        $this->_processSubscriptions($parserService->getAdsData());

        // send email notifications to subscribers:
        $this->notifySubscribers();
        $this->info("OLX advert prices monitoring completed. See system log for possible errors.");
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
                $this->warn("No data returned from parser service for URL: $url. Notification skipped.");
                // process only valid advert URLs, so
                continue;
            }

            $priceData = $adDataPerUrl[$url];  // array or string
            $currentPrice = is_array($priceData) ? SelectorHelper::getPriceFromAdData($priceData) : null;
            $prevPrice = $urlPrice->price;

            // Update the URL price record
            $urlPrice->update([
                'price' => $currentPrice,
                'is_valid' => $currentPrice !== null,
                'parsed_at' => now(),
                'ad_data' => json_encode($priceData, JSON_THROW_ON_ERROR),
            ]);

            // Prepare data to notify subscribers, only if advert price has been CHANGED
            // Note: if a user subscribed to existing URL for which price did not change, he will not receive notification. This is correct - by subscribing to some URLs, user wants to know when for some of his adverts change price, and as a result to receive a notification.
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
            } else {
                $this->info("No changes in price for URL `$url`. Notification skipped.");
            }
        }
    }

    /**
     * Notify subscribers if the changed prices in their subscriptions.
     * Send emails in bulk after processing.
     * @return void
     */
    protected function notifySubscribers(): void
    {
        $this->info(sprintf("Prepare to send notifications to %d subscriber(s).", count($this->notifications)));
        foreach ($this->notifications as $subscriberId => $changes) {
            $subscriber = Subscriber::find($subscriberId);
            if (!$subscriber) {
                $this->warn("Subscriber with ID `{$subscriberId}` not found");
                continue;
            }
            /** @var array $changes */
            // Prepare email content
            $emailContent = view('emails.price_changed', [
                'priceChanges' => $changes,
                'subscriberLogin' => $subscriber->getEmailLogin(),
            ])->render();

            // save the notification
            $notification = PriceNotification::create([
                'subscriber_id' => $subscriber->id,
                'data' => $emailContent,
                // 'queued_at' => now(),
            ]);

            try {
                // queue email notification
                Mail::to($subscriber->email)
                    ->queue(new PriceChanged($changes, $subscriber->getEmailLogin(), $notification->id));

                $this->info("Price change notification to `{$subscriber->email}` put in queue.");
            } catch (\Exception $e) {
                logger()->error("Failed to queue email for subscriber `{$subscriber->email}`. Error: " . $e->getMessage());
            }

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
