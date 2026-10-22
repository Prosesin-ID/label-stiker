
@extends('layouts.app')

@section('title', 'Detail Pembelian ' . $purchase->document_number)
@section('eyebrow', 'PEMBELIAN & BIAYA')
@section('heading', 'Detail Pembelian Operasional')

@section('content')

<style>
    .pmb-page {
        display: grid;
        gap: 18px;
        min-width: 0;
    }

    .pmb-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }

    .pmb-toolbar-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .pmb-paper {
        width: 100%;
        max-width: 900px;
        min-width: 0;
        margin: 0 auto;
        padding: 42px 46px 55px;
        box-sizing: border-box;
        background: #ffffff;
        color: #111111;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 11px;
        line-height: 1.45;
        box-shadow: 0 4px 25px rgba(0, 0, 0, .06);
    }

    .pmb-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 24px;
        margin-bottom: 52px;
    }

    .pmb-company {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .pmb-logo {
        width: 61px;
        height: 61px;
        border: 2px solid #111;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        font-weight: 900;
        flex-shrink: 0;
    }

    .pmb-company-name {
        font-size: 12px;
        line-height: 1.4;
        font-weight: 800;
        text-transform: uppercase;
    }

    .pmb-meta {
        min-width: 175px;
        text-align: right;
    }

    .pmb-meta h2 {
        margin: 0 0 13px;
        font-size: 17px;
        font-weight: 800;
        line-height: 1.25;
        text-transform: uppercase;
    }

    .pmb-meta-label {
        margin-top: 8px;
        font-weight: 800;
        font-size: 10px;
    }

    .pmb-meta-value {
        font-size: 11px;
    }

    .pmb-to {
        margin-bottom: 44px;
        text-transform: uppercase;
        min-height: 45px;
    }

    .pmb-to-title {
        display: block;
        margin-bottom: 5px;
        font-weight: 700;
    }

    .pmb-to-company {
        font-weight: 700;
        white-space: pre-line;
    }

    .pmb-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        margin-bottom: 24px;
        font-size: 10px;
    }

    .pmb-table thead th {
        background: #ffd119;
        color: #111;
        font-weight: 800;
        padding: 10px 6px;
        text-align: center;
        border-top: 1px solid #111;
        border-bottom: 1px solid #111;
    }

    .pmb-table tbody td {
        padding: 12px 6px;
        border-bottom: 1px solid #aaa;
        text-align: center;
        overflow-wrap: anywhere;
        vertical-align: top;
    }

    .pmb-table .left {
        text-align: left;
    }

    .pmb-table .right {
        text-align: right;
    }

    .pmb-table .nowrap {
        white-space: nowrap;
    }

    .pmb-item-table {
        margin-top: 38px;
    }

    .pmb-item-table tbody td {
        padding-top: 17px;
        padding-bottom: 17px;
    }

    .pmb-summary {
        display: grid;
        grid-template-columns: minmax(0, 1.3fr) minmax(0, 1fr);
        gap: 24px;
        margin-top: 36px;
        align-items: start;
    }

    .pmb-description-heading {
        background: #ffd119;
        text-align: center;
        font-weight: 800;
        padding: 9px;
        border-top: 1px solid #111;
        border-bottom: 1px solid #111;
    }

    .pmb-description-text {
        min-height: 68px;
        padding: 12px 8px;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
    }

    .pmb-summary-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 10px;
    }

    .pmb-summary-table td {
        padding: 8px 7px;
        border-bottom: 1px solid #ddd;
    }

    .pmb-summary-table .label-cell {
        background: #ffd119;
        width: 54%;
        font-weight: 800;
    }

    .pmb-summary-table .value-cell {
        text-align: right;
        font-weight: 700;
    }

    .pmb-signatures {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 24px;
        margin-top: 105px;
        text-align: center;
        page-break-inside: avoid;
        break-inside: avoid;
    }

    .pmb-signature-heading {
        font-weight: 800;
        font-size: 11px;
    }

    .pmb-signature-space {
        height: 75px;
    }

    .pmb-signature-line {
        border-top: 1px solid #111;
        margin: 0 auto 5px;
        max-width: 175px;
    }

    .pmb-signature-name {
        min-height: 18px;
        font-size: 10px;
        overflow-wrap: anywhere;
    }

    .pmb-screen-note {
        max-width: 900px;
        margin: 0 auto;
        color: #6b7280;
        font-size: 12px;
        line-height: 1.6;
    }

    @media (max-width: 650px) {
        .pmb-paper {
            padding: 24px 16px;
        }

        .pmb-header {
            gap: 12px;
        }

        .pmb-meta {
            min-width: 110px;
        }

        .pmb-meta h2 {
            font-size: 13px;
        }

        .pmb-company-name {
            font-size: 10px;
        }

        .pmb-logo {
            width: 42px;
            height: 42px;
            font-size: 13px;
        }

        .pmb-summary {
            grid-template-columns: 1fr;
        }

        .pmb-table {
            font-size: 8px;
        }

        .pmb-table thead th,
        .pmb-table tbody td {
            padding-left: 3px;
            padding-right: 3px;
        }

        .pmb-signatures {
            gap: 9px;
        }
    }

    /* CETAK A4 */

    @page {
        size: A4 portrait;
        margin: 12mm;
    }

    @media print {
        html,
        body {
            width: 100%;
            margin: 0 !important;
            padding: 0 !important;
            background: white !important;
        }

        .sidebar,
        .topbar,
        .pmb-toolbar,
        .pmb-screen-note,
        .toast-success {
            display: none !important;
        }

        .app-shell,
        .main-area,
        .page-content,
        .pmb-page {
            display: block !important;
            width: 100% !important;
            min-width: 0 !important;
            max-width: none !important;
            height: auto !important;
            min-height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: visible !important;
            background: white !important;
        }

        .pmb-paper {
            width: 100% !important;
            max-width: none !important;
            margin: 0 !important;
            padding: 0 !important;
            border: none !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            color: black !important;
            background: white !important;
        }

        .pmb-table thead th,
        .pmb-description-heading,
        .pmb-summary-table .label-cell {
            background: #ffd119 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .pmb-table thead {
            display: table-header-group;
        }

        .pmb-table tr,
        .pmb-summary,
        .pmb-header {
            break-inside: avoid;
            page-break-inside: avoid;
        }

        a {
            color: inherit !important;
            text-decoration: none !important;
        }
    }
</style>

<div class="pmb-page">

    {{-- TOMBOL DI HALAMAN, TIDAK IKUT DICETAK --}}
    <div class="pmb-toolbar">

        <a
            href="{{ route('operational-purchases.index') }}"
            class="button button-ghost"
        >
            &larr; Kembali ke Daftar Pembelian
        </a>

        <div class="pmb-toolbar-buttons">

            <button
                type="button"
                class="button button-primary"
                onclick="window.print()"
            >
                Cetak / Simpan PDF
            </button>

        </div>

    </div>

    {{-- LEMBAR DOKUMEN --}}
    <article class="pmb-paper">

        {{-- HEADER --}}
        <div class="pmb-header">

            <div class="pmb-company">

                {{-- Placeholder logo. Tidak mengarang logo asli WAF. --}}
                <div class="pmb-logo">
                    WAF
                </div>

                <div class="pmb-company-name">
                    PT.WAF<br>
                    INTERNASIONAL<br>
                    INDONESIA
                </div>

            </div>

            <div class="pmb-meta">

                <h2>
                    PEMBELIAN<br>
                    OPRASIONAL
                </h2>

                <div class="pmb-meta-label">DOC NO</div>

                <div class="pmb-meta-value">
                    {{ $purchase->document_number }}
                </div>

                <div class="pmb-meta-label">DATE</div>

                <div class="pmb-meta-value">
                    {{ $purchase->document_date?->locale('id')->translatedFormat('d F Y') }}
                </div>

            </div>

        </div>

        {{-- TO --}}
        <div class="pmb-to">

            <span class="pmb-to-title">TO.</span>

            <div class="pmb-to-company">
                {{ $purchase->recipient_name }}
            </div>

        </div>

        {{-- INFORMASI KEBUTUHAN --}}
        <table class="pmb-table">

            <thead>
                <tr>
                    <th>KEBUTUHAN</th>
                    <th>KATAGORI</th>
                    <th>LOKASI</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>
                        {{ $purchase->need?->name ?? '-' }}
                    </td>

                    <td>
                        {{ $purchase->category?->name ?? '-' }}
                    </td>

                    <td>
                        {{ $purchase->location?->code ?? '-' }}
                    </td>
                </tr>
            </tbody>

        </table>

        {{-- DETAIL BARANG --}}
        <table class="pmb-table pmb-item-table">

            <colgroup>
                <col style="width:6%">
                <col style="width:29%">
                <col style="width:10%">
                <col style="width:9%">
                <col style="width:15%">
                <col style="width:16%">
                <col style="width:15%">
            </colgroup>

            <thead>
                <tr>
                    <th>ITEM</th>
                    <th>ITEM NAME</th>
                    <th>KODE</th>
                    <th>QTY</th>
                    <th>PRICE RP.</th>
                    <th>TOTAL RP.</th>
                    <th>REMARK</th>
                </tr>
            </thead>

            <tbody>

                @forelse($purchase->items as $item)

                    @php
                        $quantity = (float) $item->quantity;

                        $quantityText = rtrim(
                            rtrim(
                                number_format($quantity, 4, '.', ''),
                                '0'
                            ),
                            '.'
                        );
                    @endphp

                    <tr>

                        <td>
                            {{ $item->line_number }}
                        </td>

                        <td class="left">
                            {{ $item->item_name }}
                        </td>

                        <td>
                            {{ $item->item_code }}
                        </td>

                        <td>
                            {{ $quantityText }}
                        </td>

                        <td class="right nowrap">
                            {{ number_format((float) $item->unit_price, 2, ',', '.') }}
                        </td>

                        <td class="right nowrap">
                            {{ number_format((float) $item->total_price, 2, ',', '.') }}
                        </td>

                        <td>
                            {{ $item->remark ?: '-' }}
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="7">
                            Tidak ada item pembelian.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

        {{-- RINGKASAN --}}
        <div class="pmb-summary">

            <div>

                <div class="pmb-description-heading">
                    DESCRIPTION
                </div>

                <div class="pmb-description-text">
                    {{ $purchase->description }}
                </div>

            </div>

            <div>

                <table class="pmb-summary-table">

                    <tbody>
                        <tr>
                            <td class="label-cell">
                                TOTAL ITEM
                            </td>

                            <td class="value-cell">
                                {{ number_format($purchase->total_items, 0, ',', '.') }}
                            </td>
                        </tr>

                        <tr>
                            <td class="label-cell">
                                TOTAL QTY
                            </td>

                            <td class="value-cell">
                                {{ rtrim(rtrim(number_format((float) $purchase->total_quantity, 4, '.', ''), '0'), '.') }}
                            </td>
                        </tr>

                        <tr>
                            <td class="label-cell">
                                GRAND TOTAL RP.
                            </td>

                            <td class="value-cell">
                                {{ number_format((float) $purchase->grand_total, 2, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>

                </table>

            </div>

        </div>

        {{-- TANDA TANGAN --}}
        <div class="pmb-signatures">

            <div>
                <div class="pmb-signature-heading">
                    APPROVED BY
                </div>

                <div class="pmb-signature-space"></div>

                <div class="pmb-signature-line"></div>

                <div class="pmb-signature-name">
                    {{ $purchase->approved_by }}
                </div>
            </div>

            <div>
                <div class="pmb-signature-heading">
                    PREPARED BY
                </div>

                <div class="pmb-signature-space"></div>

                <div class="pmb-signature-line"></div>

                <div class="pmb-signature-name">
                    {{ $purchase->prepared_by }}
                </div>
            </div>

            <div>
                <div class="pmb-signature-heading">
                    RECEIVED BY
                </div>

                <div class="pmb-signature-space"></div>

                <div class="pmb-signature-line"></div>

                <div class="pmb-signature-name">
                    {{ $purchase->received_by }}
                </div>
            </div>

        </div>

    </article>

    <p class="pmb-screen-note">
        Cetak menggunakan ukuran A4 portrait. Logo WAF saat ini
        masih berupa placeholder teks. Format final perlu
        dibandingkan dengan contoh PDF asli sebelum digunakan
        untuk dokumen resmi.
    </p>

</div>

@endsection
