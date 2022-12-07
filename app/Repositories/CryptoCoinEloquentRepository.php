<?php

namespace App\Repositories;

use App\Models\CryptoCoin;

class CryptoCoinEloquentRepository implements CryptoCoinInterfaceRepository
{
    public function __construct(
        private readonly CryptoCoin $model,
    ) {
    }

    public function register(string $coinId, string $name, string $price, string $consultedAt): CryptoCoin
    {
        return $this->model->create(
            [
                'coin_id' => $coinId,
                'price' => $price,
                'name' => $name,
                'consulted_at' => $consultedAt,
            ]
        );
    }

    public function getByCoinName(string $coinName, string $consultedAt): ?CryptoCoin
    {
        return $this->model->where('name', $coinName)
            ->firstWhere('consulted_at', $consultedAt);
    }
}
