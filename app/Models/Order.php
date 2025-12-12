<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Notifications\OrderPlaced;
use Illuminate\Support\Facades\Notification;

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
    public function createFromRequest($productId,$quantity){
        $product = Product::findOrFail($productId);
        $this->items()->create([
            'product_id' => $product->id,
            'quantity'   => $quantity,
            'price'      => $product->price,
        ]);
        // Reduce stock
        $product->decrement('stock', $quantity);
    }
}