
@extends('layouts.app')

@section('title', 'Pembelian Kebutuhan Oprasional')
@section('eyebrow', 'PEMBELIAN & BIAYA')
@section('heading', 'Pembelian Kebutuhan Oprasional')

@section('content')

<style>
    .op-page {
        display: grid;
        gap: 20px;
    }

    .op-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        padding: 6px;
        background: #f3f4f6;
        border-radius: 12px;
    }

    .op-tab {
        display: inline-flex;
        align-items: center;
        padding: 11px 14px;
        border-radius: 9px;
        text-decoration: none;
        color: #374151;
        font-size: 13px;
        font-weight: 600;
    }

    .op-tab.active {
        background: #fff;
        color: #111827;
        box-shadow: 0 1px 4px rgba(0,0,0,.08);
    }

    .op-section {
        padding: 22px;
    }

    .op-section-title {
        margin: 0 0 18px;
        font-size: 16px;
        font-weight: 700;
    }

    .op-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }

    .op-field {
        display: flex;
        flex-direction: column;
        gap: 7px;
        min-width: 0;
    }

    .op-field label {
        font-size: 13px;
        font-weight: 600;
    }

    .op-field input,
    .op-field select,
    .op-field textarea {
        width: 100%;
        min-width: 0;
        box-sizing: border-box;
        padding: 11px 12px;
        border: 1px solid #d8dde5;
        border-radius: 9px;
        background: #fff;
        color: #111827;
        font: inherit;
    }

    .op-field textarea {
        resize: vertical;
    }

    .op-full {
        grid-column: 1 / -1;
    }

    .op-table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .op-item-table {
        width: 100%;
        min-width: 930px;
        border-collapse: collapse;
    }

    .op-item-table th {
        padding: 12px 10px;
        background: #facc15;
        color: #111827;
        font-size: 12px;
        text-align: left;
        white-space: nowrap;
    }

    .op-item-table td {
        padding: 10px;
        border-bottom: 1px solid #e5e7eb;
        vertical-align: middle;
    }

    .op-item-table input,
    .op-item-table select {
        width: 100%;
        min-width: 0;
        box-sizing: border-box;
        padding: 10px;
        border: 1px solid #d8dde5;
        border-radius: 8px;
        background: #fff;
        font: inherit;
    }

    .op-item-table .op-total {
        font-weight: 700;
        white-space: nowrap;
        text-align: right;
    }

    .op-remove {
        border: 1px solid #fecaca;
        border-radius: 8px;
        background: #fff5f5;
        color: #b91c1c;
        padding: 9px 12px;
        cursor: pointer;
    }

    .op-add-row {
        margin-top: 16px;
    }

    .op-summary {
        display: grid;
        gap: 10px;
        max-width: 360px;
        margin: 22px 0 0 auto;
    }

    .op-summary-line {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e5e7eb;
    }

    .op-summary-line.grand {
        font-size: 17px;
        font-weight: 800;
        border-bottom: 0;
    }

    .op-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        flex-wrap: wrap;
    }

    .op-help {
        color: #6b7280;
        font-size: 12px;
    }

    @media (max-width: 850px) {
        .op-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 540px) {
        .op-grid {
            grid-template-columns: 1fr;
        }

        .op-section {
            padding: 16px;
        }

        .op-actions > * {
            flex: 1;
            text-align: center;
        }
    }
</style>

<div class="op-page">

    {{-- NAVIGASI MODUL --}}
    <div class="op-tabs">

        <a
            href="{{ route('operational-masters.index') }}"
            class="op-tab"
        >
            Data Tempat, Lokasi, Item DLL
        </a>

        <a
            href="{{ route('operational-purchases.index') }}"
            class="op-tab active"
        >
            Pembelian Kebutuhan Oprasional
        </a>

        {{-- TAB BIAYA SUDAH AKTIF --}}
        <a
            href="{{ route('operational-expenses.index') }}"
            class="op-tab"
        >
            Biaya Oprsional
        </a>

    </div>

    {{-- VALIDASI --}}
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

    <form
        id="operationalPurchaseForm"
        method="POST"
        action="{{ route('operational-purchases.store') }}"
        class="op-page"
    >
        @csrf

        {{-- HEADER DOKUMEN --}}
        <section class="panel op-section">

            <p class="eyebrow">
                PT WAF INTERNASIONAL INDONESIA
            </p>

            <h3 class="op-section-title">
                Dokumen Pembelian Oprasional
            </h3>

            <div class="op-grid">

                <div class="op-field">
                    <label>Doc No</label>

                    <input
                        type="text"
                        value="Otomatis setelah disimpan (PMB)"
                        disabled
                    >

                    <small class="op-help">
                        Nomor dokumen dibuat oleh sistem.
                    </small>
                </div>

                <div class="op-field">
                    <label for="document_date">
                        Date *
                    </label>

                    <input
                        id="document_date"
                        type="date"
                        name="document_date"
                        value="{{ old('document_date', now()->toDateString()) }}"
                        required
                    >
                </div>

                <div class="op-field">
                    <label for="recipient_name">
                        TO *
                    </label>

                    <input
                        id="recipient_name"
                        type="text"
                        name="recipient_name"
                        maxlength="200"
                        value="{{ old('recipient_name', 'PT.WAF INTERNASIONAL INDONESIA') }}"
                        required
                    >
                </div>

            </div>

        </section>

        {{-- KEBUTUHAN, KATEGORI, LOKASI --}}
        <section class="panel op-section">

            <h3 class="op-section-title">
                Informasi Pembelian
            </h3>

            <div class="op-grid">

                <div class="op-field">
                    <label for="need_id">
                        Kebutuhan *
                    </label>

                    <select
                        id="need_id"
                        name="need_id"
                        required
                    >
                        <option value="">
                            -- Pilih Kebutuhan --
                        </option>

                        @foreach($needs as $need)
                            <option
                                value="{{ $need->id }}"
                                @selected((string) old('need_id') === (string) $need->id)
                            >
                                {{ $need->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="op-field">
                    <label for="category_id">
                        Katagori *
                    </label>

                    <select
                        id="category_id"
                        name="category_id"
                        required
                    >
                        <option value="">
                            -- Pilih Katagori --
                        </option>

                        @foreach($categories as $category)
                            <option
                                value="{{ $category->id }}"
                                @selected((string) old('category_id') === (string) $category->id)
                            >
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="op-field">
                    <label for="location_id">
                        Lokasi *
                    </label>

                    <select
                        id="location_id"
                        name="location_id"
                        required
                    >
                        <option value="">
                            -- Pilih Lokasi --
                        </option>

                        @foreach($locations as $location)
                            <option
                                value="{{ $location->id }}"
                                @selected((string) old('location_id') === (string) $location->id)
                            >
                                {{ $location->code }}
                                - {{ $location->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>

        </section>

        {{-- DETAIL PEMBELIAN --}}
        <section class="panel op-section">

            <h3 class="op-section-title">
                Detail Item Pembelian
            </h3>

            <p class="op-help">
                Masukkan item operasional, jumlah, harga satuan,
                dan keterangan. Total akan dihitung otomatis.
            </p>

            <div class="op-table-wrap">

                <table class="op-item-table">

                    <thead>
                        <tr>
                            <th style="width:50px;">ITEM</th>
                            <th style="min-width:220px;">ITEM NAME</th>
                            <th style="width:100px;">KODE</th>
                            <th style="width:100px;">QTY</th>
                            <th style="width:150px;">PRICE RP.</th>
                            <th style="width:150px;">TOTAL RP.</th>
                            <th style="min-width:160px;">REMARK</th>
                            <th style="width:65px;">AKSI</th>
                        </tr>
                    </thead>

                    <tbody id="purchaseItemsBody"></tbody>

                </table>

            </div>

            <button
                type="button"
                id="addPurchaseItem"
                class="button button-ghost op-add-row"
            >
                + Tambah Baris Item
            </button>

            <div class="op-summary">

                <div class="op-summary-line">
                    <span>Total Item</span>
                    <strong id="summaryTotalItems">0</strong>
                </div>

                <div class="op-summary-line">
                    <span>Total Qty</span>
                    <strong id="summaryTotalQty">0</strong>
                </div>

                <div class="op-summary-line grand">
                    <span>Grand Total Rp.</span>
                    <strong id="summaryGrandTotal">Rp 0</strong>
                </div>

            </div>

        </section>

        {{-- DESCRIPTION DAN PENANGGUNG JAWAB --}}
        <section class="panel op-section">

            <h3 class="op-section-title">
                Description & Persetujuan
            </h3>

            <div class="op-grid">

                <div class="op-field op-full">
                    <label for="description">
                        Description
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        rows="3"
                        maxlength="5000"
                        placeholder="Catatan atau keterangan pembelian"
                    >{{ old('description') }}</textarea>
                </div>

                <div class="op-field">
                    <label for="approved_by">
                        Approved By
                    </label>

                    <input
                        id="approved_by"
                        type="text"
                        name="approved_by"
                        maxlength="150"
                        value="{{ old('approved_by') }}"
                        placeholder="Nama approver"
                    >
                </div>

                <div class="op-field">
                    <label for="prepared_by">
                        Prepared By
                    </label>

                    <input
                        id="prepared_by"
                        type="text"
                        name="prepared_by"
                        maxlength="150"
                        value="{{ old('prepared_by') }}"
                        placeholder="Nama pembuat"
                    >
                </div>

                <div class="op-field">
                    <label for="received_by">
                        Received By
                    </label>

                    <input
                        id="received_by"
                        type="text"
                        name="received_by"
                        maxlength="150"
                        value="{{ old('received_by') }}"
                        placeholder="Nama penerima"
                    >
                </div>

            </div>

        </section>

        {{-- TOMBOL AKSI --}}
        <div class="op-actions">

            <a
                href="{{ route('operational-purchases.index') }}"
                class="button button-ghost"
            >
                Batal
            </a>

            <button
                type="submit"
                id="savePurchaseButton"
                class="button button-primary"
            >
                Simpan Pembelian Operasional
            </button>

        </div>

    </form>

</div>

{{-- TEMPLATE BARIS ITEM --}}
<template id="purchaseItemRowTemplate">

    <tr class="purchase-item-row">

        <td class="op-line-number">
            1
        </td>

        <td>
            <select
                data-field="operational_item_id"
                class="op-item-select"
                required
            >
                <option value="">
                    -- Pilih Item --
                </option>

                @foreach($items as $item)
                    <option
                        value="{{ $item->id }}"
                        data-code="{{ $item->code }}"
                    >
                        {{ $item->name }}
                    </option>
                @endforeach
            </select>
        </td>

        <td>
            <input
                type="text"
                class="op-item-code"
                value=""
                placeholder="Otomatis"
                readonly
            >
        </td>

        <td>
            <input
                type="number"
                data-field="quantity"
                class="op-item-qty"
                min="0.0001"
                max="999999"
                step="0.0001"
                value="1"
                required
            >
        </td>

        <td>
            <input
                type="number"
                data-field="unit_price"
                class="op-item-price"
                min="0"
                max="9999999999"
                step="0.01"
                value="0"
                required
            >
        </td>

        <td class="op-total">
            Rp 0
        </td>

        <td>
            <input
                type="text"
                data-field="remark"
                class="op-item-remark"
                maxlength="2000"
                placeholder="Keterangan"
            >
        </td>

        <td>
            <button
                type="button"
                class="op-remove"
                title="Hapus baris"
                aria-label="Hapus baris"
            >
                &times;
            </button>
        </td>

    </tr>

</template>

{{-- DATA AWAL BARIS PEMBELIAN --}}
@php
    $initialPurchaseItems = old('items') ?: [[
        'operational_item_id' => '',
        'quantity' => 1,
        'unit_price' => 0,
        'remark' => '',
    ]];
@endphp

<script>
document.addEventListener('DOMContentLoaded', function () {

    const tableBody = document.getElementById(
        'purchaseItemsBody'
    );

    const rowTemplate = document.getElementById(
        'purchaseItemRowTemplate'
    );

    const addButton = document.getElementById(
        'addPurchaseItem'
    );

    const form = document.getElementById(
        'operationalPurchaseForm'
    );

    const totalItemsElement = document.getElementById(
        'summaryTotalItems'
    );

    const totalQtyElement = document.getElementById(
        'summaryTotalQty'
    );

    const grandTotalElement = document.getElementById(
        'summaryGrandTotal'
    );

    /*
    |--------------------------------------------------------------------------
    | DATA PHP KE JAVASCRIPT
    |--------------------------------------------------------------------------
    */

    const initialItems = {{ \Illuminate\Support\Js::from($initialPurchaseItems) }};

    const rupiah = new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    });

    const quantityFormatter = new Intl.NumberFormat('id-ID', {
        maximumFractionDigits: 4
    });

    function getRows() {
        return Array.from(
            tableBody.querySelectorAll('.purchase-item-row')
        );
    }

    function updateNames() {

        const rows = getRows();

        rows.forEach(function (row, index) {

            row.querySelector('.op-line-number').textContent =
                index + 1;

            row.querySelectorAll('[data-field]').forEach(
                function (field) {

                    const fieldName = field.dataset.field;

                    field.name =
                        'items[' + index + '][' + fieldName + ']';
                }
            );
        });
    }

    function updateItemCode(row) {

        const select = row.querySelector('.op-item-select');

        const selectedOption =
            select.options[select.selectedIndex];

        const codeInput = row.querySelector('.op-item-code');

        codeInput.value =
            selectedOption
                ? selectedOption.dataset.code || ''
                : '';
    }

    function calculateTotals() {

        const rows = getRows();

        let totalQuantity = 0;
        let grandTotal = 0;

        rows.forEach(function (row) {

            const quantityInput = row.querySelector(
                '.op-item-qty'
            );

            const priceInput = row.querySelector(
                '.op-item-price'
            );

            const quantity = Number(quantityInput.value) || 0;
            const price = Number(priceInput.value) || 0;

            const lineTotal = Math.round(
                (quantity * price + Number.EPSILON) * 100
            ) / 100;

            totalQuantity += quantity;
            grandTotal += lineTotal;

            row.querySelector('.op-total').textContent =
                rupiah.format(lineTotal);
        });

        totalItemsElement.textContent = rows.length;

        totalQtyElement.textContent =
            quantityFormatter.format(totalQuantity);

        grandTotalElement.textContent =
            rupiah.format(grandTotal);
    }

    function addRow(data = {}) {

        if (getRows().length >= 100) {
            alert('Maksimal 100 baris item per dokumen.');
            return;
        }

        const fragment = rowTemplate.content.cloneNode(true);

        const row = fragment.querySelector(
            '.purchase-item-row'
        );

        row.querySelector('.op-item-select').value =
            data.operational_item_id ?? '';

        row.querySelector('.op-item-qty').value =
            data.quantity ?? 1;

        row.querySelector('.op-item-price').value =
            data.unit_price ?? 0;

        row.querySelector('.op-item-remark').value =
            data.remark ?? '';

        tableBody.appendChild(fragment);

        updateItemCode(row);
        updateNames();
        calculateTotals();
    }

    addButton.addEventListener('click', function () {
        addRow();
    });

    tableBody.addEventListener('change', function (event) {

        const row = event.target.closest(
            '.purchase-item-row'
        );

        if (!row) {
            return;
        }

        if (event.target.classList.contains('op-item-select')) {
            updateItemCode(row);
        }

        calculateTotals();
    });

    tableBody.addEventListener('input', function (event) {

        if (
            event.target.classList.contains('op-item-qty') ||
            event.target.classList.contains('op-item-price')
        ) {
            calculateTotals();
        }
    });

    tableBody.addEventListener('click', function (event) {

        const removeButton = event.target.closest(
            '.op-remove'
        );

        if (!removeButton) {
            return;
        }

        const rows = getRows();

        if (rows.length <= 1) {
            alert('Minimal harus ada satu baris item.');
            return;
        }

        removeButton.closest('.purchase-item-row').remove();

        updateNames();
        calculateTotals();
    });

    form.addEventListener('submit', function () {
        updateNames();
    });

    if (
        initialItems &&
        typeof initialItems === 'object'
    ) {

        const rows = Array.isArray(initialItems)
            ? initialItems
            : Object.values(initialItems);

        if (rows.length > 0) {
            rows.forEach(function (item) {
                addRow(item);
            });
        } else {
            addRow();
        }

    } else {
        addRow();
    }

});
</script>

@endsection
