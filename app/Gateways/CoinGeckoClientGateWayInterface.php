<?php

namespace App\Gateways;

interface CoinGeckoClientGateWayInterface
{
    public function getCurrentCoinPrice(string $coinId): array;

    public function getCoinPriceByEstimatedDate(string $coinId, string $date): array;
}
