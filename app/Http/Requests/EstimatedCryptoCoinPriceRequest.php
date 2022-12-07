<?php

namespace App\Http\Requests;

use App\Enums\CryptoCoinEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class EstimatedCryptoCoinPriceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'coin_name' => $this->query('coin_name'),
            'date' => $this->query('date'),
        ]);
    }

    public function rules()
    {
        return [
            'coin_name' => ['bail', 'required', new Enum(CryptoCoinEnum::class)],
            'date' => ['bail', 'required', 'date_format:Y-m-d', 'before_or_equal:now'],
        ];
    }
}
