<?php

namespace Tests\Feature;

use App\Enums\CryptoCoinEnum;
use Codenixsv\CoinGeckoApi\Api\Coins;
use Codenixsv\CoinGeckoApi\CoinGeckoClient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class PreviousTest extends TestCase
{
    private const ROUTE = 'crypto-coin.previous';

    public function setUp(): void
    {
        parent::setUp();
        $this->coinStub = $this->createMock(Coins::class);
        $this->coinGeckoClientStub = $this->createMock(CoinGeckoClient::class);
    }

    public function test_expected_true_when_route_exists()
    {
        $this->assertTrue(Route::has(self::ROUTE));
    }

    public function test_expected_unprocessable_entity_exception_when_coin_name_and_date_is_null()
    {
        $response = $this->getJson(route(self::ROUTE, ['coin_name' => null, 'date' => null]));

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['coin_name', 'date'])
            ->assertJson([
                'errors' => [
                    'coin_name' => ['The coin name field is required.'],
                    'date' => ['The date field is required.'],
                ],
            ]);
    }

    public function test_expected_unprocessable_entity_exception_when_coin_name_and_date_is_invalid()
    {
        $response = $this->getJson(route(self::ROUTE, ['coin_name' => 'invalid', 'date' => 'invalid']));

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['coin_name', 'date'])
            ->assertJson([
                'errors' => [
                    'coin_name' => ['The selected coin name is invalid.'],
                    'date' => ['The date does not match the format Y-m-d.'],
                ],
            ]);
    }

    public function test_expected_unprocessable_entity_exception_when_date_is_after_now()
    {
        $response = $this->getJson(route(self::ROUTE, ['coin_name' => 'bitcoin', 'date' => now()->addDay()->format('Y-m-d')]));

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['date'])
            ->assertJson([
                'errors' => [
                    'date' => ['The date must be a date before or equal to now.'],
                ],
            ]);
    }

    public function test_expected_data_array_use_external_api()
    {
        $formatedDate = Carbon::now()->format('Y-m-d');
        $coinId = CryptoCoinEnum::from('bitcoin')->name;
        $apiReturn = [
            'id' => 'bitcoin',
            'symbol' => 'btc',
            'name' => 'Bitcoin',
            'localization' => [
                'en' => 'Bitcoin',
            ],
            'image' => [
                'thumb' => 'https://assets.coingecko.com/coins/images/1/thumb/bitcoin.png?1547033579',
                'small' => 'https://assets.coingecko.com/coins/images/1/small/bitcoin.png?1547033579',
            ],
            'market_data' => [
                'current_price' => [
                    'usd' => 100.00,
                ],
                'market_cap' => [
                    'usd' => 100.00,
                ],
                'total_volume' => [
                    'usd' => 100.00,
                ],
            ],
        ];
        Cache::shouldReceive('remember')
            ->once()
            ->with("crypto-coin-{$coinId}-consulted_at-{$formatedDate}", config('cache.time.one_day'), \Closure::class)
            ->andReturn([]);

        Cache::shouldReceive('put')->once();

        $this->coinStub
            ->method('getHistory')
            ->with($coinId, Carbon::parse($formatedDate)->format('d-m-Y'))
            ->willReturn($apiReturn);
        $this->coinGeckoClientStub
            ->method('coins')
            ->willReturn($this->coinStub);
        $this->instance(CoinGeckoClient::class, $this->coinGeckoClientStub);

        $response = $this->getJson(route(self::ROUTE, ['coin_name' => 'bitcoin', 'date' => $formatedDate]));

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    'coin_id' => 'bitcoin',
                    'price' => 100,
                    'name' => 'bitcoin',
                    'consulted_at' => $formatedDate,
                ],
            ]);
    }

    public function test_expected_data_array_use_cache()
    {
        $formatedDate = Carbon::now()->format('Y-m-d');
        $coinId = CryptoCoinEnum::from('bitcoin')->name;
        $cacheReturn = [
            'id' => 1,
            'coin_id' => 'bitcoin',
            'price' => 100,
            'name' => 'bitcoin',
            'consulted_at' => $formatedDate,
        ];
        Cache::shouldReceive('remember')
            ->once()
            ->with("crypto-coin-{$coinId}-consulted_at-{$formatedDate}", config('cache.time.one_day'), \Closure::class)
            ->andReturn($cacheReturn);

        Cache::shouldReceive('put')->never();

        $response = $this->getJson(route(self::ROUTE, ['coin_name' => 'bitcoin', 'date' => $formatedDate]));

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    'id' => 1,
                    'coin_id' => 'bitcoin',
                    'price' => 100,
                    'name' => 'bitcoin',
                    'consulted_at' => $formatedDate,
                ],
            ]);
    }
}
