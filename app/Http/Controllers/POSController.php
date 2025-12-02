<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Sale;
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

    public function completeOrder(Request $request) {
        Log::info('Incoming order request:', $request->all());

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

        // ----------------------------
        // 1) CASH: existing logic
        // ----------------------------
        if ($paymentMethod === 'cash') {
            try {
                foreach ($cart as $item) {
                    Log::info('Processing item (cash):', $item);

                    $sale = Sale::create([
                        'ordernumber' => 'ORD-' . strtoupper(Str::uuid()),
                        'productname' => $item['name'],
                        'price'       => floatval($item['sell_price']),
                        'instock'     => intval($item['stock']),
                        'sold'        => intval($item['quantity']),
                        'date'        => now(),
                    ]);

                    Log::info('Sale created successfully:', $sale->toArray());

                    $product = Product::find($item['id']);
                    if ($product) {
                        $product->stock = max($product->stock - $item['quantity'], 0);
                        $product->save();
                        Log::info("Updated stock for product ID {$product->id}: {$product->stock}");
                    } else {
                        Log::warning("Product ID {$item['id']} not found.");
                    }
                }

                return response()->json(['status' => 'success']);
            } catch (\Exception $e) {
                Log::error('ERROR during sale creation (cash): ' . $e->getMessage());
                Log::error($e->getTraceAsString());

                return response()->json(['error' => $e->getMessage()], 400);
            }
        }

        return response()->json(['error' => 'Invalid payment method.'], 400);
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

        $sales = $query->orderBy('date', 'desc')->get();

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
}
