<?php

namespace App\Providers;

use Codenixsv\CoinGeckoApi\CoinGeckoClient;
use Illuminate\Support\ServiceProvider;

class CoinGeckoClientProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CoinGeckoClient::class, function ($app) {
            return new CoinGeckoClient();
        });
    }
}
