<?php

namespace App\Http\Requests;

use App\Enums\CryptoCoinEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class CurrentCryptoCoinPriceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'coin_name' => $this->query('coin_name'),
        ]);
    }

    public function rules()
    {
        return [
            'coin_name' => ['bail', 'required', new Enum(CryptoCoinEnum::class)],
        ];
    }
}
