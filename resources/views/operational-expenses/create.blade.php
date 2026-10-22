
@extends('layouts.app')

@section('title', 'Tambah Biaya Oprsional')
@section('eyebrow', 'PEMBELIAN & BIAYA')
@section('heading', 'Tambah Biaya Oprsional')

@section('content')

<style>
    .bio-form-page {
        display: grid;
        gap: 20px;
        min-width: 0;
    }

    .bio-form-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        background: #f3f4f6;
        padding: 6px;
        border-radius: 12px;
    }

    .bio-form-tab {
        padding: 11px 14px;
        border-radius: 9px;
        color: #374151;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
    }

    .bio-form-tab.active {
        background: #fff;
        color: #111827;
        box-shadow: 0 1px 4px rgba(0,0,0,.08);
    }

    .bio-form-section {
        padding: 22px;
    }

    .bio-form-section h3 {
        margin: 0 0 18px;
        font-size: 16px;
    }

    .bio-form-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0,1fr));
        gap: 16px;
    }

    .bio-form-field {
        display: flex;
        flex-direction: column;
        gap: 7px;
        min-width: 0;
    }

    .bio-form-field label {
        font-size: 13px;
        font-weight: 600;
    }

    .bio-form-field input,
    .bio-form-field select,
    .bio-form-field textarea {
        width: 100%;
        min-width: 0;
        padding: 11px 12px;
        box-sizing: border-box;
        border: 1px solid #d1d5db;
        border-radius: 9px;
        background: #fff;
        color: #111827;
        font: inherit;
    }

    .bio-form-field textarea {
        resize: vertical;
    }

    .bio-form-full {
        grid-column: 1 / -1;
    }

    .bio-items-wrap {
        overflow-x: auto;
    }

    .bio-items-table {
        width: 100%;
        min-width: 900px;
        border-collapse: collapse;
    }

    .bio-items-table th {
        background: #facc15;
        color: #111827;
        padding: 12px 10px;
        font-size: 12px;
        text-align: left;
    }

    .bio-items-table td {
        padding: 10px;
        border-bottom: 1px solid #e5e7eb;
        vertical-align: middle;
    }

    .bio-items-table input,
    .bio-items-table select {
        width: 100%;
        box-sizing: border-box;
        padding: 10px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        background: #fff;
        color: #111827;
        font: inherit;
    }

    .bio-line-total {
        text-align: right;
        font-weight: 700;
        white-space: nowrap;
    }

    .bio-remove-row {
        border: 1px solid #fecaca;
        background: #fff5f5;
        color: #b91c1c;
        border-radius: 8px;
        padding: 9px 12px;
        cursor: pointer;
    }

    .bio-add-row {
        margin-top: 16px;
    }

    .bio-form-summary {
        max-width: 350px;
        margin: 24px 0 0 auto;
        display: grid;
        gap: 12px;
    }

    .bio-summary-row {
        display: flex;
        justify-content: space-between;
        gap: 15px;
        padding-bottom: 12px;
        border-bottom: 1px solid #e5e7eb;
    }

    .bio-summary-row.grand {
        font-weight: 800;
        font-size: 17px;
    }

    .bio-form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        flex-wrap: wrap;
    }

    .bio-form-note {
        font-size: 12px;
        color: #6b7280;
    }

    @media(max-width: 850px) {
        .bio-form-grid {
            grid-template-columns: repeat(2,minmax(0,1fr));
        }
    }

    @media(max-width: 560px) {
        .bio-form-grid {
            grid-template-columns: 1fr;
        }

        .bio-form-section {
            padding: 16px;
        }

        .bio-form-tab {
            width: 100%;
        }
    }
</style>

@php
    $initialExpenseItems = old('items') ?: [[
        'operational_item_id' => '',
        'item_name' => '',
        'expense_amount' => 0,
        'remark' => '',
    ]];
@endphp

<div class="bio-form-page">

    <div class="bio-form-tabs">

        <a
            class="bio-form-tab"
            href="{{ route('operational-masters.index') }}"
        >
            Data Tempat, Lokasi, Item DLL
        </a>

        <a
            class="bio-form-tab"
            href="{{ route('operational-purchases.index') }}"
        >
            Pembelian Kebutuhan Oprasional
        </a>

        <a
            class="bio-form-tab active"
            href="{{ route('operational-expenses.index') }}"
        >
            Biaya Oprsional
        </a>

    </div>

    @if($errors->any())
        <div
            style="padding:16px;border:1px solid #fca5a5;
            border-radius:10px;background:#fef2f2;color:#991b1b;"
        >
            <strong>Periksa data berikut:</strong>

            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        id="bioExpenseForm"
        method="POST"
        action="{{ route('operational-expenses.store') }}"
        class="bio-form-page"
    >
        @csrf

        {{-- HEADER --}}
        <section class="panel bio-form-section">

            <p class="eyebrow">PT WAF INTERNASIONAL INDONESIA</p>
            <h3>Dokumen Biaya Oprsional</h3>

            <div class="bio-form-grid">

                <div class="bio-form-field">
                    <label>Doc No</label>

                    <input
                        type="text"
                        value="Otomatis setelah disimpan (BIO)"
                        disabled
                    >
                </div>

                <div class="bio-form-field">
                    <label for="bio_date">Date *</label>

                    <input
                        id="bio_date"
                        type="date"
                        name="document_date"
                        value="{{ old('document_date', now()->toDateString()) }}"
                        required
                    >
                </div>

                <div class="bio-form-field">
                    <label for="bio_recipient">TO *</label>

                    <input
                        id="bio_recipient"
                        type="text"
                        name="recipient_name"
                        maxlength="200"
                        value="{{ old('recipient_name', 'PT.WAF INTERNASIONAL INDONESIA') }}"
                        required
                    >
                </div>

            </div>

        </section>

        {{-- MASTER --}}
        <section class="panel bio-form-section">

            <h3>Informasi Biaya Operasional</h3>

            <div class="bio-form-grid">

                <div class="bio-form-field">
                    <label for="bio_need">Kebutuhan *</label>

                    <select id="bio_need" name="need_id" required>
                        <option value="">-- Pilih Kebutuhan --</option>

                        @foreach($needs as $need)
                            <option
                                value="{{ $need->id }}"
                                @selected((string)old('need_id') === (string)$need->id)
                            >
                                {{ $need->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="bio-form-field">
                    <label for="bio_category">Katagori *</label>

                    <select id="bio_category" name="category_id" required>
                        <option value="">-- Pilih Katagori --</option>

                        @foreach($categories as $category)
                            <option
                                value="{{ $category->id }}"
                                @selected((string)old('category_id') === (string)$category->id)
                            >
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="bio-form-field">
                    <label for="bio_location">Lokasi *</label>

                    <select id="bio_location" name="location_id" required>
                        <option value="">-- Pilih Lokasi --</option>

                        @foreach($locations as $location)
                            <option
                                value="{{ $location->id }}"
                                @selected((string)old('location_id') === (string)$location->id)
                            >
                                {{ $location->code }} - {{ $location->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>

        </section>

        {{-- DETAIL BIAYA --}}
        <section class="panel bio-form-section">

            <h3>Detail Biaya Oprsional</h3>

            <p class="bio-form-note">
                Pilih item dari master atau isi nama item secara manual.
                Total biaya dihitung otomatis.
            </p>

            <div class="bio-items-wrap">

                <table class="bio-items-table">

                    <thead>
                        <tr>
                            <th style="width:55px;">ITEM</th>
                            <th style="min-width:180px;">PILIH MASTER</th>
                            <th style="min-width:200px;">ITEM NAME</th>
                            <th style="width:145px;">BIAYA RP.</th>
                            <th style="width:145px;">TOTAL RP.</th>
                            <th style="min-width:160px;">REMARK</th>
                            <th style="width:65px;">AKSI</th>
                        </tr>
                    </thead>

                    <tbody id="bioItemsBody"></tbody>

                </table>

            </div>

            <button
                type="button"
                id="bioAddItem"
                class="button button-ghost bio-add-row"
            >
                + Tambah Baris Biaya
            </button>

            <div class="bio-form-summary">

                <div class="bio-summary-row">
                    <span>Total Item</span>
                    <strong id="bioTotalItems">0</strong>
                </div>

                <div class="bio-summary-row grand">
                    <span>Grand Total Rp.</span>
                    <strong id="bioGrandTotal">Rp 0</strong>
                </div>

            </div>

        </section>

        {{-- KETERANGAN DAN PERSETUJUAN --}}
        <section class="panel bio-form-section">

            <h3>Description & Persetujuan</h3>

            <div class="bio-form-grid">

                <div class="bio-form-field bio-form-full">
                    <label for="bio_description">Description</label>

                    <textarea
                        id="bio_description"
                        name="description"
                        maxlength="5000"
                        rows="3"
                        placeholder="Keterangan biaya operasional"
                    >{{ old('description') }}</textarea>
                </div>

                <div class="bio-form-field">
                    <label for="bio_approved">Approved By</label>

                    <input
                        id="bio_approved"
                        type="text"
                        name="approved_by"
                        maxlength="150"
                        value="{{ old('approved_by') }}"
                    >
                </div>

                <div class="bio-form-field">
                    <label for="bio_prepared">Prepared By</label>

                    <input
                        id="bio_prepared"
                        type="text"
                        name="prepared_by"
                        maxlength="150"
                        value="{{ old('prepared_by') }}"
                    >
                </div>

                <div class="bio-form-field">
                    <label for="bio_received">Received By</label>

                    <input
                        id="bio_received"
                        type="text"
                        name="received_by"
                        maxlength="150"
                        value="{{ old('received_by') }}"
                    >
                </div>

            </div>

        </section>

        <div class="bio-form-actions">

            <a
                class="button button-ghost"
                href="{{ route('operational-expenses.index') }}"
            >
                Batal
            </a>

            <button
                id="bioSaveButton"
                type="submit"
                class="button button-primary"
            >
                Simpan Biaya Operasional
            </button>

        </div>

    </form>

</div>

{{-- TEMPLATE BARIS DINAMIS --}}
<template id="bioItemRowTemplate">

    <tr class="bio-item-row">

        <td class="bio-line-number">1</td>

        <td>
            <select
                data-field="operational_item_id"
                class="bio-master-select"
            >
                <option value="">-- Manual / Pilih Item --</option>

                @foreach($items as $item)
                    <option
                        value="{{ $item->id }}"
                        data-name="{{ $item->name }}"
                    >
                        {{ $item->name }}
                    </option>
                @endforeach
            </select>
        </td>

        <td>
            <input
                type="text"
                data-field="item_name"
                class="bio-item-name"
                maxlength="200"
                placeholder="Nama item biaya"
                required
            >
        </td>

        <td>
            <input
                type="number"
                data-field="expense_amount"
                class="bio-expense-amount"
                min="0"
                max="99999999999999999"
                step="0.01"
                value="0"
                required
            >
        </td>

        <td class="bio-line-total">
            Rp 0
        </td>

        <td>
            <input
                type="text"
                data-field="remark"
                class="bio-item-remark"
                maxlength="2000"
                placeholder="Keterangan"
            >
        </td>

        <td>
            <button
                type="button"
                class="bio-remove-row"
                title="Hapus baris"
            >
                &times;
            </button>
        </td>

    </tr>

</template>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const body = document.getElementById('bioItemsBody');
    const template = document.getElementById('bioItemRowTemplate');
    const addButton = document.getElementById('bioAddItem');
    const form = document.getElementById('bioExpenseForm');

    const itemCountElement = document.getElementById('bioTotalItems');
    const grandTotalElement = document.getElementById('bioGrandTotal');

    const initialItems =
        {{ \Illuminate\Support\Js::from($initialExpenseItems) }};

    const rupiah = new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    });

    function getRows() {
        return Array.from(
            body.querySelectorAll('.bio-item-row')
        );
    }

    function updateNames() {
        getRows().forEach(function (row, index) {

            row.querySelector('.bio-line-number').textContent =
                index + 1;

            row.querySelectorAll('[data-field]').forEach(function (field) {
                field.name =
                    'items[' + index + '][' + field.dataset.field + ']';
            });
        });
    }

    function calculateTotals() {
        let grandTotalCents = 0;

        const rows = getRows();

        rows.forEach(function (row) {
            const amountInput =
                row.querySelector('.bio-expense-amount');

            const amount = Number(amountInput.value);

            const validAmount =
                Number.isFinite(amount) && amount >= 0
                    ? amount
                    : 0;

            const amountCents =
                Math.round((validAmount + Number.EPSILON) * 100);

            grandTotalCents += amountCents;

            row.querySelector('.bio-line-total').textContent =
                rupiah.format(amountCents / 100);
        });

        itemCountElement.textContent = rows.length;

        grandTotalElement.textContent =
            rupiah.format(grandTotalCents / 100);
    }

    function addRow(data = {}) {

        if (getRows().length >= 100) {
            alert('Maksimal 100 baris biaya.');
            return;
        }

        const fragment = template.content.cloneNode(true);
        const row = fragment.querySelector('.bio-item-row');

        row.querySelector('.bio-master-select').value =
            data.operational_item_id ?? '';

        row.querySelector('.bio-item-name').value =
            data.item_name ?? '';

        row.querySelector('.bio-expense-amount').value =
            data.expense_amount ?? 0;

        row.querySelector('.bio-item-remark').value =
            data.remark ?? '';

        body.appendChild(fragment);

        updateNames();
        calculateTotals();
    }

    addButton.addEventListener('click', function () {
        addRow();
    });

    body.addEventListener('change', function (event) {

        if (event.target.classList.contains('bio-master-select')) {

            const row = event.target.closest('.bio-item-row');
            const option = event.target.selectedOptions[0];

            const itemName = row.querySelector('.bio-item-name');

            if (option && option.value !== '') {
                itemName.value = option.dataset.name || '';
            }
        }

        calculateTotals();
    });

    body.addEventListener('input', function (event) {

        if (event.target.classList.contains('bio-expense-amount')) {
            calculateTotals();
        }

        if (event.target.classList.contains('bio-item-name')) {

            const row = event.target.closest('.bio-item-row');

            const master = row.querySelector('.bio-master-select');

            const selectedOption = master.selectedOptions[0];

            if (
                selectedOption &&
                selectedOption.value !== '' &&
                event.target.value !== (selectedOption.dataset.name || '')
            ) {
                master.value = '';
            }
        }
    });

    body.addEventListener('click', function (event) {

        const button = event.target.closest('.bio-remove-row');

        if (!button) {
            return;
        }

        if (getRows().length <= 1) {
            alert('Minimal harus ada satu baris biaya.');
            return;
        }

        button.closest('.bio-item-row').remove();

        updateNames();
        calculateTotals();
    });

    form.addEventListener('submit', function () {
        updateNames();
    });

    const restoredItems = Array.isArray(initialItems)
        ? initialItems
        : Object.values(initialItems || {});

    if (restoredItems.length > 0) {
        restoredItems.forEach(function (item) {
            addRow(item);
        });
    } else {
        addRow();
    }

});
</script>

@endsection
