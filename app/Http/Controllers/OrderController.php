<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Exceptions\ProductOutOfStockException;
// use Illuminate\Support\Facades\DB;
// use App\Notifications\OrderPlaced;
// use Illuminate\Support\Facades\Notification;

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
        try {
            $total = Order::calculateTotal($data["items"]);
        } catch (ProductOutOfStockException $e) {
            return back()->withErrors(['stock' => $e->getMessage()]);
        }

        // 3️⃣ Create order + order_items inside a transaction
        try {
            $order = Order::createWithItems($data, $total);
        } catch (\Exception $e) {
            return back()->withErrors(['db' => 'Could not place order']);
        }

        // 4️⃣ Send notification
        // Notification::send($order->customer, new OrderPlaced($order));
        $order->notifyCustomer();

        // 5️⃣ Redirect
        return redirect()->route('orders.show', $order);
    }

    public function show(Order $order)
    {
        return view('orders.show', compact('order'));
    }
}
