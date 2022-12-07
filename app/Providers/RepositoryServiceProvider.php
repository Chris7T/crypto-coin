<?php

namespace App\Providers;

use App\Repositories\CryptoCoinEloquentRepository;
use App\Repositories\CryptoCoinInterfaceRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CryptoCoinInterfaceRepository::class, CryptoCoinEloquentRepository::class);
    }
}
