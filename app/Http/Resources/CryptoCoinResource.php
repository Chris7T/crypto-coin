<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CryptoCoinResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this['id'],
            'coin_id' => $this['coin_id'],
            'price' => $this['price'],
            'name' => $this['name'],
            'consulted_at' => $this['consulted_at'],
        ];
    }
}
