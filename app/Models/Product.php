<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $fillable = ['category_id', 'name', 'stock', 'sell_price', 'purchase_price', 'image'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
