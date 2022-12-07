<?php

namespace App\Http\Controllers;

use App\Actions\GetCurrentCoinPriceAction;
use App\Http\Requests\CurrentCryptoCoinPriceRequest;
use App\Http\Resources\CryptoCoinResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class CurrentCryptoCoinPriceController extends Controller
{
    public function __construct(
        private GetCurrentCoinPriceAction $getCurrentCoinPriceAction
    ) {
    }

    public function __invoke(CurrentCryptoCoinPriceRequest $request)
    {
        try {
            $cryptoCoin = $this->getCurrentCoinPriceAction->execute($request->input('coin_name'));

            return CryptoCoinResource::make($cryptoCoin);
        } catch (UnprocessableEntityHttpException $ex) {
            return Response::json(['message' => $ex->getMessage()], HttpFoundationResponse::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $ex) {
            Log::critical('Controller: '.self::class, ['exception' => $ex->getMessage()]);

            return Response::json(['message' => config('messages.error.server')], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
