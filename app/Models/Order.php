<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Notifications\OrderPlaced;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['customer_id', 'total_price', 'status'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function notifyCustomer(){
        Notification::send($this->customer, new OrderPlaced($this));
    }
    public static function createWithItems($data, $total){
        $order = DB::transaction(function () use($data, $total){
            $order = Order::create([
                'customer_id' => $data['customer_id'],
                'total_price' => $total,
                'status'      => 'pending',
            ]);

            foreach($data['items'] as $item){
                $product = Product::findOrFail($item["product_id"]);
                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity'   => $item['quantity'],
                    'price'      => $product->price,
                ]);
                // Reduce stock
                $product->decrement('stock', $item["quantity"]);
            }

            return $order;
        });
        return $order;
    }
}