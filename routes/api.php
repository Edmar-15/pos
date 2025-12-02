<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\POSController;

Route::apiResource('categories', CategoryController::class);

Route::apiResource('products', ProductController::class);
