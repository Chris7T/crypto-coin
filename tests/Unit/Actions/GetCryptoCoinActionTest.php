<?php

namespace Tests\Unit\Actions;

use App\Actions\GetCryptoCoinAction;
use App\Models\CryptoCoin;
use App\Repositories\CryptoCoinInterfaceRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class GetCryptoCoinActionTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->cryptoCoinRepositoryStub = $this->createMock(CryptoCoinInterfaceRepository::class);
        $this->coinId = 'bitcoin';
        $this->dateTime = Carbon::now()->format('Y-m-d');
        $this->coinName = 'bitcoin';
        $this->coinCrypto = new CryptoCoin();
        $this->coinCrypto->coin_id = 'bitcoin';
        $this->coinCrypto->price = 100;
        $this->coinCrypto->name = 'bitcoin';
        $this->coinCrypto->consulted_at = $this->dateTime;
        $this->coinCrypto->updated_at = $this->dateTime;
        $this->coinCrypto->created_at = $this->dateTime;
        $this->coinCrypto->id = 1;
    }

    public function test_expected_data_array_using_cache()
    {
        $responseExpected = [
            'id' => 1,
            'coin_id' => 'bitcoin',
            'price' => 100,
            'name' => 'bitcoin',
            'consulted_at' => $this->dateTime,
            'updated_at' => $this->dateTime,
            'created_at' => $this->dateTime,
        ];

        Cache::shouldReceive('remember')
            ->once()
            ->with("crypto-coin-{$this->coinId}-consulted_at-{$this->dateTime}", config('cache.time.one_day'), \Closure::class)
            ->andReturn($responseExpected);

        $service = new GetCryptoCoinAction(cryptoCoinRepository: $this->cryptoCoinRepositoryStub);

        $response = $service->execute($this->coinId, $this->dateTime);

        $this->assertEquals($responseExpected, $response);
    }

    public function test_expected_data_array_using_database()
    {
        $responseExpected = [
            'id' => 1,
            'coin_id' => $this->coinId,
            'price' => 100,
            'name' => $this->coinName,
            'consulted_at' => $this->dateTime,
            'updated_at' => $this->dateTime,
            'created_at' => $this->dateTime,
        ];

        $this->cryptoCoinRepositoryStub
            ->expects(self::once())
            ->method('getByCoinName')
            ->with($this->coinName, $this->dateTime)
            ->willReturn($this->coinCrypto);

        $service = new GetCryptoCoinAction(cryptoCoinRepository: $this->cryptoCoinRepositoryStub);
        $response = $service->execute($this->coinId, $this->dateTime);

        $this->assertEquals($responseExpected, $response);
    }

    public function test_expected_empty_array_when_not_found_crypto_coin()
    {
        $responseExpected = [];

        $this->cryptoCoinRepositoryStub
            ->expects(self::once())
            ->method('getByCoinName')
            ->with($this->coinName, $this->dateTime)
            ->willReturn(null);

        $service = new GetCryptoCoinAction(cryptoCoinRepository: $this->cryptoCoinRepositoryStub);
        $response = $service->execute($this->coinId, $this->dateTime);

        $this->assertEquals($responseExpected, $response);
    }
}
