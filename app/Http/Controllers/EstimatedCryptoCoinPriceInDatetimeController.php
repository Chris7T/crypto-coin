<?php

namespace App\Http\Controllers;

use App\Actions\GetCoinPriceByEstimatedDateAction;
use App\Http\Requests\EstimatedCryptoCoinPriceRequest;
use App\Http\Resources\CryptoCoinResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class EstimatedCryptoCoinPriceInDatetimeController extends Controller
{
    public function __construct(
        private GetCoinPriceByEstimatedDateAction $getCoinPriceByEstimatedDateAction
    ) {
    }

    public function __invoke(EstimatedCryptoCoinPriceRequest $request)
    {
        try {
            $cryptoCoin = $this->getCoinPriceByEstimatedDateAction->execute(
                $request->input('coin_name'),
                $request->input('date')
            );

            return CryptoCoinResource::make($cryptoCoin);
        } catch (UnprocessableEntityHttpException $ex) {
            return Response::json(['message' => $ex->getMessage()], HttpFoundationResponse::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $ex) {
            Log::critical('Controller: ' . self::class, ['exception' => $ex->getMessage()]);

            return Response::json(['message' => config('messages.error.server')], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
