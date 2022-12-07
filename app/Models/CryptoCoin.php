<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CryptoCoin extends Model
{
    use HasFactory;

    protected $table = 'crypto_coins';

    protected $primaryKey = 'id';

    protected $fillable = [
        'coin_id',
        'price',
        'name',
        'consulted_at',
    ];

    protected $casts = [
        'consulted_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d',
        'created_at' => 'datetime:Y-m-d',
        'price' => 'float',
    ];
}
