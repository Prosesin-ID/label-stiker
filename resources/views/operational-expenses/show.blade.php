
@extends('layouts.app')

@section('title', 'Detail Biaya ' . $expense->document_number)
@section('eyebrow', 'PEMBELIAN & BIAYA')
@section('heading', 'Detail Biaya Oprsional')

@section('content')

<style>
    .bio-detail-page {
        display: grid;
        gap: 20px;
        min-width: 0;
    }

    .bio-detail-tabs {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        padding: 6px;
        background: #f3f4f6;
        border-radius: 12px;
    }

    .bio-detail-tab {
        padding: 11px 14px;
        border-radius: 9px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        color: #374151;
    }

    .bio-detail-tab.active {
        background: #fff;
        color: #111827;
        box-shadow: 0 1px 4px rgba(0,0,0,.08);
    }

    .bio-detail-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .bio-detail-toolbar-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .bio-paper {
        width: 100%;
        max-width: 900px;
        margin: 0 auto;
        padding: 42px 46px 55px;
        box-sizing: border-box;
        background: #fff;
        color: #111;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 11px;
        line-height: 1.45;
        box-shadow: 0 4px 25px rgba(0,0,0,.07);
    }

    .bio-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 24px;
        margin-bottom: 48px;
    }

    .bio-company {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .bio-company-logo {
        width: 62px;
        height: 62px;
        border: 2px solid #111;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 19px;
        font-weight: 900;
    }

    .bio-company-name {
        font-size: 12px;
        font-weight: 800;
        line-height: 1.4;
    }

    .bio-document-meta {
        text-align: right;
        min-width: 155px;
    }

    .bio-document-meta h2 {
        margin: 0 0 15px;
        font-size: 18px;
        font-weight: 800;
        line-height: 1.3;
    }

    .bio-meta-label {
        display: block;
        margin-top: 8px;
        font-size: 10px;
        font-weight: 800;
    }

    .bio-meta-value {
        display: block;
        font-size: 11px;
    }

    .bio-recipient {
        margin-bottom: 40px;
        text-transform: uppercase;
    }

    .bio-recipient-title {
        display: block;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .bio-recipient-name {
        display: block;
        font-weight: 700;
        white-space: pre-line;
    }

    .bio-document-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        font-size: 10px;
        margin-bottom: 25px;
    }

    .bio-document-table thead th {
        background: #ffd119;
        color: #111;
        text-align: center;
        font-weight: 800;
        padding: 10px 7px;
        border-top: 1px solid #111;
        border-bottom: 1px solid #111;
    }

    .bio-document-table tbody td {
        padding: 12px 7px;
        border-bottom: 1px solid #aaa;
        text-align: center;
        vertical-align: top;
        overflow-wrap: anywhere;
    }

    .bio-document-table .align-left {
        text-align: left;
    }

    .bio-document-table .align-right {
        text-align: right;
    }

    .bio-detail-items {
        margin-top: 36px;
    }

    .bio-detail-items tbody td {
        padding-top: 16px;
        padding-bottom: 16px;
    }

    .bio-summary-layout {
        display: grid;
        grid-template-columns: minmax(0,1.3fr) minmax(0,1fr);
        gap: 24px;
        align-items: start;
        margin-top: 36px;
    }

    .bio-description-heading {
        background: #ffd119;
        border-top: 1px solid #111;
        border-bottom: 1px solid #111;
        padding: 10px;
        text-align: center;
        font-weight: 800;
    }

    .bio-description-content {
        min-height: 75px;
        padding: 12px 8px;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
    }

    .bio-summary-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 10px;
    }

    .bio-summary-table td {
        border-bottom: 1px solid #ddd;
        padding: 9px 7px;
    }

    .bio-summary-table .bio-summary-label {
        background: #ffd119;
        font-weight: 800;
        width: 52%;
    }

    .bio-summary-table .bio-summary-value {
        text-align: right;
        font-weight: 700;
    }

    .bio-signatures {
        display: grid;
        grid-template-columns: repeat(3, minmax(0,1fr));
        gap: 24px;
        margin-top: 105px;
        text-align: center;
        break-inside: avoid;
        page-break-inside: avoid;
    }

    .bio-signature-heading {
        font-weight: 800;
        font-size: 11px;
    }

    .bio-signature-space {
        height: 80px;
    }

    .bio-signature-line {
        border-top: 1px solid #111;
        margin: 0 auto 6px;
        max-width: 170px;
    }

    .bio-signature-name {
        min-height: 18px;
        font-size: 10px;
        overflow-wrap: anywhere;
    }

    .bio-detail-note {
        margin: 0 auto;
        max-width: 900px;
        font-size: 12px;
        color: #6b7280;
        line-height: 1.6;
    }

    @media (max-width: 650px) {
        .bio-paper {
            padding: 24px 16px;
        }

        .bio-header {
            gap: 12px;
        }

        .bio-company-logo {
            width: 42px;
            height: 42px;
            font-size: 13px;
        }

        .bio-company-name {
            font-size: 10px;
        }

        .bio-document-meta {
            min-width: 105px;
        }

        .bio-document-meta h2 {
            font-size: 14px;
        }

        .bio-summary-layout {
            grid-template-columns: 1fr;
        }

        .bio-document-table {
            font-size: 9px;
        }

        .bio-document-table th,
        .bio-document-table td {
            padding-left: 3px !important;
            padding-right: 3px !important;
        }

        .bio-signatures {
            gap: 10px;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CETAK A4
    |--------------------------------------------------------------------------
    */

    @page {
        size: A4 portrait;
        margin: 12mm;
    }

    @media print {
        html,
        body {
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
        }

        /*
         | Sembunyikan seluruh elemen halaman selain dokumen BIO.
         | Dokumen dicetak tanpa sidebar atau navigasi aplikasi.
         */

        body * {
            visibility: hidden !important;
        }

        #bioPrintableDocument,
        #bioPrintableDocument * {
            visibility: visible !important;
        }

        #bioPrintableDocument {
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            max-width: none !important;
            margin: 0 !important;
            padding: 0 !important;
            border: 0 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            background: #fff !important;
        }

        .bio-document-table thead th,
        .bio-description-heading,
        .bio-summary-label {
            background: #ffd119 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .bio-document-table thead {
            display: table-header-group;
        }

        .bio-document-table tr,
        .bio-header,
        .bio-summary-layout,
        .bio-signatures {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .bio-paper {
            font-size: 11px;
        }
    }
</style>

<div class="bio-detail-page">

    {{-- NAVIGASI MODUL --}}
    <nav class="bio-detail-tabs">

        <a
            class="bio-detail-tab"
            href="{{ route('operational-masters.index') }}"
        >
            Data Tempat, Lokasi, Item DLL
        </a>

        <a
            class="bio-detail-tab"
            href="{{ route('operational-purchases.index') }}"
        >
            Pembelian Kebutuhan Oprasional
        </a>

        <a
            class="bio-detail-tab active"
            href="{{ route('operational-expenses.index') }}"
        >
            Biaya Oprsional
        </a>

    </nav>

    {{-- TOMBOL AKSI --}}
    <div class="bio-detail-toolbar">

        <a
            class="button button-ghost"
            href="{{ route('operational-expenses.index') }}"
        >
            &larr; Kembali ke Daftar Biaya
        </a>

        <div class="bio-detail-toolbar-actions">

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
    <article
        id="bioPrintableDocument"
        class="bio-paper"
    >

        {{-- HEADER --}}
        <div class="bio-header">

            <div class="bio-company">

                {{-- Logo sementara, bukan logo asli PT WAF --}}
                <div class="bio-company-logo">
                    WAF
                </div>

                <div class="bio-company-name">
                    PT.WAF<br>
                    INTERNASIONAL<br>
                    INDONESIA
                </div>

            </div>

            <div class="bio-document-meta">

                <h2>
                    BIAYA<br>
                    OPRASIONAL
                </h2>

                <span class="bio-meta-label">
                    DOC NO
                </span>

                <span class="bio-meta-value">
                    {{ $expense->document_number }}
                </span>

                <span class="bio-meta-label">
                    DATE
                </span>

                <span class="bio-meta-value">
                    {{ $expense->document_date?->locale('id')->translatedFormat('d F Y') }}
                </span>

            </div>

        </div>

        {{-- TO --}}
        <div class="bio-recipient">

            <span class="bio-recipient-title">
                TO.
            </span>

            <span class="bio-recipient-name">
                {{ $expense->recipient_name }}
            </span>

        </div>

        {{-- KEBUTUHAN / KATAGORI / LOKASI --}}
        <table class="bio-document-table">

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
                        {{ $expense->need?->name ?? '-' }}
                    </td>

                    <td>
                        {{ $expense->category?->name ?? '-' }}
                    </td>

                    <td>
                        {{ $expense->location?->code ?? '-' }}
                    </td>
                </tr>
            </tbody>

        </table>

        {{-- TABEL DETAIL BIAYA --}}
        <table class="bio-document-table bio-detail-items">

            <colgroup>
                <col style="width:8%">
                <col style="width:37%">
                <col style="width:18%">
                <col style="width:19%">
                <col style="width:18%">
            </colgroup>

            <thead>
                <tr>
                    <th>ITEM</th>
                    <th>ITEM NAME</th>
                    <th>BIAYA RP.</th>
                    <th>TOTAL RP.</th>
                    <th>REMARK</th>
                </tr>
            </thead>

            <tbody>

                @forelse($expense->items as $item)

                    <tr>

                        <td>
                            {{ $item->line_number }}
                        </td>

                        <td class="align-left">
                            {{ $item->item_name }}
                        </td>

                        <td class="align-right">
                            {{ number_format(
                                (float) $item->expense_amount,
                                2,
                                ',',
                                '.'
                            ) }}
                        </td>

                        <td class="align-right">
                            {{ number_format(
                                (float) $item->total_amount,
                                2,
                                ',',
                                '.'
                            ) }}
                        </td>

                        <td>
                            {{ $item->remark ?: '-' }}
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="5">
                            Tidak ada rincian biaya.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

        {{-- DESCRIPTION DAN TOTAL --}}
        <div class="bio-summary-layout">

            <div>

                <div class="bio-description-heading">
                    DESCRIPTION
                </div>

                <div class="bio-description-content">{{ $expense->description ?? '' }}</div>

            </div>

            <div>

                <table class="bio-summary-table">

                    <tbody>

                        <tr>
                            <td class="bio-summary-label">
                                TOTAL ITEM
                            </td>

                            <td class="bio-summary-value">
                                {{ number_format(
                                    $expense->total_items,
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </td>
                        </tr>

                        <tr>
                            <td class="bio-summary-label">
                                GRAND TOTAL RP.
                            </td>

                            <td class="bio-summary-value">
                                {{ number_format(
                                    (float) $expense->grand_total,
                                    2,
                                    ',',
                                    '.'
                                ) }}
                            </td>
                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

        {{-- TANDA TANGAN --}}
        <div class="bio-signatures">

            <div>

                <div class="bio-signature-heading">
                    APPROVED BY
                </div>

                <div class="bio-signature-space"></div>
                <div class="bio-signature-line"></div>

                <div class="bio-signature-name">
                    {{ $expense->approved_by ?: '' }}
                </div>

            </div>

            <div>

                <div class="bio-signature-heading">
                    PREPARED BY
                </div>

                <div class="bio-signature-space"></div>
                <div class="bio-signature-line"></div>

                <div class="bio-signature-name">
                    {{ $expense->prepared_by ?: '' }}
                </div>

            </div>

            <div>

                <div class="bio-signature-heading">
                    RECEIVED BY
                </div>

                <div class="bio-signature-space"></div>
                <div class="bio-signature-line"></div>

                <div class="bio-signature-name">
                    {{ $expense->received_by ?: '' }}
                </div>

            </div>

        </div>

    </article>

    <p class="bio-detail-note">
        Dokumen menggunakan layout A4 portrait dengan tabel berwarna
        kuning sesuai struktur contoh PT WAF. Logo masih placeholder.
        Tombol cetak menggunakan fitur cetak browser sehingga dapat
        disimpan sebagai PDF.
    </p>

</div>

@endsection
