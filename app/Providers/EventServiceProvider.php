<?php

namespace App\Providers;

use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Command starting time
     */
    public float|string $starting_time;

    /**
     * Command finished time
     */
    public float|string $finished_time;

    /**
     * The event to listener mappings for the application.
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot(): void
    {
        Event::listen(CommandStarting::class, function (CommandStarting $event) {
            $this->starting_time = microtime(true);
        });

        Event::listen(CommandFinished::class, function (CommandFinished $event) {
            $this->finished_time = microtime(true);
            $time = ($this->finished_time - $this->starting_time); // time in seconds
            // here you can store, display or log time for future use.
            Log::info(sprintf("Command [%s] takes %01.2f minutes (%01.4f seconds).", $event->input, $time / 60, $time));
        });
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
