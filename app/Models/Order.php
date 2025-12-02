<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    // Fillable fields — allow mass assignment
    protected $fillable = [
        'order_number',
        'cart',
        'total',
        'status',
        'paymongo_link_id',
    ];

    // Cast cart to array automatically
    protected $casts = [
        'cart' => 'array',
    ];
}
