<?php

namespace App\Providers;

use App\Services\Contracts\ParserServiceInterface;
use Illuminate\Support\ServiceProvider;

class ParserServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Bind the default parser service
        $this->app->bind(ParserServiceInterface::class, function ($app) {
            $defaultMethod = config('parser.default_method');  // only default method
            $serviceClass = config("parser.methods.$defaultMethod");

            if (!$serviceClass) {
                throw new \InvalidArgumentException("Unknown parser method: $defaultMethod");
            }

            return $app->make($serviceClass);
        });
    }
}
