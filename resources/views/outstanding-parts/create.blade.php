
@extends('layouts.app')

@section('title', 'Tambah Outstanding Parts')
@section('eyebrow', 'STOCK CONSIGNMENT & NORMAL')
@section('heading', 'Tambah Permintaan Parts')

@section('content')

<style>
    .out-create-page {
        display: grid;
        gap: 20px;
        max-width: 1150px;
        margin: 0 auto;
    }

    .out-create-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 14px;
    }

    .out-create-header h3 {
        margin: 0 0 6px;
        font-size: 20px;
    }

    .out-create-header p {
        font-size: 13px;
        color: #6b7280;
        margin: 0;
        line-height: 1.6;
    }

    .out-form-section {
        padding: 24px;
    }

    .out-section-title {
        margin: 0 0 8px;
        font-size: 16px;
        font-weight: 700;
        color: #111827;
    }

    .out-section-description {
        margin: 0 0 20px;
        font-size: 12px;
        line-height: 1.6;
        color: #6b7280;
    }

    .out-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0,1fr));
        gap: 18px;
    }

    .out-form-field {
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-width: 0;
    }

    .out-form-field.full {
        grid-column: 1 / -1;
    }

    .out-form-field label {
        font-size: 13px;
        color: #374151;
        font-weight: 600;
    }

    .out-form-field input,
    .out-form-field select,
    .out-form-field textarea {
        box-sizing: border-box;
        width: 100%;
        min-width: 0;
        padding: 12px 13px;
        font: inherit;
        color: #111827;
        border: 1px solid #d1d5db;
        border-radius: 9px;
        background: #fff;
    }

    .out-form-field input:focus,
    .out-form-field select:focus,
    .out-form-field textarea:focus {
        outline: 2px solid rgba(37,99,235,.18);
        border-color: #2563eb;
    }

    .out-form-field input[readonly] {
        background: #f9fafb;
        color: #6b7280;
    }

    .out-form-field textarea {
        resize: vertical;
    }

    .out-field-help {
        font-size: 11px;
        color: #6b7280;
        line-height: 1.5;
    }

    .out-required {
        color: #dc2626;
    }

    .out-information {
        border: 1px solid #bfdbfe;
        background: #eff6ff;
        color: #1e40af;
        border-radius: 10px;
        padding: 15px 17px;
        font-size: 12px;
        line-height: 1.7;
    }

    .out-form-actions {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 12px;
    }

    .out-errors {
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
        padding: 16px 20px;
        border-radius: 10px;
        font-size: 13px;
    }

    .out-errors ul {
        padding-left: 18px;
        margin: 8px 0 0;
    }

    .out-form-preview {
        padding: 20px;
    }

    .out-form-preview-title {
        color: #6b7280;
        font-size: 12px;
        margin-bottom: 10px;
    }

    .out-form-preview-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0,1fr));
        gap: 14px;
    }

    .out-form-preview-item {
        border-right: 1px solid #e5e7eb;
    }

    .out-form-preview-item:last-child {
        border-right: none;
    }

    .out-form-preview-item small {
        display: block;
        color: #6b7280;
        font-size: 11px;
        margin-bottom: 7px;
    }

    .out-form-preview-item strong {
        display: block;
        color: #111827;
        font-weight: 800;
        font-size: 22px;
    }

    @media(max-width:700px) {
        .out-form-grid {
            grid-template-columns: 1fr;
        }

        .out-form-section {
            padding: 18px;
        }

        .out-form-preview-grid {
            grid-template-columns: 1fr;
        }

        .out-form-preview-item {
            border-right: 0;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 12px;
        }

        .out-form-preview-item:last-child {
            border-bottom: none;
        }
    }
</style>

<div class="out-create-page">

    {{-- HEADER --}}
    <div class="out-create-header">

        <div>
            <h3>Pencatatan Permintaan Customer</h3>

            <p>
                Catat parts yang diminta customer, termasuk
                jumlah permintaan dan jenis supply.
                Sisa outstanding akan dihitung otomatis.
            </p>
        </div>

        <a
            href="{{ route('outstanding-parts.index') }}"
            class="button button-ghost"
        >
            &larr; Kembali ke Daftar
        </a>

    </div>

    {{-- VALIDASI --}}
    @if($errors->any())

        <div class="out-errors">
            <strong>Data belum bisa disimpan:</strong>

            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>

    @endif

    <form
        id="outstandingPartForm"
        method="POST"
        action="{{ route('outstanding-parts.store') }}"
        class="out-create-page"
    >
        @csrf

        {{-- DATA PERMINTAAN --}}
        <section class="panel out-form-section">

            <h3 class="out-section-title">
                Informasi Permintaan
            </h3>

            <p class="out-section-description">
                Masukkan informasi customer dan referensi
                permintaan barang.
            </p>

            <div class="out-form-grid">

                <div class="out-form-field">

                    <label for="request_date">
                        Tanggal Permintaan
                        <span class="out-required">*</span>
                    </label>

                    <input
                        id="request_date"
                        type="date"
                        name="request_date"
                        value="{{ old('request_date', now()->toDateString()) }}"
                        required
                    >

                </div>

                <div class="out-form-field">

                    <label for="supply_type">
                        Jenis Supply
                        <span class="out-required">*</span>
                    </label>

                    <select
                        id="supply_type"
                        name="supply_type"
                        required
                    >
                        <option value="">
                            -- Pilih Jenis Supply --
                        </option>

                        <option
                            value="NORMAL"
                            @selected(old('supply_type') === 'NORMAL')
                        >
                            Normal
                        </option>

                        <option
                            value="CONSIGNMENT"
                            @selected(old('supply_type') === 'CONSIGNMENT')
                        >
                            Consignment
                        </option>

                    </select>

                </div>

                <div class="out-form-field">

                    <label for="customer_name">
                        Nama Customer
                        <span class="out-required">*</span>
                    </label>

                    <input
                        id="customer_name"
                        type="text"
                        name="customer_name"
                        maxlength="200"
                        value="{{ old('customer_name') }}"
                        placeholder="Contoh: PT ABC INDONESIA"
                        required
                    >

                </div>

                <div class="out-form-field">

                    <label for="customer_reference">
                        Nomor Referensi / PO Customer
                    </label>

                    <input
                        id="customer_reference"
                        type="text"
                        name="customer_reference"
                        maxlength="100"
                        value="{{ old('customer_reference') }}"
                        placeholder="Nomor PO atau permintaan customer"
                    >

                </div>

            </div>

        </section>

        {{-- INFORMASI PART --}}
        <section class="panel out-form-section">

            <h3 class="out-section-title">
                Informasi Parts
            </h3>

            <p class="out-section-description">
                Pilih dari Master Product untuk mengambil
                kode dan nama item otomatis, atau isi
                manual jika produk belum terdaftar.
            </p>

            <div class="out-form-grid">

                <div class="out-form-field full">

                    <label for="product_id">
                        Pilih Master Product
                    </label>

                    <select
                        id="product_id"
                        name="product_id"
                    >
                        <option value="">
                            -- Input Manual / Pilih Product --
                        </option>

                        @foreach($products as $product)

                            <option
                                value="{{ $product->id }}"
                                data-sku="{{ $product->sku }}"
                                data-name="{{ $product->name }}"
                                @selected((string) old('product_id') === (string) $product->id)
                            >
                                {{ $product->sku }}
                                - {{ $product->name }}
                            </option>

                        @endforeach

                    </select>

                    <small class="out-field-help">
                        Pilih produk untuk mengisi Part Number
                        dan Part Name secara otomatis.
                    </small>

                </div>

                <div class="out-form-field">

                    <label for="part_number">
                        Part Number
                        <span class="out-required">*</span>
                    </label>

                    <input
                        id="part_number"
                        type="text"
                        name="part_number"
                        maxlength="100"
                        value="{{ old('part_number') }}"
                        placeholder="Kode part / SKU"
                        required
                    >

                </div>

                <div class="out-form-field">

                    <label for="part_name">
                        Part Name
                        <span class="out-required">*</span>
                    </label>

                    <input
                        id="part_name"
                        type="text"
                        name="part_name"
                        maxlength="255"
                        value="{{ old('part_name') }}"
                        placeholder="Nama produk / parts"
                        required
                    >

                </div>

            </div>

        </section>

        {{-- QTY --}}
        <section class="panel out-form-section">

            <h3 class="out-section-title">
                Jumlah Permintaan
            </h3>

            <p class="out-section-description">
                Qty Supplied dimulai dari nol.
                Pemenuhan barang akan dicatat secara
                terpisah agar riwayatnya bisa ditelusuri.
            </p>

            <div class="out-form-grid">

                <div class="out-form-field">

                    <label for="requested_quantity">
                        Qty Requested
                        <span class="out-required">*</span>
                    </label>

                    <input
                        id="requested_quantity"
                        type="number"
                        name="requested_quantity"
                        min="0.0001"
                        max="9999999999"
                        step="0.0001"
                        value="{{ old('requested_quantity', 1) }}"
                        required
                    >

                    <small class="out-field-help">
                        Jumlah total barang yang diminta customer.
                    </small>

                </div>

                <div class="out-form-field">

                    <label for="due_date">
                        Target Tanggal Supply
                    </label>

                    <input
                        id="due_date"
                        type="date"
                        name="due_date"
                        value="{{ old('due_date') }}"
                    >

                    <small class="out-field-help">
                        Opsional. Tanggal target pemenuhan permintaan.
                    </small>

                </div>

                <div class="out-form-field full">

                    <label for="notes">
                        Catatan Tambahan
                    </label>

                    <textarea
                        id="notes"
                        name="notes"
                        maxlength="5000"
                        rows="4"
                        placeholder="Keterangan permintaan, kebutuhan customer, atau informasi follow-up"
                    >{{ old('notes') }}</textarea>

                </div>

            </div>

        </section>

        {{-- PREVIEW --}}
        <div class="panel out-form-preview">

            <div class="out-form-preview-title">
                Ringkasan Permintaan Baru
            </div>

            <div class="out-form-preview-grid">

                <div class="out-form-preview-item">
                    <small>Qty Requested</small>
                    <strong id="previewRequested">0</strong>
                </div>

                <div class="out-form-preview-item">
                    <small>Qty Supplied</small>
                    <strong>0</strong>
                </div>

                <div class="out-form-preview-item">
                    <small>Qty Outstanding</small>
                    <strong id="previewOutstanding">0</strong>
                </div>

            </div>

        </div>

        {{-- INFORMASI --}}
        <div class="out-information">
            Setelah disimpan, permintaan akan berstatus
            <strong>PENDING</strong>.
            Pencatatan ini belum membuat Delivery Order
            dan belum mengurangi stok gudang secara otomatis.
        </div>

        {{-- AKSI --}}
        <div class="out-form-actions">

            <a
                href="{{ route('outstanding-parts.index') }}"
                class="button button-ghost"
            >
                Batal
            </a>

            <button
                id="saveOutstandingPart"
                type="submit"
                class="button button-primary"
            >
                Simpan Permintaan Parts
            </button>

        </div>

    </form>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const productSelect = document.getElementById('product_id');
    const partNumber = document.getElementById('part_number');
    const partName = document.getElementById('part_name');

    const quantityInput = document.getElementById(
        'requested_quantity'
    );

    const previewRequested = document.getElementById(
        'previewRequested'
    );

    const previewOutstanding = document.getElementById(
        'previewOutstanding'
    );

    const quantityFormatter = new Intl.NumberFormat('id-ID', {
        maximumFractionDigits: 4
    });

    /*
    |--------------------------------------------------------------------------
    | MASTER PRODUCT
    |--------------------------------------------------------------------------
    */

    function syncMasterProduct(replaceValues) {

        const option = productSelect.selectedOptions[0];

        const selectedProduct = option && option.value !== '';

        if (selectedProduct) {

            if (replaceValues || partNumber.value.trim() === '') {
                partNumber.value = option.dataset.sku || '';
            }

            if (replaceValues || partName.value.trim() === '') {
                partName.value = option.dataset.name || '';
            }

            partNumber.readOnly = true;
            partName.readOnly = true;

        } else {

            partNumber.readOnly = false;
            partName.readOnly = false;

        }
    }

    productSelect.addEventListener('change', function () {

        syncMasterProduct(true);

    });

    /*
    |--------------------------------------------------------------------------
    | PREVIEW QTY
    |--------------------------------------------------------------------------
    */

    function updateQuantityPreview() {

        const quantity = Number(quantityInput.value);

        const requested = Number.isFinite(quantity) && quantity > 0
            ? quantity
            : 0;

        previewRequested.textContent =
            quantityFormatter.format(requested);

        previewOutstanding.textContent =
            quantityFormatter.format(requested);
    }

    quantityInput.addEventListener(
        'input',
        updateQuantityPreview
    );

    /*
    |--------------------------------------------------------------------------
    | INITIALIZE
    |--------------------------------------------------------------------------
    */

    syncMasterProduct(false);
    updateQuantityPreview();

});
</script>

@endsection
