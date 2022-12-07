<?php

namespace Tests\Unit\Actions;

use App\Actions\GetCoinPriceByEstimatedDateAction;
use App\Actions\GetCryptoCoinAction;
use App\Gateways\CoinGeckoClientGateWayInterface;
use App\Models\CryptoCoin;
use App\Repositories\CryptoCoinInterfaceRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Tests\TestCase;

class GetCoinPriceByEstimatedDateActionTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->coinGeckoClientGateWayStub = $this->createMock(CoinGeckoClientGateWayInterface::class);
        $this->getCryptoCoinActionStub = $this->createMock(GetCryptoCoinAction::class);
        $this->cryptoCoinRepositoryStub = $this->createMock(CryptoCoinInterfaceRepository::class);
    }

    public function test_expected_unprocessable_entity_http_exception_when_coin_id_is_invalid()
    {
        $this->expectException(UnprocessableEntityHttpException::class);

        $coinName = 'bitcoin';
        $dateTime = Carbon::now()->format('Y-m-d');
        $apiReturn = [
            'error' => 'coin not found',
        ];

        $this->getCryptoCoinActionStub
            ->expects(self::once())
            ->method('execute')
            ->with($coinName, $dateTime)
            ->willReturn([]);
        $this->coinGeckoClientGateWayStub
            ->expects(self::once())
            ->method('getCoinPriceByEstimatedDate')
            ->with($coinName, $dateTime)
            ->willReturn($apiReturn);

        $service = new GetCoinPriceByEstimatedDateAction(
            coinGeckoClientGateWay: $this->coinGeckoClientGateWayStub,
            getCryptoCoinAction: $this->getCryptoCoinActionStub,
            cryptoCoinRepository: $this->cryptoCoinRepositoryStub,
        );

        $service->execute(coinName: $coinName, dateTime: $dateTime);
    }

    public function test_expected_data_array_using_database()
    {
        $coinName = 'bitcoin';
        $dateTime = Carbon::now()->format('Y-m-d');
        $responseExpected = [
            'id' => 1,
            'coin_id' => 'bitcoin',
            'price' => 100,
            'name' => 'bitcoin',
            'consulted_at' => $dateTime,
        ];

        $this->getCryptoCoinActionStub
            ->expects(self::once())
            ->method('execute')
            ->with($coinName, $dateTime)
            ->willReturn($responseExpected);

        $service = new GetCoinPriceByEstimatedDateAction(
            coinGeckoClientGateWay: $this->coinGeckoClientGateWayStub,
            getCryptoCoinAction: $this->getCryptoCoinActionStub,
            cryptoCoinRepository: $this->cryptoCoinRepositoryStub,
        );

        $response = $service->execute(coinName: $coinName, dateTime: $dateTime);

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
                    'usd' => 100,
                ],
                'market_cap' => [
                    'usd' => 100,
                ],
                'total_volume' => [
                    'usd' => 100,
                ],
            ],
        ];
        $coinCrypto = new CryptoCoin();
        $coinCrypto->coin_id = $coinId;
        $coinCrypto->price = $price;
        $coinCrypto->name = 'bitcoin';
        $coinCrypto->consulted_at = $dateTime;
        $coinCrypto->updated_at = $dateTime;
        $coinCrypto->created_at = $dateTime;
        $coinCrypto->id = 1;

        $this->getCryptoCoinActionStub
            ->expects(self::once())
            ->method('execute')
            ->with($coinName, $dateTime)
            ->willReturn([]);
        $this->coinGeckoClientGateWayStub
            ->expects(self::once())
            ->method('getCoinPriceByEstimatedDate')
            ->with($coinName, $dateTime)
            ->willReturn($apiReturn);
        $this->cryptoCoinRepositoryStub
            ->expects(self::once())
            ->method('register')
            ->with($coinId, $coinName, $price, $dateTime)
            ->willReturn($coinCrypto);
        Cache::shouldReceive('put')
            ->once()
            ->with("crypto-coin-{$coinId}-consulted_at-{$dateTime}", $responseExpected, config('cache.time.one_month'));

        $service = new GetCoinPriceByEstimatedDateAction(
            coinGeckoClientGateWay: $this->coinGeckoClientGateWayStub,
            getCryptoCoinAction: $this->getCryptoCoinActionStub,
            cryptoCoinRepository: $this->cryptoCoinRepositoryStub,
        );

        $response = $service->execute(coinName: $coinName, dateTime: $dateTime);

        $this->assertEquals($response, $responseExpected);
    }
}
