
@extends('layouts.app')

@section('title', 'Master Gudang')
@section('eyebrow', 'INVENTORY')
@section('heading', 'Master gudang')

@section('content')

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

<div class="catalog-layout">

    <section class="panel catalog-panel">

        <div class="panel-heading">
            <div>
                <p class="eyebrow">DATABASE GUDANG</p>
                <h3>{{ $warehouses->total() }} gudang tersimpan</h3>
            </div>

            <form
                method="GET"
                action="{{ route('warehouses.index') }}"
                class="table-search"
            >
                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Cari kode, nama, alamat..."
                >
                <button type="submit">&#8981;</button>
            </form>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Gudang</th>
                        <th>Jenis Gudang</th>
                        <th>Pelanggan</th>
                        <th>Alamat</th>
                        <th>Stok minus</th>
                        <th>Relasi stok</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($warehouses as $warehouse)
                        <tr>
                            <td>
                                <strong>{{ $warehouse->name }}</strong>
                                <small class="mono">{{ $warehouse->code }}</small>
                            </td>

                            <td>
                                @switch($warehouse->warehouse_type)
                                    @case('consignment_waf')
                                        Consignment WAF
                                        @break

                                    @case('consignment_customer')
                                        Consignment Customer
                                        @break

                                    @default
                                        Gudang Normal
                                @endswitch
                            </td>

                            <td>
                                {{ $warehouse->customer?->name ?? '-' }}
                            </td>

                            <td>
                                {{ $warehouse->address ?: '-' }}
                            </td>

                            <td>
                                <span class="status {{ $warehouse->allow_negative_stock ? 'draft' : 'done' }}">
                                    {{ $warehouse->allow_negative_stock ? 'Diizinkan' : 'Ditahan' }}
                                </span>
                            </td>

                            <td>
                                <strong>{{ $warehouse->stock_balances_count }}</strong>
                                <small>{{ $warehouse->stock_movements_count }} transaksi</small>
                            </td>

                            <td>
                                <span class="status {{ $warehouse->is_active ? 'done' : 'draft' }}">
                                    {{ $warehouse->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>

                            <td>
                                <div class="row-actions">
                                    <a
                                        href="{{ route('warehouses.edit', $warehouse) }}"
                                        class="action-button"
                                        title="Edit gudang"
                                    >&#9998;</a>

                                    <form
                                        method="POST"
                                        action="{{ route('warehouses.destroy', $warehouse) }}"
                                        onsubmit="return confirm('Hapus gudang ini dari master gudang?')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="action-button danger"
                                            title="Hapus gudang"
                                        >&times;</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-state">
                                Belum ada gudang. Tambahkan gudang pertama dari form kanan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">
            {{ $warehouses->links() }}
        </div>

    </section>

    <aside class="panel add-product-panel">

        <p class="eyebrow">TAMBAH CEPAT</p>
        <h3>Gudang baru</h3>
        <p>
            Tambahkan gudang normal, gudang consignment WAF,
            atau gudang consignment pelanggan.
        </p>

        <form
            method="POST"
            action="{{ route('warehouses.store') }}"
        >
            @csrf

            <label for="code">Kode gudang *</label>
            <input
                id="code"
                name="code"
                value="{{ old('code') }}"
                placeholder="MAIN"
                required
            >

            <label for="name">Nama gudang *</label>
            <input
                id="name"
                name="name"
                value="{{ old('name') }}"
                placeholder="Gudang Utama"
                required
            >

            <label for="warehouse_type">Jenis gudang *</label>
            <select
                id="warehouse_type"
                name="warehouse_type"
                required
            >
                <option value="normal" @selected(old('warehouse_type', 'normal') === 'normal')>
                    Gudang Normal
                </option>

                <option value="consignment_waf" @selected(old('warehouse_type') === 'consignment_waf')>
                    Gudang Consignment WAF
                </option>

                <option value="consignment_customer" @selected(old('warehouse_type') === 'consignment_customer')>
                    Gudang Consignment Customer
                </option>
            </select>

            <div
                id="customer-field"
                style="{{ old('warehouse_type', 'normal') === 'consignment_customer' ? '' : 'display:none;' }}"
            >
                <label for="customer_id">Nama Pelanggan *</label>

                <select
                    id="customer_id"
                    name="customer_id"
                >
                    <option value="">-- Pilih Pelanggan --</option>

                    @foreach($customers as $customer)
                        <option
                            value="{{ $customer->id }}"
                            @selected((string) old('customer_id') === (string) $customer->id)
                        >
                            {{ $customer->name }} ({{ $customer->code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <label for="address">Alamat</label>
            <input
                id="address"
                name="address"
                value="{{ old('address') }}"
                placeholder="Lokasi gudang"
            >

            <label class="active-switch">
                <input
                    type="checkbox"
                    name="allow_negative_stock"
                    value="1"
                    @checked(old('allow_negative_stock'))
                >
                <span></span>
                <div>
                    <strong>Izinkan stok minus</strong>
                    <small>Umumnya dimatikan untuk stok fisik.</small>
                </div>
            </label>

            <label class="active-switch">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    @checked(old('is_active', true))
                >
                <span></span>
                <div>
                    <strong>Gudang aktif</strong>
                    <small>Gudang aktif bisa dipilih saat transaksi stok.</small>
                </div>
            </label>

            <button
                type="submit"
                class="button button-primary button-wide"
            >
                Simpan gudang
            </button>

        </form>

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
        customerSelect.required = isCustomer;
        customerSelect.disabled = !isCustomer;
    }

    typeSelect.addEventListener('change', toggleCustomer);
    toggleCustomer();
});
</script>

@endsection
