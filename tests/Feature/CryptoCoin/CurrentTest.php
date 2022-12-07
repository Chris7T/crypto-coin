<?php

namespace Tests\Feature;

use App\Enums\CryptoCoinEnum;
use Codenixsv\CoinGeckoApi\Api\Simple;
use Codenixsv\CoinGeckoApi\CoinGeckoClient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class CurrentTest extends TestCase
{
    private const ROUTE = 'crypto-coin.current';

    public function setUp(): void
    {
        parent::setUp();
        $this->simpleStub = $this->createMock(Simple::class);
        $this->coinGeckoClientStub = $this->createMock(CoinGeckoClient::class);
    }

    public function test_expected_true_when_route_exists()
    {
        $this->assertTrue(Route::has(self::ROUTE));
    }

    public function test_expected_unprocessable_entity_exception_when_coin_name_is_null()
    {
        $response = $this->getJson(route(self::ROUTE, ['coin_name' => null]));

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['coin_name'])
            ->assertJson([
                'errors' => [
                    'coin_name' => ['The coin name field is required.'],
                ],
            ]);
    }

    public function test_expected_unprocessable_entity_exception_when_coin_name_is_invalid()
    {
        $response = $this->getJson(route(self::ROUTE, ['coin_name' => 'invalid']));

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['coin_name'])
            ->assertJson([
                'errors' => [
                    'coin_name' => ['The selected coin name is invalid.'],
                ],
            ]);
    }

    public function test_expected_unprocessable_entity_exception_when_coin_gecko_is_empty()
    {
        $this->simpleStub
            ->method('getPrice')
            ->with(CryptoCoinEnum::bitcoin->name, 'usd')
            ->willReturn([]);
        $this->coinGeckoClientStub
            ->method('simple')
            ->willReturn($this->simpleStub);
        $this->instance(CoinGeckoClient::class, $this->coinGeckoClientStub);

        $response = $this->getJson(route(self::ROUTE, ['coin_name' => 'bitcoin']));

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson([
                'message' => 'The selected coin name is invalid.',
            ]);
    }

    public function test_expected_data_array_use_external_api()
    {
        $formatedDate = Carbon::now()->format('Y-m-d');
        $coinId = CryptoCoinEnum::from('bitcoin')->name;
        $apiReturn = [
            'bitcoin' => [
                'usd' => 100.00,
            ],
        ];
        Cache::shouldReceive('remember')
            ->once()
            ->with("crypto-coin-{$coinId}-consulted_at-{$formatedDate}", config('cache.time.one_day'), \Closure::class)
            ->andReturn([]);

        Cache::shouldReceive('put')->once();

        $this->simpleStub
            ->method('getPrice')
            ->with($coinId, 'usd')
            ->willReturn($apiReturn);
        $this->coinGeckoClientStub
            ->method('simple')
            ->willReturn($this->simpleStub);
        $this->instance(CoinGeckoClient::class, $this->coinGeckoClientStub);

        $response = $this->getJson(route(self::ROUTE, ['coin_name' => 'bitcoin']));

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
            'coin_id' => 'bitcoin',
            'price' => 100,
            'name' => 'bitcoin',
            'consulted_at' => $formatedDate,
            'updated_at' => $formatedDate,
            'created_at' => $formatedDate,
            'id' => 12,
        ];
        Cache::shouldReceive('remember')
            ->once()
            ->with("crypto-coin-{$coinId}-consulted_at-{$formatedDate}", config('cache.time.one_day'), \Closure::class)
            ->andReturn($cacheReturn);

        Cache::shouldReceive('put')->never();

        $response = $this->getJson(route(self::ROUTE, ['coin_name' => 'bitcoin']));

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'data' => [
                    'id' => 12,
                    'coin_id' => 'bitcoin',
                    'price' => 100,
                    'name' => 'bitcoin',
                    'consulted_at' => $formatedDate,
                ],
            ]);
    }
}
