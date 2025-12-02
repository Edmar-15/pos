<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'cart_items',   // JSON of items before checkout
        'total_amount',
        'checkout_id',
        'status'
    ];

    protected $casts = [
        'cart_items' => 'array'
    ];
}
