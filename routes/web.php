<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PaymongoController;
use App\Http\Controllers\OtpController;
use Illuminate\Support\Facades\Route;
use App\Models\Order;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ResetPasswordController;
use Barryvdh\DomPDF\Facade\Pdf;

Route::get('/', [POSController::class, 'showPOS']);
Route::get('/pos/category/{id}/products', [POSController::class, 'getProductsByCategory']);
Route::post('/pos/complete-order', [POSController::class, 'completeOrder']);

// Paymongo Checkout
Route::post('/pos/paymongo/create-checkout', [PaymongoController::class, 'createCheckout']);
Route::get('/pos/paymongo/check-status/{checkoutId}', [PaymongoController::class, 'checkStatus']);
Route::post('/pos/paymongo/finalize/{checkoutId}', [PaymongoController::class, 'finalizePaidOrder']);
Route::get('/payment-success', [PaymongoController::class, 'paymentSuccess']);

Route::get('/orders/{id}/receipt', function ($id) {
    $order = Order::findOrFail($id);

    $items = $order->cart_items;

    if (!is_array($items)) {
        $items = [];
    }

    $pdf = Pdf::loadView('receipt.pdf', [
        'order' => $order,
        'items' => $items,
    ]);

    return $pdf->download('receipt-'.$order->id.'.pdf');
})->name('receipt.download');

// OTP Route
Route::get('/otp', [OtpController::class, 'showOtpForm'])->name('otp.verify.form');
Route::post('/otp', [OtpController::class, 'verifyOtp'])->name('otp.verify');

// Reset Pass Route
Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');

Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'resetPassword'])->name('password.update');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('show.register');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('show.login');

    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [POSController::class, 'showIndex'])->name('pos.index');
    Route::get('/dashboard/data', [POSController::class, 'dashboardData'])->name('dashboard.data');
    Route::get('/category', [POSController::class, 'showCategory'])->name('pos.category');
    Route::get('/products', [POSController::class, 'showProducts'])->name('pos.products');
    Route::get('/report/sales', [POSController::class, 'showReportSales'])->name('pos.report.sales');
    Route::get('/report/product-analysis', [POSController::class, 'showProdAnal'])->name('pos.report.product.analysis');
    Route::get('/report/profits-and-loss', [POSController::class, 'showGainsLoss'])->name('pos.report.profits');
    Route::get('/sales/data', [POSController::class, 'getSalesData'])->name('sales.data');
    Route::get('/report/product-info/data', [POSController::class, 'getProductInfo'])->name('product.info.data');
    Route::get('/report/profit-loss/data', [POSController::class, 'getProfitLossData'])->name('profit.loss.data');

    Route::get('/category/create', [CategoryController::class, 'create'])->name('show.create.category');
    Route::get('category/{category}/edit', [CategoryController::class, 'edit'])->name('show.edit.category');

    Route::get('/product/create', [ProductController::class, 'create'])->name('show.create.product');
    Route::get('/products/show/{product}', [ProductController::class, 'showPage'])->name('products.showPage');
    Route::get('/products/{product}/edit', [ProductController::class, 'editPage'])->name('products.editPage');

    Route::get('/logs', [POSController::class, 'showLogs'])->name('show.logs');
});