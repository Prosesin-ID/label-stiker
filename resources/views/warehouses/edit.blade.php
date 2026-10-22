
@extends('layouts.app')

@section('title', 'Edit Gudang')
@section('eyebrow', 'INVENTORY')
@section('heading', 'Edit gudang')

@section('content')

@php
    $selectedWarehouseType = old(
        'warehouse_type',
        $warehouse->warehouse_type ?? 'normal'
    );

    $selectedCustomerId = old(
        'customer_id',
        $warehouse->customer_id
    );

    $hasStockHistory =
        $warehouse->stockMovements()->exists()
        ||
        $warehouse->stockBalances()
            ->where('quantity', '!=', 0)
            ->exists();
@endphp

<div class="edit-product-layout">

    <form
        method="POST"
        action="{{ route('warehouses.update', $warehouse) }}"
        class="panel edit-product-form"
    >
        @csrf
        @method('PUT')

        <div class="panel-heading">
            <div>
                <p class="eyebrow">DATA GUDANG</p>
                <h3>{{ $warehouse->name }}</h3>
            </div>

            <span class="status {{ $warehouse->is_active ? 'done' : 'draft' }}">
                {{ $warehouse->is_active ? 'Aktif' : 'Nonaktif' }}
            </span>
        </div>

        @if($errors->any())
            <div class="error-box edit-error">
                <strong>Periksa data berikut:</strong>

                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="edit-form-body">

            {{-- KODE DAN NAMA GUDANG --}}
            <div class="form-grid">

                <div class="field">
                    <label for="code">
                        Kode gudang <b>*</b>
                    </label>

                    <input
                        id="code"
                        name="code"
                        value="{{ old('code', $warehouse->code) }}"
                        required
                    >
                </div>

                <div class="field">
                    <label for="name">
                        Nama gudang <b>*</b>
                    </label>

                    <input
                        id="name"
                        name="name"
                        value="{{ old('name', $warehouse->name) }}"
                        required
                    >
                </div>

            </div>

            {{-- JENIS GUDANG --}}
            <div class="field">

                <label for="warehouse_type">
                    Jenis gudang <b>*</b>
                </label>

                <select
                    id="warehouse_type"
                    name="warehouse_type"
                    required
                    @disabled($hasStockHistory)
                >
                    <option
                        value="normal"
                        @selected($selectedWarehouseType === 'normal')
                    >
                        Gudang Normal
                    </option>

                    <option
                        value="consignment_waf"
                        @selected($selectedWarehouseType === 'consignment_waf')
                    >
                        Gudang Consignment WAF
                    </option>

                    <option
                        value="consignment_customer"
                        @selected($selectedWarehouseType === 'consignment_customer')
                    >
                        Gudang Consignment Customer
                    </option>
                </select>

                @if($hasStockHistory)
                    <input
                        type="hidden"
                        name="warehouse_type"
                        value="{{ $warehouse->warehouse_type ?? 'normal' }}"
                    >

                    <small>
                        Jenis gudang tidak dapat diubah karena
                        sudah memiliki histori atau saldo stok.
                    </small>
                @endif

            </div>

            {{-- PELANGGAN --}}
            <div
                class="field"
                id="customer-field"
                style="{{ $selectedWarehouseType === 'consignment_customer' ? '' : 'display: none;' }}"
            >

                <label for="customer_id">
                    Nama Pelanggan <b>*</b>
                </label>

                <select
                    id="customer_id"
                    name="customer_id"
                    @disabled($hasStockHistory)
                >
                    <option value="">
                        -- Pilih Pelanggan --
                    </option>

                    @foreach($customers as $customer)
                        <option
                            value="{{ $customer->id }}"
                            @selected(
                                (string) $selectedCustomerId
                                === (string) $customer->id
                            )
                        >
                            {{ $customer->name }}
                            ({{ $customer->code }})
                        </option>
                    @endforeach

                    @if(
                        $warehouse->customer_id
                        &&
                        !$customers->contains(
                            'id',
                            $warehouse->customer_id
                        )
                    )
                        <option
                            value="{{ $warehouse->customer_id }}"
                            selected
                        >
                            {{ $warehouse->customer?->name ?? 'Pelanggan tidak aktif' }}
                        </option>
                    @endif
                </select>

                @if($hasStockHistory)
                    <input
                        type="hidden"
                        name="customer_id"
                        value="{{ $warehouse->customer_id }}"
                    >

                    <small>
                        Pelanggan tidak dapat diganti karena
                        gudang sudah memiliki histori atau saldo stok.
                    </small>
                @else
                    <small>
                        Pilih pelanggan yang menjadi pemilik
                        lokasi gudang consignment.
                    </small>
                @endif

            </div>

            {{-- ALAMAT --}}
            <div class="field">

                <label for="address">
                    Alamat
                </label>

                <input
                    id="address"
                    name="address"
                    value="{{ old('address', $warehouse->address) }}"
                >

            </div>

            {{-- PENGATURAN STOK MINUS --}}
            <label class="active-switch">

                <input
                    type="checkbox"
                    name="allow_negative_stock"
                    value="1"
                    @checked(
                        old(
                            'allow_negative_stock',
                            $warehouse->allow_negative_stock
                        )
                    )
                >

                <span></span>

                <div>
                    <strong>Izinkan stok minus</strong>
                    <small>
                        Matikan jika stok keluar tidak boleh
                        melebihi saldo gudang.
                    </small>
                </div>

            </label>

            {{-- STATUS GUDANG --}}
            <label class="active-switch">

                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    @checked(
                        old(
                            'is_active',
                            $warehouse->is_active
                        )
                    )
                >

                <span></span>

                <div>
                    <strong>Gudang aktif</strong>
                    <small>
                        Gudang aktif bisa dipilih saat transaksi stok.
                    </small>
                </div>

            </label>

        </div>

        {{-- TOMBOL AKSI --}}
        <div class="edit-form-actions">

            <a
                href="{{ route('warehouses.index') }}"
                class="button button-ghost"
            >
                Batal
            </a>

            <button
                type="submit"
                class="button button-primary"
            >
                Simpan perubahan
            </button>

        </div>

    </form>

    {{-- PANEL HISTORI GUDANG --}}
    <aside class="panel edit-product-note">

        <span>&#9432;</span>

        <h3>Histori stok aman</h3>

        <p>
            Gudang yang sudah punya transaksi sebaiknya
            dinonaktifkan, bukan dihapus, supaya riwayat
            stok tetap bisa dibaca.
        </p>

        <dl>

            <div>
                <dt>Saldo stok</dt>
                <dd>
                    {{ $warehouse->stockBalances()->count() }}
                </dd>
            </div>

            <div>
                <dt>Transaksi</dt>
                <dd>
                    {{ $warehouse->stockMovements()->count() }}
                </dd>
            </div>

            <div>
                <dt>Terakhir diubah</dt>
                <dd>
                    {{ $warehouse->updated_at->diffForHumans() }}
                </dd>
            </div>

        </dl>

    </aside>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const typeSelect = document.getElementById('warehouse_type');
    const customerField = document.getElementById('customer-field');
    const customerSelect = document.getElementById('customer_id');

    if (!typeSelect || !customerField || !customerSelect) {
        return;
    }

    function toggleCustomer() {
        const isCustomer = typeSelect.value === 'consignment_customer';

        customerField.style.display = isCustomer ? '' : 'none';

        if (!customerSelect.disabled) {
            customerSelect.required = isCustomer;
        }
    }

    typeSelect.addEventListener('change', toggleCustomer);

    toggleCustomer();

});
</script>

@endsection
