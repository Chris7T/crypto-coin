<?php

namespace App\Providers;

use App\Gateways\CoinGeckoClientGateWay;
use App\Gateways\CoinGeckoClientGateWayInterface;
use Illuminate\Support\ServiceProvider;

class CoinGeckoApiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CoinGeckoClientGateWayInterface::class, CoinGeckoClientGateWay::class);
    }
}
