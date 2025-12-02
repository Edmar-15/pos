<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Order;
use App\Models\Product;

class PaymongoController extends Controller
{
    // ----------------------
    // 1. CREATE CHECKOUT SESSION
    // ----------------------
    public function createCheckout(Request $request)
    {
        $cartItems = $request->cart;

        $total = collect($cartItems)->sum(function ($item) {
            return $item['sell_price'] * $item['quantity'];
        });

        $totalWithTax = $total + ($total * 0.10);

        $secretKey = base64_encode(env('PAYMONGO_SECRET') . ":");

        $response = Http::withHeaders([
            'Authorization' => "Basic {$secretKey}",
            'Content-Type'  => 'application/json'
        ])->post('https://api.paymongo.com/v1/checkout_sessions', [
            "data" => [
                "attributes" => [
                    "amount" => intval($totalWithTax * 100),
                    "currency" => "PHP",
                    "description" => "POS Order Payment",
                    "line_items" => [
                        [
                            "name" => "POS Payment",
                            "amount" => intval($totalWithTax * 100),
                            "currency" => "PHP",
                            "quantity" => 1
                        ]
                    ],
                    "payment_method_types" => ["gcash", "grab_pay", "card"],
                    "success_url" => url('/payment-success'),
                    "cancel_url" => url('/payment-cancel')
                ]
            ]
        ]);

        if (!$response->successful()) {
            return response()->json(['error' => 'Failed to create checkout session'], 500);
        }

        $data = $response->json()['data'];

        // ----------------------
        // SAVE PENDING ORDER
        // ----------------------
        Order::create([
            'user_id' => null,
            'cart_items' => $cartItems,
            'total_amount' => intval($totalWithTax),
            'checkout_id' => $data['id'],
            'status' => 'pending'
        ]);

        return [
            'checkout_id' => $data['id'],
            'checkout_url' => $data['attributes']['checkout_url']
        ];
    }

    // ----------------------
    // 2. POLL CHECKOUT STATUS
    // ----------------------
    public function checkStatus($checkoutId)
    {
        $secretKey = base64_encode(env('PAYMONGO_SECRET') . ":");

        $response = Http::withHeaders([
            'Authorization' => "Basic {$secretKey}"
        ])->get("https://api.paymongo.com/v1/checkout_sessions/{$checkoutId}");

        if (!$response->successful()) {
            return ['status' => 'error'];
        }

        $status = $response->json()['data']['attributes']['payment_intent']['attributes']['status'] ?? null;

        return ['status' => $status];
    }

    // ----------------------
    // 3. FINALIZE PAID ORDER
    // ----------------------
    public function finalizePaidOrder(Request $request, $checkoutId)
    {
        $order = Order::where('checkout_id', $checkoutId)
                    ->where('status', 'pending')
                    ->first();

        if (!$order) {
            return response()->json(['message' => 'Order already processed or not found'], 200);
        }

        $cartItems = $order->cart_items;

        // Insert each cart item as a separate row in sales table
        foreach ($cartItems as $item) {
            DB::table('sales')->insert([
                'ordernumber' => 'ORD-' . strtoupper(Str::uuid()), // unique order number
                'productname' => $item['name'],
                'price'       => $item['sell_price'],
                'instock'     => $item['stock'],
                'sold'        => $item['quantity'],
                'date'        => now(),
                'created_at'  => now(),
                'updated_at'  => now()
            ]);

            $product = Product::find($item['id']);
            if ($product) {
                $product->stock = max($product->stock - $item['quantity'], 0);
                $product->save();
                Log::info("Updated stock for product ID {$product->id}: {$product->stock}");
            } else {
                Log::warning("Product ID {$item['id']} not found.");
            }
        }

        $order->status = 'paid';
        $order->save();

        return response()->json(['message' => 'Order finalized successfully']);
    }

    public function paymentSuccess(Request $request)
    {
        $checkoutId = $request->query('checkout_id');

        if (!$checkoutId) {
            return view('pos.payment-success')->with('message', 'Checkout ID missing.');
        }

        // Fetch the pending order
        $order = Order::where('checkout_id', $checkoutId)
                    ->where('status', 'pending')
                    ->first();

        if (!$order) {
            return view('pos.payment-success')->with('message', 'Order already processed or not found.');
        }

        // ----------------------
        // POLL PAYMONGO TO CONFIRM PAYMENT
        // ----------------------
        $secretKey = base64_encode(env('PAYMONGO_SECRET') . ":");

        $response = Http::withHeaders([
            'Authorization' => "Basic {$secretKey}"
        ])->get("https://api.paymongo.com/v1/checkout_sessions/{$checkoutId}");

        if (!$response->successful()) {
            return view('pos.payment-success')->with('message', 'Could not verify payment status.');
        }

        $status = $response->json()['data']['attributes']['payment_intent']['attributes']['status'] ?? null;

        if ($status !== 'succeeded') {
            // Payment is not completed yet
            return view('pos.payment-success')->with('message', 'Payment not completed yet. Please wait.');
        }

        // ----------------------
        // PAYMENT CONFIRMED → MOVE TO SALES TABLE
        // ----------------------
        $cartItems = $order->cart_items;

        foreach ($cartItems as $item) {
            DB::table('sales')->insert([
                'ordernumber' => Str::upper(Str::random(10)),
                'productname' => $item['name'],
                'price'       => $item['sell_price'],
                'instock'     => $item['stock'],
                'sold'        => $item['quantity'],
                'date'        => now(),
                'created_at'  => now(),
                'updated_at'  => now()
            ]);
        }

        $order->status = 'paid';
        $order->save();

        return redirect()->route('receipt.download', ['id' => $order->id]);
    }
}
