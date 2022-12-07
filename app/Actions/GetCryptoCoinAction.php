<?php

namespace App\Actions;

use App\Enums\CryptoCoinEnum;
use App\Repositories\CryptoCoinInterfaceRepository;
use Illuminate\Support\Facades\Cache;

class GetCryptoCoinAction
{
    public function __construct(
        private readonly CryptoCoinInterfaceRepository $cryptoCoinRepository
    ) {
    }

    public function execute(string $coinId, string $consultedAt): ?array
    {
        return Cache::remember(
            "crypto-coin-{$coinId}-consulted_at-{$consultedAt}",
            config('cache.time.one_day'),
            function () use ($coinId, $consultedAt) {
                $coinName = CryptoCoinEnum::from($coinId)->name;
                $cryptoCoin = $this->cryptoCoinRepository->getByCoinName($coinName, $consultedAt);

                return is_null($cryptoCoin) ? [] : $cryptoCoin->toArray();
            }
        );
    }
}
