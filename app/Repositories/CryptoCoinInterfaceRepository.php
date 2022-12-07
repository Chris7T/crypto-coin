<?php

namespace App\Repositories;

use App\Models\CryptoCoin;

interface CryptoCoinInterfaceRepository
{
    public function register(string $coinId, string $name, string $price, string $consultedAt): CryptoCoin;

    public function getByCoinName(string $coinName, string $consultedAt): ?CryptoCoin;
}
