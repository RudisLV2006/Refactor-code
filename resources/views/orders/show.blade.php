@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h1>Order #{{ $order->id }}</h1>
            <p><strong>Customer:</strong> {{ $order->customer->name }}</p>
            <p><strong>Total Price:</strong> ${{ $order->total_price }}</p>
            <p><strong>Status:</strong> {{ $order->status }}</p>

            <h3 class="mt-4">Items</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->product->name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>${{ $item->price }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <a href="{{ route('orders.create') }}" class="btn btn-primary">Place Another Order</a>
        </div>
    </div>
@endsection
