<?php

namespace App\Actions;

use App\Enums\CryptoCoinEnum;
use App\Gateways\CoinGeckoClientGateWayInterface;
use App\Repositories\CryptoCoinInterfaceRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class GetCurrentCoinPriceAction
{
    public function __construct(
        private readonly CoinGeckoClientGateWayInterface $coinGeckoClientGateWay,
        private readonly GetCryptoCoinAction $getCryptoCoinAction,
        private readonly CryptoCoinInterfaceRepository $cryptoCoinRepository
    ) {
    }

    public function execute(string $coinName): array
    {
        $coinId = CryptoCoinEnum::from($coinName)->name;
        $formatedDate = Carbon::now()->format('Y-m-d');
        $cryptoCoin = $this->getCryptoCoinAction->execute($coinName, $formatedDate);

        if (!empty($cryptoCoin)) {
            return $cryptoCoin;
        }
        $coinPriceResult = $this->coinGeckoClientGateWay->getCurrentCoinPrice($coinId);

        if (empty($coinPriceResult)) {
            throw new UnprocessableEntityHttpException(config('messages.crypto_coin.name_invalid'));
        }
        $price = $coinPriceResult[$coinId]['usd'];
        $cryptoCoin = $this->cryptoCoinRepository->register($coinId, $coinName, $price, $formatedDate)->toArray();

        Cache::put(
            "crypto-coin-{$coinId}-consulted_at-{$formatedDate}",
            $cryptoCoin,
            config('cache.time.one_day')
        );

        return $cryptoCoin;
    }
}
