<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Notifications\OrderPlaced;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('customer', 'items')->latest()->get();
        return view('orders.index', compact('orders'));
    }

    public function create()
    {
        $customers = Customer::all();
        $products = Product::all();
        return view('orders.create', compact('customers', 'products'));
    }

    public function store(Request $request)
    {
        // 1️⃣ Validate input
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'items'       => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
        ]);

        // 2️⃣ Calculate total price & check stock
        $total = 0;
        foreach ($data['items'] as $item) {
            $product = Product::findOrFail($item['product_id']);

            if ($product->checkStock($item['quantity'])) {
                return back()->withErrors(['stock' => "{$product->name} is out of stock"]);
            }

            $total += $product->price * $item['quantity'];
        }

        // 3️⃣ Create order + order_items inside a transaction
        DB::beginTransaction();
        try {
            $order = Order::create([
                'customer_id' => $data['customer_id'],
                'total_price' => $total,
                'status'      => 'pending',
            ]);

            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity'   => $item['quantity'],
                    'price'      => $product->price,
                ]);
                // Reduce stock
                $product->decrement('stock', $item['quantity']);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['db' => 'Could not place order']);
        }

        // 4️⃣ Send notification
        Notification::send($order->customer, new OrderPlaced($order));

        // 5️⃣ Redirect
        return redirect()->route('orders.show', $order);
    }

    public function show(Order $order)
    {
        return view('orders.show', compact('order'));
    }
}
