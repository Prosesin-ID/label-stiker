
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

    $editCustomerHasErrors = $errors->any()
        && (string) old('editing_customer_id')
            === (string) ($selectedCustomer?->id ?? '');
@endphp

<style>
    /* -----------------------------------------
       STOCK CONSIGNMENT - TAMPILAN KHUSUS
       ----------------------------------------- */

    .cc-content {
        padding: 24px;
    }

    .cc-filter-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .cc-card {
        background: #ffffff;
        border: 1px solid #d9e1ea;
        border-radius: 12px;
        padding: 20px;
        min-width: 0;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.035);
    }

    .cc-filter-card {
        background: #fbfcfe;
    }

    .cc-card-label {
        display: block;
        font-size: 13px;
        font-weight: 700;
        color: #475569;
        margin-bottom: 10px;
    }

    .cc-control {
        display: block;
        width: 100%;
        min-height: 44px;
        padding: 10px 12px;
        box-sizing: border-box;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #ffffff;
        color: #172033;
        font-size: 14px;
        line-height: 1.5;
    }

    .cc-control:focus {
        outline: none;
        border-color: #eab308;
        box-shadow: 0 0 0 3px rgba(234, 179, 8, 0.15);
    }

    .cc-detail-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
    }

    .cc-info-card {
        margin-top: 20px;
        border: 1px solid #d9e1ea;
        border-radius: 12px;
        background: #ffffff;
        overflow: hidden;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.035);
    }

    .cc-info-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 17px 20px;
        background: #f8fafc;
        border-bottom: 1px solid #d9e1ea;
        border-left: 4px solid #f8c400;
    }

    .cc-info-header h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 700;
        color: #172033;
    }

    .cc-info-body {
        padding: 20px;
    }

    .cc-info-field {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 9px;
        padding: 15px 16px;
        min-width: 0;
    }

    .cc-field-label {
        display: block;
        font-size: 12px;
        font-weight: 700;
        color: #64748b;
        letter-spacing: 0.3px;
        margin-bottom: 8px;
    }

    .cc-field-value {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #172033;
        line-height: 1.6;
        overflow-wrap: anywhere;
    }

    .cc-info-actions {
        margin-top: 18px;
        padding-top: 16px;
        border-top: 1px solid #e2e8f0;
    }

    .cc-edit-details {
        width: 100%;
    }

    .cc-edit-toggle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 42px;
        padding: 0 20px;
        background: #ffc400;
        border: 1px solid #ffc400;
        border-radius: 9px;
        color: #111827;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        list-style: none;
        user-select: none;
        box-sizing: border-box;
    }

    .cc-edit-toggle::-webkit-details-marker {
        display: none;
    }

    .cc-edit-toggle:hover {
        background: #edb600;
    }

    .cc-edit-toggle:focus-visible {
        outline: 3px solid rgba(234, 179, 8, 0.4);
        outline-offset: 3px;
    }

    .cc-edit-panel {
        margin-top: 18px;
        padding: 20px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #fbfcfe;
    }

    .cc-edit-title {
        margin: 0 0 6px;
        font-size: 16px;
        font-weight: 700;
        color: #172033;
    }

    .cc-edit-description {
        margin: 0 0 20px;
        color: #64748b;
        font-size: 13px;
        line-height: 1.6;
    }

    .cc-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .cc-form-label {
        display: block;
        font-size: 13px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 8px;
    }

    .cc-help-text {
        display: block;
        margin-top: 7px;
        color: #64748b;
        font-size: 12px;
        line-height: 1.5;
    }

    .cc-error-text {
        display: block;
        margin-top: 7px;
        color: #dc2626;
        font-size: 12px;
    }

    .cc-edit-note {
        margin-top: 17px;
        padding: 12px 14px;
        background: #fffdf2;
        border: 1px solid #fde68a;
        border-radius: 8px;
        color: #785e13;
        font-size: 12px;
        line-height: 1.6;
    }

    .cc-action-row {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 18px;
    }

    .cc-muted-text {
        color: #64748b;
        font-size: 12px;
    }

    .cc-add-item {
        margin-top: 20px;
        padding: 18px 20px;
        background: #f8fafc;
        border: 1px solid #d9e1ea;
        border-radius: 10px;
    }

    .cc-stock-table {
        width: 100%;
        min-width: 980px;
        border-collapse: collapse;
    }

    .cc-stock-table th {
        padding: 14px 16px;
        background: #f8fafc;
        border: 1px solid #dce3ec;
        color: #475569;
        font-size: 12px;
        font-weight: 700;
        text-align: left;
        white-space: nowrap;
    }

    .cc-stock-table td {
        padding: 14px 16px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        color: #334155;
        font-size: 13px;
        vertical-align: top;
    }

    .cc-stock-table tbody tr:hover td {
        background: #fffdf3;
    }

    .cc-stock-table td strong {
        display: block;
        font-weight: 700;
        color: #172033;
    }

    .cc-stock-table td small {
        display: block;
        margin-top: 5px;
        color: #64748b;
        font-size: 12px;
        overflow-wrap: anywhere;
    }

    .cc-success-box {
        padding: 14px 18px;
        margin-bottom: 18px;
        border: 1px solid #86efac;
        border-radius: 9px;
        background: #f0fdf4;
        color: #166534;
        font-size: 14px;
    }

    @media (max-width: 768px) {
        .cc-content {
            padding: 16px;
        }

        .cc-filter-grid,
        .cc-form-grid,
        .cc-detail-grid {
            grid-template-columns: 1fr;
        }

        .cc-card,
        .cc-info-body {
            padding: 16px;
        }

        .cc-info-header {
            padding: 15px 16px;
        }

        .cc-edit-panel {
            padding: 16px;
        }

        .cc-edit-toggle {
            width: 100%;
        }

        .cc-action-row .button {
            width: 100%;
        }
    }
</style>

{{-- PESAN BERHASIL --}}
@if(session('success'))
    <div class="cc-success-box" role="status">
        <strong>Berhasil!</strong>
        {{ session('success') }}
    </div>
@endif

{{-- PESAN VALIDASI --}}
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

    {{-- HEADER HALAMAN --}}
    <div class="panel-heading">
        <div>
            <p class="eyebrow">CONSIGNMENT CUSTOMER</p>
            <h3>Data Stock Consignment Gudang Customer</h3>
        </div>
    </div>

    <div class="cc-content">

        {{-- FILTER PELANGGAN DAN ITEM --}}
        <form
            method="GET"
            action="{{ route('consignment-customer.index') }}"
        >
            <div class="cc-filter-grid">

                {{-- NAMA PELANGGAN --}}
                <div class="cc-card cc-filter-card">
                    <label
                        for="customer_id"
                        class="cc-card-label"
                    >
                        Nama Pelanggan
                    </label>

                    <select
                        id="customer_id"
                        name="customer_id"
                        class="cc-control"
                        onchange="this.form.submit()"
                    >
                        <option value="">
                            -- Semua Pelanggan --
                        </option>

                        @foreach($customers as $customer)
                            <option
                                value="{{ $customer->id }}"
                                @selected(
                                    $selectedCustomer?->id === $customer->id
                                )
                            >
                                {{ $customer->name }}
                                ({{ $customer->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- DATA ITEM PARTS --}}
                <div class="cc-card cc-filter-card">
                    <label
                        for="product_id"
                        class="cc-card-label"
                    >
                        Data Item Parts Consignment
                    </label>

                    <select
                        id="product_id"
                        name="product_id"
                        class="cc-control"
                        onchange="this.form.submit()"
                    >
                        <option value="">
                            -- Pilih Item Parts --
                        </option>

                        @foreach($products as $product)
                            <option
                                value="{{ $product->id }}"
                                @selected(
                                    $selectedProduct?->id === $product->id
                                )
                            >
                                {{ $product->name }}
                                ({{ $product->sku }})
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>
        </form>

        {{-- INFORMASI PELANGGAN --}}
        @if($selectedCustomer)

            <section class="cc-info-card">

                <div class="cc-info-header">
                    <h3>Informasi Pelanggan</h3>
                </div>

                <div class="cc-info-body">

                    <div class="cc-detail-grid">

                        {{-- NAMA --}}
                        <div class="cc-info-field">
                            <span class="cc-field-label">
                                NAMA PELANGGAN
                            </span>

                            <span class="cc-field-value">
                                {{ $selectedCustomer->name }}
                            </span>
                        </div>

                        {{-- KODE --}}
                        <div class="cc-info-field">
                            <span class="cc-field-label">
                                KODE PELANGGAN
                            </span>

                            <span class="cc-field-value">
                                {{ $selectedCustomer->code }}
                            </span>
                        </div>

                        {{-- ALAMAT --}}
                        <div class="cc-info-field">
                            <span class="cc-field-label">
                                ALAMAT PELANGGAN
                            </span>

                            <span class="cc-field-value">
                                {{ $selectedCustomer->address ?: '-' }}
                            </span>
                        </div>

                    </div>

                    {{-- FORM EDIT PELANGGAN --}}
                    <div class="cc-info-actions">

                        <details
                            class="cc-edit-details"
                            @if($editCustomerHasErrors) open @endif
                        >

                            <summary class="cc-edit-toggle">
                                Edit Pelanggan
                            </summary>

                            <div class="cc-edit-panel">

                                <h3 class="cc-edit-title">
                                    Edit Data Pelanggan
                                </h3>

                                <p class="cc-edit-description">
                                    Ubah nama atau kode pelanggan
                                    melalui formulir berikut.
                                </p>

                                <form
                                    method="POST"
                                    action="{{ route(
                                        'consignment-customer.customers.update',
                                        [
                                            'customer' => $selectedCustomer->id
                                        ]
                                    ) }}"
                                >
                                    @csrf
                                    @method('PUT')

                                    <input
                                        type="hidden"
                                        name="editing_customer_id"
                                        value="{{ $selectedCustomer->id }}"
                                    >

                                    <input
                                        type="hidden"
                                        name="return_product_id"
                                        value="{{ $selectedProduct?->id }}"
                                    >

                                    <div class="cc-form-grid">

                                        {{-- EDIT NAMA --}}
                                        <div>
                                            <label
                                                for="edit_customer_name"
                                                class="cc-form-label"
                                            >
                                                Nama Pelanggan
                                                <span style="color:#dc2626">
                                                    *
                                                </span>
                                            </label>

                                            <input
                                                type="text"
                                                id="edit_customer_name"
                                                name="name"
                                                class="cc-control"
                                                value="{{ $editCustomerHasErrors
                                                    ? old('name')
                                                    : $selectedCustomer->name }}"
                                                maxlength="160"
                                                required
                                                autocomplete="off"
                                            >

                                            @error('name')
                                                <small class="cc-error-text">
                                                    {{ $message }}
                                                </small>
                                            @enderror
                                        </div>

                                        {{-- EDIT KODE --}}
                                        <div>
                                            <label
                                                for="edit_customer_code"
                                                class="cc-form-label"
                                            >
                                                Kode Pelanggan
                                                <span style="color:#dc2626">
                                                    *
                                                </span>
                                            </label>

                                            <input
                                                type="text"
                                                id="edit_customer_code"
                                                name="code"
                                                class="cc-control"
                                                value="{{ $editCustomerHasErrors
                                                    ? old('code')
                                                    : $selectedCustomer->code }}"
                                                maxlength="40"
                                                required
                                                autocomplete="off"
                                            >

                                            <small class="cc-help-text">
                                                Maksimal 40 karakter
                                                dan tidak boleh duplikat.
                                            </small>

                                            @error('code')
                                                <small class="cc-error-text">
                                                    {{ $message }}
                                                </small>
                                            @enderror
                                        </div>

                                    </div>

                                    <div class="cc-edit-note">
                                        Perubahan nama dan kode tidak
                                        mengganti ID pelanggan maupun
                                        mengubah saldo stok consignment.
                                    </div>

                                    <div class="cc-action-row">

                                        <button
                                            type="submit"
                                            class="button button-primary"
                                        >
                                            Simpan Perubahan
                                        </button>

                                        <span class="cc-muted-text">
                                            Tutup panel untuk membatalkan.
                                        </span>

                                    </div>

                                </form>

                            </div>

                        </details>

                    </div>

                </div>

            </section>

        @endif

        {{-- INFORMASI ITEM PARTS --}}
        @if($selectedProduct)

            <section class="cc-info-card">

                <div class="cc-info-header">
                    <h3>Informasi Item Parts</h3>
                </div>

                <div class="cc-info-body">

                    <div class="cc-detail-grid">

                        <div class="cc-info-field">
                            <span class="cc-field-label">
                                NAMA ITEM
                            </span>

                            <span class="cc-field-value">
                                {{ $selectedProduct->name }}
                            </span>
                        </div>

                        <div class="cc-info-field">
                            <span class="cc-field-label">
                                CUSTOMER NO
                            </span>

                            <span class="cc-field-value">
                                {{ $selectedProduct->customer_part_no ?: '-' }}
                            </span>
                        </div>

                        <div class="cc-info-field">
                            <span class="cc-field-label">
                                WAF NO (SKU)
                            </span>

                            <span class="cc-field-value">
                                {{ $selectedProduct->sku }}
                            </span>
                        </div>

                        <div class="cc-info-field">
                            <span class="cc-field-label">
                                UNIT
                            </span>

                            <span class="cc-field-value">
                                {{ $selectedProduct->uom }}
                            </span>
                        </div>

                    </div>

                </div>

            </section>

        @endif

        {{-- TAMBAHKAN ITEM CONSIGNMENT --}}
        @if($selectedCustomer && $selectedProduct)

            <div class="cc-add-item">

                <form
                    method="POST"
                    action="{{ route('consignment-customer.store') }}"
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

            </div>

        @endif

    </div>

    {{-- HEADER TABEL STOCK CONSIGNMENT --}}
    <div class="panel-heading">
        <div>
            <p class="eyebrow">
                DATA STOCK CONSIGNMENT
            </p>

            <h3>
                @if($selectedCustomer)
                    Stock Consignment:
                    {{ $selectedCustomer->name }}
                @else
                    Semua Stock Consignment Customer
                @endif
            </h3>
        </div>
    </div>

    {{-- TABEL STOCK CONSIGNMENT --}}
    <div class="table-wrap">

        <table class="cc-stock-table">

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

                            <td>
                                {{ $item->id }}
                            </td>

                            <td>
                                <strong>
                                    {{ $item->customer?->name ?? '-' }}
                                </strong>

                                <small>
                                    {{ $item->customer?->code ?? '-' }}
                                </small>
                            </td>

                            <td>
                                <strong>
                                    {{ $warehouse->name }}
                                </strong>

                                <small class="mono">
                                    {{ $warehouse->code }}
                                </small>
                            </td>

                            <td>
                                <strong>
                                    {{ $item->product?->name ?? '-' }}
                                </strong>
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
                                    {{ number_format(
                                        $stockQuantity,
                                        4,
                                        ',',
                                        '.'
                                    ) }}
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
