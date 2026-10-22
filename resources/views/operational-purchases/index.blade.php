
@extends('layouts.app')

@section('title', 'Pembelian Kebutuhan Oprasional')
@section('eyebrow', 'PEMBELIAN & BIAYA')
@section('heading', 'Pembelian Kebutuhan Oprasional')

@section('content')

<style>
    .purchase-page {
        display: grid;
        gap: 20px;
        min-width: 0;
    }

    .purchase-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        padding: 6px;
        background: #f3f4f6;
        border-radius: 12px;
    }

    .purchase-tab {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 16px;
        border-radius: 9px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
    }

    .purchase-tab.active {
        background: #fff;
        color: #111827;
        box-shadow: 0 1px 4px rgba(0,0,0,.08);
    }

    .purchase-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }

    .purchase-summary-card {
        padding: 20px;
        min-width: 0;
    }

    .purchase-summary-card small {
        display: block;
        margin-bottom: 10px;
        color: #6b7280;
        font-size: 12px;
    }

    .purchase-summary-card strong {
        display: block;
        font-size: 22px;
        font-weight: 800;
        overflow-wrap: anywhere;
    }

    .purchase-summary-card p {
        margin: 8px 0 0;
        color: #6b7280;
        font-size: 12px;
    }

    .purchase-header-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
        padding: 18px 20px;
    }

    .purchase-header-actions h3 {
        margin: 4px 0 0;
    }

    .purchase-search {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    .purchase-search input {
        min-width: 220px;
        padding: 11px 12px;
        border: 1px solid #d8dde5;
        border-radius: 9px;
        background: #fff;
        color: #111827;
        font: inherit;
    }

    .purchase-table-wrap {
        width: 100%;
        max-width: 100%;
        overflow-x: auto;
    }

    .purchase-table {
        width: 100%;
        min-width: 1050px;
        border-collapse: collapse;
    }

    .purchase-table th {
        padding: 14px 12px;
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
        color: #6b7280;
        font-size: 12px;
        font-weight: 700;
        text-align: left;
        white-space: nowrap;
    }

    .purchase-table td {
        padding: 15px 12px;
        border-bottom: 1px solid #e5e7eb;
        font-size: 13px;
        vertical-align: middle;
    }

    .purchase-table td strong {
        display: block;
    }

    .purchase-table td small {
        display: block;
        margin-top: 5px;
        color: #6b7280;
    }

    .purchase-number {
        font-family: monospace;
        font-weight: 700;
        color: #111827;
        text-decoration: underline;
        text-underline-offset: 3px;
    }

    .purchase-number:hover {
        color: #2563eb;
    }

    .purchase-money {
        text-align: right;
        white-space: nowrap;
        font-weight: 700;
    }

    .purchase-empty {
        padding: 36px 20px !important;
        text-align: center;
        color: #6b7280;
    }

    .purchase-footer {
        padding: 15px 20px;
    }

    .purchase-note {
        padding: 16px 18px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #f9fafb;
        font-size: 12px;
        color: #6b7280;
        line-height: 1.6;
    }

    @media (max-width: 850px) {
        .purchase-summary {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 520px) {
        .purchase-tab {
            width: 100%;
        }

        .purchase-search,
        .purchase-search input {
            width: 100%;
        }
    }
</style>

@php
    $totalDocuments = $purchases->total();

    $pageTotal = $purchases->getCollection()
        ->sum(fn ($purchase) => (float) $purchase->grand_total);

    $pageItems = $purchases->getCollection()
        ->sum(fn ($purchase) => (int) $purchase->total_items);
@endphp

<div class="purchase-page">

    {{-- NAVIGASI PEMBELIAN & BIAYA --}}
    <div class="purchase-tabs">

        <a
            href="{{ route('operational-masters.index') }}"
            class="purchase-tab"
        >
            Data Tempat, Lokasi, Item DLL
        </a>

        <a
            href="{{ route('operational-purchases.index') }}"
            class="purchase-tab active"
        >
            Pembelian Kebutuhan Oprasional
        </a>

        {{-- TAB BIAYA SUDAH AKTIF --}}
        <a
            href="{{ route('operational-expenses.index') }}"
            class="purchase-tab"
        >
            Biaya Oprsional
        </a>

    </div>

    {{-- RINGKASAN --}}
    <div class="purchase-summary">

        <div class="panel purchase-summary-card">
            <small>Total Dokumen</small>

            <strong>
                {{ number_format($totalDocuments, 0, ',', '.') }}
            </strong>

            <p>Jumlah dokumen sesuai pencarian.</p>
        </div>

        <div class="panel purchase-summary-card">
            <small>Total Item Halaman Ini</small>

            <strong>
                {{ number_format($pageItems, 0, ',', '.') }}
            </strong>

            <p>Jumlah baris item pada halaman aktif.</p>
        </div>

        <div class="panel purchase-summary-card">
            <small>Nilai Pembelian Halaman Ini</small>

            <strong>
                Rp {{ number_format($pageTotal, 2, ',', '.') }}
            </strong>

            <p>Total pembelian pada halaman aktif.</p>
        </div>

    </div>

    {{-- DAFTAR DOKUMEN --}}
    <section
        class="panel"
        style="min-width:0;overflow:hidden;"
    >

        <div class="purchase-header-actions">

            <div>
                <p class="eyebrow">DOKUMEN PMB</p>
                <h3>Daftar Pembelian Operasional</h3>
            </div>

            <a
                href="{{ route('operational-purchases.create') }}"
                class="button button-primary"
            >
                + Buat Pembelian
            </a>

        </div>

        <div class="purchase-header-actions">

            <form
                method="GET"
                action="{{ route('operational-purchases.index') }}"
                class="purchase-search"
            >

                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Cari nomor dokumen / tujuan..."
                    aria-label="Cari pembelian"
                >

                <button
                    type="submit"
                    class="button button-ghost"
                >
                    Cari
                </button>

                @if($search !== '')
                    <a
                        href="{{ route('operational-purchases.index') }}"
                        class="button button-ghost"
                    >
                        Reset
                    </a>
                @endif

            </form>

        </div>

        <div class="purchase-table-wrap">

            <table class="purchase-table">

                <thead>
                    <tr>
                        <th>No</th>
                        <th>Doc No</th>
                        <th>Date</th>
                        <th>TO</th>
                        <th>Kebutuhan</th>
                        <th>Katagori</th>
                        <th>Lokasi</th>
                        <th>Total Item</th>
                        <th style="text-align:right;">
                            Grand Total Rp.
                        </th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($purchases as $purchase)

                        <tr>

                            <td>
                                {{ $purchases->firstItem() + $loop->index }}
                            </td>

                            {{-- NOMOR PMB BISA DIKLIK --}}
                            <td>
                                <a
                                    href="{{ route('operational-purchases.show', $purchase) }}"
                                    class="purchase-number"
                                    title="Lihat detail dokumen"
                                >
                                    {{ $purchase->document_number }}
                                </a>
                            </td>

                            <td>
                                {{ $purchase->document_date?->format('d/m/Y') ?? '-' }}
                            </td>

                            <td>
                                <strong>
                                    {{ $purchase->recipient_name }}
                                </strong>

                                <small>
                                    Dibuat oleh:
                                    {{ $purchase->creator?->name ?? '-' }}
                                </small>
                            </td>

                            <td>
                                {{ $purchase->need?->name ?? '-' }}
                            </td>

                            <td>
                                {{ $purchase->category?->name ?? '-' }}
                            </td>

                            <td>
                                <strong>
                                    {{ $purchase->location?->code ?? '-' }}
                                </strong>

                                <small>
                                    {{ $purchase->location?->name ?? '-' }}
                                </small>
                            </td>

                            <td>
                                {{ number_format($purchase->total_items, 0, ',', '.') }}
                                item
                            </td>

                            <td class="purchase-money">
                                Rp {{ number_format((float) $purchase->grand_total, 2, ',', '.') }}
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="9" class="purchase-empty">
                                @if($search !== '')
                                    Tidak ditemukan dokumen dengan kata kunci
                                    "{{ $search }}".
                                @else
                                    Belum ada dokumen pembelian operasional.
                                @endif
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="purchase-footer">
            {{ $purchases->links() }}
        </div>

    </section>

    <div class="purchase-note">
        Klik nomor dokumen PMB untuk melihat detail pembelian,
        daftar item, Qty, harga, Grand Total, dan nama penanggung jawab.
        Cetak PDF akan ditambahkan pada tahap berikutnya.
    </div>

</div>

@endsection
