<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PaymongoController extends Controller
{
    // ----------------------------------------------------
    // 1. CREATE CHECKOUT SESSION
    // ----------------------------------------------------
    public function createCheckout(Request $request)
    {
        $total = collect($request->cart)->sum(function ($item) {
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
                    "success_url" => url('/'),
                    "cancel_url" => url('/')
                ]
            ]
        ]);

        if (!$response->successful()) {
    return response()->json([
        'error' => 'Failed to create checkout session',
        'paymongo_response' => $response->body()
    ], 500);
}


        $data = $response->json()['data'];

        return [
            'checkout_id' => $data['id'],
            'checkout_url' => $data['attributes']['checkout_url']
        ];
    }

    // ----------------------------------------------------
    // 2. POLL CHECKOUT STATUS
    // ----------------------------------------------------
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
}
