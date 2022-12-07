<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CryptoCoinFactory extends Factory
{
    public function definition()
    {
        return [
            'price' => fake()->randomFloat(2, 1, 1000),
            'name' => fake()->word(),
        ];
    }
}
