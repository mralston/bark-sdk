<?php

namespace Mralston\Bark\Providers;

use Illuminate\Support\ServiceProvider;
use Mralston\Quake\Client;

class BarkServiceProvider extends ServiceProvider
{
    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->singleton(Client::class, function ($app) {
            return new Client(
                config('bark.client_id'),
                config('bark.secret'),
                config('bark.api_endpoint')
            );
        });

        $this->publishes([
            __DIR__.'/../../config/bark.php' => config_path('bark.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/bark.php', 'bark');
    }
}