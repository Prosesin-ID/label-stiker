
@extends('layouts.app')

@section('title', 'Outstanding Parts #' . $part->id)
@section('eyebrow', 'STOCK NORMAL & CONSIGNMENT')
@section('heading', 'Detail Outstanding Parts')

@section('content')

<style>
.os-wrap {
    display: grid;
    gap: 18px;
    min-width: 0;
}
.os-top {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    align-items: center;
}
.os-card {
    padding: 22px;
}
.os-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
}
.os-stat {
    padding: 16px;
    border: 1px solid #e5e7eb;
    border-radius: 9px;
}
.os-stat small {
    display: block;
    color: #6b7280;
    margin-bottom: 8px;
}
.os-stat strong {
    font-size: 24px;
}
.os-fields {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
}
.os-field {
    display: grid;
    gap: 6px;
}
.os-field label {
    font-size: 12px;
    font-weight: 600;
}
.os-field input,
.os-field textarea {
    width: 100%;
    box-sizing: border-box;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 11px;
    font: inherit;
    background: #fff;
    color: #111827;
}
.os-field textarea {
    resize: vertical;
}
.os-wide {
    grid-column: 1 / -1;
}
.os-table-wrap {
    overflow-x: auto;
}
.os-table {
    border-collapse: collapse;
    width: 100%;
    min-width: 650px;
}
.os-table th,
.os-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
    font-size: 13px;
}
.os-table th {
    background: #f9fafb;
}
.os-muted {
    color: #6b7280;
    font-size: 12px;
}
.os-error {
    padding: 12px;
    border: 1px solid #fca5a5;
    background: #fef2f2;
    border-radius: 9px;
}
.os-success {
    padding: 12px;
    border: 1px solid #86efac;
    background: #f0fdf4;
    border-radius: 9px;
}
@media(max-width:850px) {
    .os-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media(max-width:520px) {
    .os-grid,
    .os-fields {
        grid-template-columns: 1fr;
    }
}
</style>

@php
    $qtyFormat = function ($number) {
        return rtrim(
            rtrim(
                number_format((float) $number, 4, ',', '.'),
                '0'
            ),
            ','
        );
    };

    $outstanding = $part->outstanding_quantity;
@endphp

<div class="os-wrap">

    <div class="os-top">

        <a
            class="button button-ghost"
            href="{{ route('outstanding-parts.index') }}"
        >
            &larr; Kembali ke Outstanding Parts
        </a>

        <strong>
            Permintaan #{{ $part->id }}
            · {{ $part->fulfillment_status }}
        </strong>

    </div>

    @if(session('success'))
        <div class="os-success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())

        <div class="os-error">
            <strong>Data belum valid:</strong>

            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>

    @endif

    {{-- INFORMASI PERMINTAAN --}}
    <section class="panel os-card">

        <h3>{{ $part->customer_name }}</h3>

        <p class="os-muted">
            {{ $part->supply_type }}
            · Referensi customer:
            {{ $part->customer_reference ?: '-' }}
            · {{ $part->request_date?->format('d/m/Y') }}
        </p>

        <p>
            <strong>{{ $part->part_number }}</strong>
            — {{ $part->part_name }}
        </p>

        <p class="os-muted">
            Target supply:
            {{ $part->due_date?->format('d/m/Y') ?? '-' }}

            · Dibuat oleh
            {{ $part->creator?->name ?? '-' }}
        </p>

        @if($part->notes)
            <p>{{ $part->notes }}</p>
        @endif

        <div class="os-grid">

            <div class="os-stat">
                <small>Qty Diminta</small>

                <strong>
                    {{ $qtyFormat($part->requested_quantity) }}
                </strong>
            </div>

            <div class="os-stat">
                <small>Qty Tersuplai</small>

                <strong>
                    {{ $qtyFormat($part->supplied_quantity) }}
                </strong>
            </div>

            <div class="os-stat">
                <small>Outstanding</small>

                <strong>
                    {{ $qtyFormat($outstanding) }}
                </strong>
            </div>

            <div class="os-stat">
                <small>Status</small>

                <strong style="font-size:17px">
                    {{ $part->fulfillment_status }}
                </strong>
            </div>

        </div>

    </section>

    {{-- INPUT PEMENUHAN --}}
    <section class="panel os-card">

        <h3>Catat Pemenuhan Parts</h3>

        <p class="os-muted">
            Catatan ini untuk supply yang benar-benar sudah
            terjadi. Tidak membuat DO baru atau mengurangi
            stok gudang secara otomatis.
        </p>

        @if($part->fulfillment_status === 'COMPLETED')

            <p>
                <strong>
                    Permintaan sudah terpenuhi seluruhnya.
                </strong>

                Tidak bisa menambahkan Qty lagi.
            </p>

        @else

            <form
                method="POST"
                action="{{ route('outstanding-parts.fulfillments.store', $part) }}"
                class="os-fields"
            >
                @csrf

                <div class="os-field">

                    <label for="supply_date">
                        Tanggal Supply *
                    </label>

                    <input
                        id="supply_date"
                        type="date"
                        name="supply_date"
                        value="{{ old('supply_date', now()->toDateString()) }}"
                        required
                    >

                </div>

                <div class="os-field">

                    <label for="quantity">
                        Qty Disuplai *
                        (Maks. {{ $qtyFormat($outstanding) }})
                    </label>

                    <input
                        id="quantity"
                        type="number"
                        name="quantity"
                        min="0.0001"
                        step="0.0001"
                        max="{{ $outstanding }}"
                        value="{{ old('quantity') }}"
                        required
                    >

                </div>

                <div class="os-field os-wide">

                    <label for="reference_number">
                        Nomor DO / Surat Jalan / Referensi Supply *
                    </label>

                    <input
                        id="reference_number"
                        type="text"
                        name="reference_number"
                        maxlength="100"
                        value="{{ old('reference_number') }}"
                        placeholder="Contoh: DO-2026-001"
                        required
                    >

                </div>

                <div class="os-field os-wide">

                    <label for="notes">
                        Catatan
                    </label>

                    <textarea
                        id="notes"
                        name="notes"
                        rows="2"
                        maxlength="5000"
                    >{{ old('notes') }}</textarea>

                </div>

                <div class="os-wide">

                    <button
                        class="button button-primary"
                        type="submit"
                    >
                        Simpan Riwayat Supply
                    </button>

                </div>

            </form>

        @endif

    </section>

    {{-- RIWAYAT --}}
    <section class="panel os-card">

        <h3>
            Histori Pemenuhan
            ({{ $fulfillments->count() }})
        </h3>

        <div class="os-table-wrap">

            <table class="os-table">

                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Qty</th>
                        <th>Referensi DO / Supply</th>
                        <th>Dicatat Oleh</th>
                        <th>Catatan</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($fulfillments as $record)

                        <tr>

                            <td>
                                {{ $record->supply_date?->format('d/m/Y') }}
                            </td>

                            <td>
                                {{ $qtyFormat($record->quantity) }}
                            </td>

                            <td>
                                {{ $record->reference_number }}
                            </td>

                            <td>
                                {{ $record->creator?->name ?? '-' }}
                            </td>

                            <td>
                                {{ $record->notes ?: '-' }}
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="5"
                                style="text-align:center"
                            >
                                Belum ada histori pemenuhan.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </section>

</div>

@endsection
