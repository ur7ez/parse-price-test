<?php

use App\Console\Commands\ParseOlxPrice;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->everySixHours();

Schedule::command(ParseOlxPrice::class, ['--method=http'])
    ->hourly()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/parse_olx_price.log'));

// to test command run:
//          php artisan schedule:test --name="parse:olx-price --method=http"

// To run a scheduled worker locally, run
//          php artisan schedule:work
// This command will run in the foreground and invoke the scheduler every minute until you terminate the command.
// To run scheduled tasks once (without worker):
//          php artisan schedule:run
// Ensure that your queue worker is also running (ParseOlxPrice command queues emails):
//          php artisan queue:work
