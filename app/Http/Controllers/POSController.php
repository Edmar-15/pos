<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Order;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class POSController extends Controller
{
    public function showIndex() {
        return view('pos.index');
    }

    public function showCategory() {
        return view('pos.category');
    }

    public function showProducts() {
        return view('pos.products');
    }

    public function showReportSales() {
        return view('pos.salesReport');
    }

    public function showProdAnal() {
        return view('pos.productAnalysis');
    }

    public function showGainsLoss() {
        return view('pos.profitsLoss');
    }

    public function showPOS() {
        $categories = Category::all();
        return view('welcome', compact('categories'));
    }

    public function getProductsByCategory($id) {
        $products = Product::where('category_id', $id)->get();
        return response()->json($products);
    }

    public function completeOrder(Request $request)
    {
        $request->validate([
            'cart'              => 'required|array|min:1',
            'cart.*.name'       => 'required|string',
            'cart.*.sell_price' => 'required|numeric',
            'cart.*.stock'      => 'required|integer',
            'cart.*.quantity'   => 'required|integer|min:1',
            'cart.*.id'         => 'required|integer',
            'payment_method'    => 'required|string',
        ]);

        $cart = $request->input('cart');
        $paymentMethod = $request->input('payment_method');

        if ($paymentMethod === 'cash') {
            DB::beginTransaction();
            try {
                // 1️⃣ Create the order
                $order = Order::create([
                    'cart_items'     => $cart, // store as array, ensure $casts in Order model: 'cart_items' => 'array'
                    'status'         => 'paid',
                    'total_amount'   => array_sum(array_map(fn($i) => $i['sell_price'] * $i['quantity'], $cart)),
                    'payment_method' => 'cash',
                    'checkout_id'    => 'CASH-' . Str::upper(Str::random(8)), // fake checkout ID for cash
                ]);

                // 2️⃣ Insert sales and update stock
                foreach ($cart as $item) {
                    Sale::create([
                        'ordernumber' => Str::upper(Str::random(10)),
                        'productname' => $item['name'],
                        'price'       => $item['sell_price'],
                        'instock'     => $item['stock'],
                        'sold'        => $item['quantity'],
                        'date'        => now(),
                    ]);

                    $product = Product::find($item['id']);
                    if ($product) {
                        $product->stock = max($product->stock - $item['quantity'], 0);
                        $product->save();
                    }
                }

                DB::commit();

                // 3️⃣ Return the same view as paymentSuccess
                return response()->json([
                    'status'    => 'success',
                    'message'   => 'Payment successful!',
                    'order'     => $order,
                    'items'     => $order->cart_items,
                    'receiptUrl'=> route('receipt.download', ['id' => $order->id]),
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                ], 400);
            }
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'Invalid payment method.',
        ], 400);
    }

    public function getSalesData(Request $request) {
        $query = DB::table('sales');

        if ($request->start && $request->end) {
            $query->whereDate('date', '>=', $request->start)
                ->whereDate('date', '<=', $request->end);
        } elseif ($request->start) {
            $query->whereDate('date', '>=', $request->start);
        } elseif ($request->end) {
            $query->whereDate('date', '<=', $request->end);
        }

        $sales = $query
        ->orderBy('date', 'desc')
        ->paginate(10);

        return response()->json($sales);
    }

    public function getProductInfo(Request $request) {
        $query = DB::table('products as p')
            ->join('categories as c', 'p.category_id', '=', 'c.id')
            ->select(
                'p.name as productname',
                'c.name as categoryname',
                DB::raw('COALESCE((SELECT SUM(s.sold) FROM sales s WHERE s.productname = p.name), 0) as sold'),
                'p.stock as remaining_stock'
            );

        if ($request->low_stock) {
            $query->where('p.stock', '<', 100);
        }

        $products = $query->get();

        return response()->json($products);
    }

    public function getProfitLossData() {
        $data = DB::table('products as p')
            ->join('sales as s', 'p.name', '=', 's.productname')
            ->select(
                'p.name as productname',
                'p.sell_price',
                'p.purchase_price',
                DB::raw('SUM(s.sold) as sold')
            )
            ->groupBy('p.id', 'p.name', 'p.sell_price', 'p.purchase_price')
            ->get();

        return response()->json($data);
    }

    public function dashboardData()
    {
        // 1. Low stock count (<100)
        $lowStock = DB::table('products')->where('stock', '<', 100)->count();

        // 2. Low stock products for pie chart
        $lowStockProducts = DB::table('products')
            ->where('stock', '<', 100)
            ->get(['name', 'stock']);

        // 3. Daily Purchase (count of sales today)
        $today = Carbon::today();
        $dailyPurchase = DB::table('sales')
            ->whereDate('date', $today)
            ->sum('instock'); // assuming 'instock' = quantity purchased

        // 4. Daily Sales (total price of sales today)
        $dailySales = DB::table('sales')
            ->whereDate('date', $today)
            ->sum(DB::raw('sold * price'));

        // 5. Monthly Sales (total price of sales this month)
        $monthStart = Carbon::now()->startOfMonth();
        $monthlySales = DB::table('sales')
            ->whereBetween('date', [$monthStart, Carbon::now()])
            ->sum(DB::raw('sold * price'));

        // 6. Chart Data: Monthly Sales by Product
        $chartDataRaw = DB::table('sales')
            ->select('productname', DB::raw('SUM(sold) as total_sold'))
            ->whereMonth('date', Carbon::now()->month)
            ->groupBy('productname')
            ->get();

        $chartLabels = $chartDataRaw->pluck('productname');
        $chartData = $chartDataRaw->pluck('total_sold');

        // Return JSON
        return response()->json([
            'lowStock' => $lowStock,
            'lowStockProducts' => $lowStockProducts,
            'dailyPurchase' => $dailyPurchase,
            'dailySales' => $dailySales,
            'monthlySales' => $monthlySales,
            'chart' => [
                'labels' => $chartLabels,
                'data' => $chartData
            ],
        ]);
    }

    public function showLogs() {
        $logs = DB::table('logs')
        ->join('users', 'logs.user_id', '=', 'users.id')
        ->select('users.name', 'logs.time_in', 'logs.time_out')
        ->orderBy('logs.id', 'desc')
        ->get();

        return view('pos.logs', compact('logs'));
    }
}
