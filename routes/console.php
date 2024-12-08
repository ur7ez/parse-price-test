<?php

use App\Console\Commands\ParseOlxPrice;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command(ParseOlxPrice::class)
    ->hourly()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/parse_olx_price.log'));

// to test command run:
// php artisan schedule:test --name=parse:olx-price
// php artisan schedule:work
