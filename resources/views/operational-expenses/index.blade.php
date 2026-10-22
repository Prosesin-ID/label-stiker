
@extends('layouts.app')

@section('title', 'Biaya Oprsional')
@section('eyebrow', 'PEMBELIAN & BIAYA')
@section('heading', 'Biaya Oprsional')

@section('content')
<style>
    .bio-page {
        display: grid;
        gap: 20px;
        min-width: 0;
    }

    .bio-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        padding: 6px;
        background: #f3f4f6;
        border-radius: 12px;
    }

    .bio-tab {
        display: inline-flex;
        align-items: center;
        padding: 11px 14px;
        border-radius: 9px;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        text-decoration: none;
    }

    .bio-tab.active {
        background: #fff;
        color: #111827;
        box-shadow: 0 1px 4px rgba(0,0,0,.08);
    }

    .bio-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0,1fr));
        gap: 16px;
    }

    .bio-stat {
        padding: 20px;
        min-width: 0;
    }

    .bio-stat small {
        display: block;
        color: #6b7280;
        margin-bottom: 8px;
    }

    .bio-stat strong {
        font-size: 22px;
        overflow-wrap: anywhere;
    }

    .bio-stat p {
        font-size: 12px;
        color: #6b7280;
        margin: 8px 0 0;
    }

    .bio-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
        padding: 18px 20px;
    }

    .bio-toolbar h3 {
        margin: 4px 0 0;
    }

    .bio-search {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .bio-search input {
        padding: 11px 12px;
        min-width: 235px;
        border-radius: 9px;
        border: 1px solid #d1d5db;
        background: #fff;
        color: #111827;
        font: inherit;
    }

    .bio-table-wrap {
        overflow-x: auto;
        width: 100%;
    }

    .bio-table {
        min-width: 980px;
        width: 100%;
        border-collapse: collapse;
    }

    .bio-table th {
        background: #f9fafb;
        color: #6b7280;
        text-align: left;
        font-size: 12px;
        font-weight: 700;
        padding: 13px 12px;
        white-space: nowrap;
        border-bottom: 1px solid #e5e7eb;
    }

    .bio-table td {
        font-size: 13px;
        padding: 14px 12px;
        border-bottom: 1px solid #e5e7eb;
        vertical-align: middle;
    }

    .bio-table td small {
        display: block;
        color: #6b7280;
        margin-top: 4px;
    }

    .bio-doc-link {
        font-weight: 700;
        font-family: monospace;
        text-decoration: underline;
        text-underline-offset: 3px;
        color: #111827;
    }

    .bio-doc-link:hover {
        color: #2563eb;
    }

    .bio-right {
        text-align: right !important;
        white-space: nowrap;
    }

    .bio-empty {
        text-align: center;
        color: #6b7280;
        padding: 32px !important;
    }

    .bio-footer {
        padding: 16px 20px;
    }

    @media(max-width: 800px) {
        .bio-summary {
            grid-template-columns: 1fr;
        }
    }

    @media(max-width: 520px) {
        .bio-tab,
        .bio-search,
        .bio-search input {
            width: 100%;
            box-sizing: border-box;
        }
    }
</style>

@php
    $totalDocuments = $expenses->total();

    $pageTotal = $expenses->getCollection()
        ->sum(fn ($expense) => (float) $expense->grand_total);

    $pageItems = $expenses->getCollection()
        ->sum(fn ($expense) => (int) $expense->total_items);
@endphp

<div class="bio-page">

    <div class="bio-tabs">
        <a
            class="bio-tab"
            href="{{ route('operational-masters.index') }}"
        >
            Data Tempat, Lokasi, Item DLL
        </a>

        <a
            class="bio-tab"
            href="{{ route('operational-purchases.index') }}"
        >
            Pembelian Kebutuhan Oprasional
        </a>

        <a
            class="bio-tab active"
            href="{{ route('operational-expenses.index') }}"
        >
            Biaya Oprsional
        </a>
    </div>

    @if(session('success'))
        <div
            style="padding:14px 18px;border:1px solid #86efac;
            border-radius:10px;background:#f0fdf4;color:#166534;"
        >
            {{ session('success') }}
        </div>
    @endif

    <div class="bio-summary">

        <section class="panel bio-stat">
            <small>Total Dokumen</small>
            <strong>
                {{ number_format($totalDocuments, 0, ',', '.') }}
            </strong>
            <p>Jumlah dokumen sesuai pencarian.</p>
        </section>

        <section class="panel bio-stat">
            <small>Total Item Halaman Ini</small>
            <strong>
                {{ number_format($pageItems, 0, ',', '.') }}
            </strong>
            <p>Jumlah baris item pada halaman aktif.</p>
        </section>

        <section class="panel bio-stat">
            <small>Nilai Biaya Halaman Ini</small>
            <strong>
                Rp {{ number_format($pageTotal, 2, ',', '.') }}
            </strong>
            <p>Total nominal pada halaman aktif.</p>
        </section>

    </div>

    <section class="panel" style="min-width:0;overflow:hidden;">

        <div class="bio-toolbar">

            <div>
                <p class="eyebrow">DOKUMEN BIO</p>
                <h3>Daftar Biaya Operasional</h3>
            </div>

            <a
                class="button button-primary"
                href="{{ route('operational-expenses.create') }}"
            >
                + Tambah Biaya
            </a>

        </div>

        <div class="bio-toolbar">

            <form
                class="bio-search"
                method="GET"
                action="{{ route('operational-expenses.index') }}"
            >
                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Cari nomor dokumen / tujuan..."
                >

                <button
                    type="submit"
                    class="button button-ghost"
                >
                    Cari
                </button>

                @if($search !== '')
                    <a
                        class="button button-ghost"
                        href="{{ route('operational-expenses.index') }}"
                    >
                        Reset
                    </a>
                @endif
            </form>

        </div>

        <div class="bio-table-wrap">
            <table class="bio-table">

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
                        <th class="bio-right">Grand Total Rp.</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($expenses as $expense)
                        <tr>
                            <td>
                                {{ $expenses->firstItem() + $loop->index }}
                            </td>

                            <td>
                                <a
                                    class="bio-doc-link"
                                    href="{{ route('operational-expenses.show', $expense) }}"
                                >
                                    {{ $expense->document_number }}
                                </a>
                            </td>

                            <td>
                                {{ $expense->document_date?->format('d/m/Y') ?? '-' }}
                            </td>

                            <td>
                                <strong>{{ $expense->recipient_name }}</strong>
                                <small>
                                    Dibuat oleh:
                                    {{ $expense->creator?->name ?? '-' }}
                                </small>
                            </td>

                            <td>
                                {{ $expense->need?->name ?? '-' }}
                            </td>

                            <td>
                                {{ $expense->category?->name ?? '-' }}
                            </td>

                            <td>
                                <strong>
                                    {{ $expense->location?->code ?? '-' }}
                                </strong>
                                <small>
                                    {{ $expense->location?->name ?? '' }}
                                </small>
                            </td>

                            <td>
                                {{ number_format($expense->total_items, 0, ',', '.') }}
                            </td>

                            <td class="bio-right">
                                <strong>
                                    Rp {{ number_format((float)$expense->grand_total, 2, ',', '.') }}
                                </strong>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="bio-empty">
                                @if($search !== '')
                                    Tidak ada dokumen yang sesuai pencarian.
                                @else
                                    Belum ada dokumen biaya operasional.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        <div class="bio-footer">
            {{ $expenses->links() }}
        </div>

    </section>

</div>

@endsection
