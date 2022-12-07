<?php

namespace App\Actions;

use App\Enums\CryptoCoinEnum;
use App\Gateways\CoinGeckoClientGateWayInterface;
use App\Repositories\CryptoCoinInterfaceRepository;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class GetCoinPriceByEstimatedDateAction
{
    public function __construct(
        private readonly CoinGeckoClientGateWayInterface $coinGeckoClientGateWay,
        private readonly GetCryptoCoinAction $getCryptoCoinAction,
        private readonly CryptoCoinInterfaceRepository $cryptoCoinRepository
    ) {
    }

    public function execute(string $coinName, string $dateTime): array
    {
        $coinId = CryptoCoinEnum::from($coinName)->name;
        $cryptoCoin = $this->getCryptoCoinAction->execute($coinName, $dateTime);

        if (!empty($cryptoCoin)) {
            return $cryptoCoin;
        }
        $coinPriceResult = $this->coinGeckoClientGateWay->getCoinPriceByEstimatedDate($coinId, $dateTime);

        if (isset($coinPriceResult['error'])) {
            throw new UnprocessableEntityHttpException(config('messages.crypto_coin.name_invalid'));
        }
        $price = $coinPriceResult['market_data']['current_price']['usd'];
        $cryptoCoin = $this->cryptoCoinRepository->register($coinId, $coinName, $price, $dateTime)->toArray();

        Cache::put("crypto-coin-{$coinId}-consulted_at-{$dateTime}", $cryptoCoin, config('cache.time.one_month'));

        return $cryptoCoin;
    }
}
