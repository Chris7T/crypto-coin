<?php

namespace Tests\Unit\Actions;

use App\Actions\GetCryptoCoinAction;
use App\Actions\GetCurrentCoinPriceAction;
use App\Gateways\CoinGeckoClientGateWayInterface;
use App\Models\CryptoCoin;
use App\Repositories\CryptoCoinInterfaceRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Tests\TestCase;

class GetCurrentCoinPriceActionTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->coinGeckoClientGateWayStub = $this->createMock(CoinGeckoClientGateWayInterface::class);
        $this->getCryptoCoinActionStub = $this->createMock(GetCryptoCoinAction::class);
        $this->cryptoCoinRepositoryStub = $this->createMock(CryptoCoinInterfaceRepository::class);
    }

    public function test_expected_data_array_using_database()
    {
        $this->expectException(UnprocessableEntityHttpException::class);
        $coinName = 'bitcoin';
        $dateTime = Carbon::now()->format('Y-m-d');

        $this->getCryptoCoinActionStub
            ->expects(self::once())
            ->method('execute')
            ->with($coinName, $dateTime)
            ->willReturn([]);

        $service = new GetCurrentCoinPriceAction(
            coinGeckoClientGateWay: $this->coinGeckoClientGateWayStub,
            getCryptoCoinAction: $this->getCryptoCoinActionStub,
            cryptoCoinRepository: $this->cryptoCoinRepositoryStub,
        );

        $service->execute(coinName: $coinName);
    }

    public function test_exception_unprocessable_entity_http_when_coin_id_is_invalid()
    {
        $dateTime = Carbon::now()->format('Y-m-d');
        $coinName = 'bitcoin';
        $coinId = 'bitcoin';
        $price = 100.0;

        $responseExpected = [
            'id' => 1,
            'coin_id' => $coinId,
            'price' => $price,
            'name' => 'bitcoin',
            'consulted_at' => $dateTime,
            'updated_at' => $dateTime,
            'created_at' => $dateTime,
        ];

        $this->getCryptoCoinActionStub
            ->expects(self::once())
            ->method('execute')
            ->with($coinName, $dateTime)
            ->willReturn($responseExpected);
        Cache::shouldReceive('put')
            ->never();

        $service = new GetCurrentCoinPriceAction(
            coinGeckoClientGateWay: $this->coinGeckoClientGateWayStub,
            getCryptoCoinAction: $this->getCryptoCoinActionStub,
            cryptoCoinRepository: $this->cryptoCoinRepositoryStub,
        );

        $response = $service->execute(coinName: $coinName);

        $this->assertEquals($response, $responseExpected);
    }

    public function test_expected_data_array_using_external_api()
    {
        $coinName = 'bitcoin';
        $coinId = 'bitcoin';
        $price = 100.0;
        $dateTime = Carbon::now()->format('Y-m-d');
        $responseExpected = [
            'id' => 1,
            'coin_id' => $coinId,
            'price' => $price,
            'name' => 'bitcoin',
            'consulted_at' => $dateTime,
            'updated_at' => $dateTime,
            'created_at' => $dateTime,
        ];
        $apiReturn = [
            'bitcoin' => [
                'usd' => 100.0,
            ],
        ];
        $coinCrypto = new CryptoCoin();
        $coinCrypto->id = 1;
        $coinCrypto->coin_id = $coinId;
        $coinCrypto->price = $price;
        $coinCrypto->name = 'bitcoin';
        $coinCrypto->consulted_at = $dateTime;
        $coinCrypto->updated_at = $dateTime;
        $coinCrypto->created_at = $dateTime;

        $this->getCryptoCoinActionStub
            ->expects(self::once())
            ->method('execute')
            ->with($coinName, $dateTime)
            ->willReturn([]);
        $this->coinGeckoClientGateWayStub
            ->expects(self::once())
            ->method('getCurrentCoinPrice')
            ->with($coinName)
            ->willReturn($apiReturn);
        $this->cryptoCoinRepositoryStub
            ->expects(self::once())
            ->method('register')
            ->with($coinId, $coinName, $price, $dateTime)
            ->willReturn($coinCrypto);
        Cache::shouldReceive('put')
            ->once()
            ->with(
                "crypto-coin-{$coinId}-consulted_at-{$dateTime}",
                $responseExpected,
                config('cache.time.one_day')
            );

        $service = new GetCurrentCoinPriceAction(
            coinGeckoClientGateWay: $this->coinGeckoClientGateWayStub,
            getCryptoCoinAction: $this->getCryptoCoinActionStub,
            cryptoCoinRepository: $this->cryptoCoinRepositoryStub,
        );

        $response = $service->execute(coinName: $coinName);

        $this->assertEquals($response, $responseExpected);
    }
}
