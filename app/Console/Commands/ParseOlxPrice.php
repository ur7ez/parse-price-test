<?php

namespace App\Console\Commands;

use App\Services\Contracts\ParserServiceInterface;
use Illuminate\Console\Command;

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

    private array $urls = [
//        'https://www.dfdggryty.ua/dosdsdsd.html',
//        'https://www.olx.ua/d/uk/obyavlenie/warhIDDEZKk.html',

        'https://www.olx.ua/d/uk/obyavlenie/warhammer-40000-varhammer-abnett-inkvizitor-reyvenor-vsya-trilogiya-IDDEZKk.html',
        'https://www.olx.ua/d/uk/obyavlenie/moncler-leersie-novaya-kollektsiya-pyshneyshiy-meh-lisy-IDV0qTe.html',
        'https://www.olx.ua/d/uk/obyavlenie/bomber-ma-1-camo-rap-opium-IDVSs5d.html',
    ];

    /**
     * Execute the console command.
     * @throws \Throwable
     */
    public function handle()
    {
        $method = $this->option('method')
            ?? config('parser.default_method'); // use default if not provided

        try {
            $service = $this->_resolveService($method);
        } catch (\InvalidArgumentException $e) {
            $this->fail("Unknown parsing method: $method");
        }

        $this->info("Parsing prices with $method...");
        $prices = $service->parsePrice($this->urls);

        if (empty($prices)) {
            $this->info("Could not retrieve any prices");
            return 2;
        }
        // print the results:
        $this->info("Prices parsed successfully:");
        $this->table(['URL', 'Price, UAH'], $prices);
        logger()->info("Ad data per url:\n" . print_r($service->getAdData(), true));  // TODO: debug
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
}
