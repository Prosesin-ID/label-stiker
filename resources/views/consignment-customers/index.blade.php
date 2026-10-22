
@extends('layouts.app')

@section('title', 'Stock Consignment Gudang Customer')
@section('eyebrow', 'INVENTORY')
@section('heading', 'Stock Consignment Gudang Customer')

@section('content')

@php
    $selectedCustomer = $customers->firstWhere(
        'id',
        request('customer_id')
    );

    $selectedProduct = $products->firstWhere(
        'id',
        request('product_id')
    );
@endphp

@if($errors->any())
    <div class="error-box access-error">
        <strong>Periksa data berikut:</strong>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="panel">

    <div class="panel-heading">
        <div>
            <p class="eyebrow">CONSIGNMENT CUSTOMER</p>
            <h3>Data Stock Consignment Gudang Customer</h3>
        </div>
    </div>

    {{-- FILTER PELANGGAN DAN ITEM --}}
    <div style="padding: 24px;">

        <form
            method="GET"
            action="{{ route('consignment-customer.index') }}"
        >

            <div style="display: flex; gap: 20px; flex-wrap: wrap;">

                <div style="flex: 1; min-width: 240px;">

                    <label for="customer_id">
                        Nama Pelanggan
                    </label>

                    <select
                        id="customer_id"
                        name="customer_id"
                        onchange="this.form.submit()"
                        style="width: 100%;"
                    >
                        <option value="">-- Semua Pelanggan --</option>

                        @foreach($customers as $customer)
                            <option
                                value="{{ $customer->id }}"
                                @selected($selectedCustomer?->id === $customer->id)
                            >
                                {{ $customer->name }}
                                ({{ $customer->code }})
                            </option>
                        @endforeach
                    </select>

                </div>

                <div style="flex: 1; min-width: 240px;">

                    <label for="product_id">
                        Data Item Parts Consignment
                    </label>

                    <select
                        id="product_id"
                        name="product_id"
                        onchange="this.form.submit()"
                        style="width: 100%;"
                    >
                        <option value="">-- Pilih Item Parts --</option>

                        @foreach($products as $product)
                            <option
                                value="{{ $product->id }}"
                                @selected($selectedProduct?->id === $product->id)
                            >
                                {{ $product->name }}
                                ({{ $product->sku }})
                            </option>
                        @endforeach
                    </select>

                </div>

            </div>
        </form>

        {{-- DETAIL PELANGGAN --}}
        @if($selectedCustomer)
            <div style="margin-top: 24px;">
                <h3>Informasi Pelanggan</h3>

                <p>
                    <strong>Nama Pelanggan:</strong>
                    {{ $selectedCustomer->name }}
                </p>

                <p>
                    <strong>Kode Pelanggan:</strong>
                    {{ $selectedCustomer->code }}
                </p>

                <p>
                    <strong>Alamat:</strong>
                    {{ $selectedCustomer->address ?: '-' }}
                </p>
            </div>
        @endif

        {{-- DETAIL ITEM --}}
        @if($selectedProduct)
            <div style="margin-top: 24px;">
                <h3>Informasi Item Parts</h3>

                <p>
                    <strong>Nama Item:</strong>
                    {{ $selectedProduct->name }}
                </p>

                <p>
                    <strong>Customer No:</strong>
                    {{ $selectedProduct->customer_part_no ?: '-' }}
                </p>

                <p>
                    <strong>WAF No (SKU):</strong>
                    {{ $selectedProduct->sku }}
                </p>

                <p>
                    <strong>Unit:</strong>
                    {{ $selectedProduct->uom }}
                </p>
            </div>
        @endif

        {{-- TAMBAHKAN ITEM CONSIGNMENT --}}
        @if($selectedCustomer && $selectedProduct)
            <form
                method="POST"
                action="{{ route('consignment-customer.store') }}"
                style="margin-top: 24px;"
            >
                @csrf

                <input
                    type="hidden"
                    name="customer_id"
                    value="{{ $selectedCustomer->id }}"
                >

                <input
                    type="hidden"
                    name="product_id"
                    value="{{ $selectedProduct->id }}"
                >

                <button
                    type="submit"
                    class="button button-primary"
                >
                    + Tambahkan Item Consignment
                </button>
            </form>
        @endif

    </div>

    {{-- TABEL STOCK CONSIGNMENT --}}
    <div class="panel-heading">
        <div>
            <p class="eyebrow">DATA STOCK CONSIGNMENT</p>

            <h3>
                @if($selectedCustomer)
                    Stock Consignment: {{ $selectedCustomer->name }}
                @else
                    Semua Stock Consignment Customer
                @endif
            </h3>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Pelanggan</th>
                    <th>Gudang Customer</th>
                    <th>Item Parts</th>
                    <th>Customer No</th>
                    <th>WAF No</th>
                    <th>Unit</th>
                    <th>Saldo Stock</th>
                </tr>
            </thead>

            <tbody>
                @forelse($consignmentItems as $item)

                    @php
                        $itemWarehouses = $customerWarehouses->where(
                            'customer_id',
                            $item->customer_id
                        );
                    @endphp

                    @forelse($itemWarehouses as $warehouse)

                        @php
                            $balance = $balancesByWarehouse
                                ->get($warehouse->id, collect())
                                ->firstWhere(
                                    'product_id',
                                    $item->product_id
                                );

                            $stockQuantity = $balance
                                ? (float) $balance->quantity
                                : 0;
                        @endphp

                        <tr>
                            <td>{{ $item->id }}</td>

                            <td>
                                <strong>
                                    {{ $item->customer?->name ?? '-' }}
                                </strong>
                                <small>
                                    {{ $item->customer?->code ?? '-' }}
                                </small>
                            </td>

                            <td>
                                <strong>{{ $warehouse->name }}</strong>
                                <small class="mono">{{ $warehouse->code }}</small>
                            </td>

                            <td>
                                <strong>{{ $item->product?->name ?? '-' }}</strong>
                            </td>

                            <td>
                                {{ $item->product?->customer_part_no ?: '-' }}
                            </td>

                            <td>
                                {{ $item->product?->sku ?? '-' }}
                            </td>

                            <td>
                                {{ $item->product?->uom ?? '-' }}
                            </td>

                            <td>
                                <strong>
                                    {{ number_format($stockQuantity, 4, ',', '.') }}
                                </strong>
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td>{{ $item->id }}</td>

                            <td>
                                <strong>
                                    {{ $item->customer?->name ?? '-' }}
                                </strong>
                            </td>

                            <td>
                                <span class="status draft">
                                    Belum ada gudang
                                </span>
                            </td>

                            <td>
                                {{ $item->product?->name ?? '-' }}
                            </td>

                            <td>
                                {{ $item->product?->customer_part_no ?: '-' }}
                            </td>

                            <td>
                                {{ $item->product?->sku ?? '-' }}
                            </td>

                            <td>
                                {{ $item->product?->uom ?? '-' }}
                            </td>

                            <td>-</td>
                        </tr>

                    @endforelse

                @empty

                    <tr>
                        <td colspan="8" class="empty-state">
                            Belum ada Item Parts Consignment terdaftar.
                        </td>
                    </tr>

                @endforelse
            </tbody>
        </table>
    </div>

</div>

@endsection
