@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h1>Create Order</h1>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('orders.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="customer_id" class="form-label">Customer</label>
                    <select name="customer_id" id="customer_id" class="form-control">
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>

                <hr>

                <h3>Items</h3>
                <div id="items">
                    <div class="row item mb-2">
                        <div class="col-md-5">
                            <select name="items[0][product_id]" class="form-control">
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} - ${{ $product->price }} ({{$product->stock}} in stock)</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <input type="number" name="items[0][quantity]" class="form-control" placeholder="Quantity" min="1">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger remove-item">Remove</button>
                        </div>
                    </div>
                </div>
                <button type="button" id="add-item" class="btn btn-primary">Add Item</button>

                <hr>

                <button type="submit" class="btn btn-success">Place Order</button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let itemIndex = 1;
            document.getElementById('add-item').addEventListener('click', function () {
                const itemHtml = `
                    <div class="row item mb-2">
                        <div class="col-md-5">
                            <select name="items[${itemIndex}][product_id]" class="form-control">
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} - ${{ $product->price }} ({{$product->stock}} in stock)</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <input type="number" name="items[${itemIndex}][quantity]" class="form-control" placeholder="Quantity" min="1">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger remove-item">Remove</button>
                        </div>
                    </div>
                `;
                document.getElementById('items').insertAdjacentHTML('beforeend', itemHtml);
                itemIndex++;
            });

            document.getElementById('items').addEventListener('click', function (e) {
                if (e.target.classList.contains('remove-item')) {
                    e.target.closest('.item').remove();
                }
            });
        });
    </script>
@endsection
