<?php

namespace App\Gateways;

use Codenixsv\CoinGeckoApi\CoinGeckoClient;
use Illuminate\Support\Carbon;

class CoinGeckoClientGateWay implements CoinGeckoClientGateWayInterface
{
    public function __construct(
        private readonly CoinGeckoClient $coinGeckoClient
    ) {
    }

    public function getCurrentCoinPrice(string $coinId): array
    {
        return $this->coinGeckoClient->simple()->getPrice($coinId, 'usd');
    }

    public function getCoinPriceByEstimatedDate(string $coinId, string $date): array
    {
        return $this->coinGeckoClient->coins()->getHistory($coinId, Carbon::parse($date)->format('d-m-Y'));
    }
}
