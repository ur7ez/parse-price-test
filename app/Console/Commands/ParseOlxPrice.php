<?php

namespace App\Console\Commands;

use App\Services\HttpService;
use App\Services\SeleniumService;
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
    protected $description = 'Porse OLX advert price via Selenium or HTTP client';

    private array $urls = [
        'https://www.dfdggryty.ua/dosdsdsd.html',
        'https://www.olx.ua/d/uk/obyavlenie/warhIDDEZKk.html',
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
        $method = $this->option('method');  // parsing method

        if ($method === 'selenium') {
            $this->info('Parse price with Selenium...');
            $seleniumService = new SeleniumService();
            $prices = $seleniumService->parsePrice($this->urls);
        } elseif ($method === 'http') {
            $this->info('Parse with HTTP-client...');
            $httpService = new HttpService();
            $prices = $httpService->parsePrice($this->urls);
        } else {
            $this->fail("Unknown parsing method: $method");
        }
        if (empty($prices)) {
            $this->info("Could not retrieve any prices");
            return 2; // error code
        }
        // print the results:
        $this->table(['URL', 'Price, UAH'], $prices);
        return 0;
    }
}
