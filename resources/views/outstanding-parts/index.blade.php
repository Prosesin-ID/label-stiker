
@extends('layouts.app')

@section('title', 'Outstanding Parts')
@section('eyebrow', 'STOCK NORMAL & CONSIGNMENT')
@section('heading', 'Outstanding Parts')

@section('content')

<style>
.op-wrap {
    display: grid;
    gap: 18px;
    min-width: 0;
}
.op-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 14px;
}
.op-panel {
    padding: 20px;
}
.op-filter {
    display: flex;
    flex-wrap: wrap;
    align-items: end;
    gap: 10px;
}
.op-filter label {
    display: grid;
    gap: 5px;
    font-size: 12px;
    font-weight: 600;
}
.op-filter input,
.op-filter select {
    padding: 10px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font: inherit;
    background: #fff;
    color: #111827;
}
.op-table-wrap {
    overflow-x: auto;
}
.op-table {
    width: 100%;
    min-width: 1090px;
    border-collapse: collapse;
}
.op-table th,
.op-table td {
    padding: 12px;
    text-align: left;
    font-size: 12px;
    border-bottom: 1px solid #e5e7eb;
}
.op-table th {
    background: #f9fafb;
    font-weight: 700;
}
.op-tag {
    display: inline-block;
    background: #fef3c7;
    color: #92400e;
    padding: 5px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 700;
}
.op-tag.partial {
    background: #dbeafe;
    color: #1d4ed8;
}
.op-tag.complete {
    background: #dcfce7;
    color: #166534;
}
.op-muted {
    font-size: 12px;
    color: #6b7280;
    line-height: 1.6;
}
</style>

@php
    $fmtQty = function ($value) {
        return rtrim(
            rtrim(
                number_format((float) $value, 4, ',', '.'),
                '0'
            ),
            ','
        );
    };
@endphp

<div class="op-wrap">

    <div class="op-head">

        <div>
            <h3>Monitoring Permintaan Parts</h3>

            <p class="op-muted">
                Normal & Consignment.
                Outstanding dihitung dari selisih
                jumlah permintaan dan barang tersuplai.
            </p>
        </div>

        <a
            href="{{ route('outstanding-parts.create') }}"
            class="button button-primary"
        >
            + Tambah Permintaan
        </a>

    </div>

    @if(session('success'))
        <div style="padding:12px;border:1px solid #86efac;background:#f0fdf4;border-radius:9px">
            {{ session('success') }}
        </div>
    @endif

    <section class="panel op-panel">

        <form
            method="GET"
            action="{{ route('outstanding-parts.index') }}"
            class="op-filter"
        >

            <label>
                Cari Customer / Part / Referensi

                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Nama, kode part, PO"
                >
            </label>

            <label>
                Jenis Supply

                <select name="supply_type">
                    <option value="">Semua</option>

                    <option
                        value="NORMAL"
                        @selected($supplyType === 'NORMAL')
                    >
                        Normal
                    </option>

                    <option
                        value="CONSIGNMENT"
                        @selected($supplyType === 'CONSIGNMENT')
                    >
                        Consignment
                    </option>
                </select>
            </label>

            <label>
                Status

                <select name="status">
                    <option value="">Semua</option>

                    <option
                        value="PENDING"
                        @selected($status === 'PENDING')
                    >
                        Pending
                    </option>

                    <option
                        value="PARTIAL"
                        @selected($status === 'PARTIAL')
                    >
                        Partial
                    </option>

                    <option
                        value="COMPLETED"
                        @selected($status === 'COMPLETED')
                    >
                        Completed
                    </option>
                </select>
            </label>

            <button
                type="submit"
                class="button button-primary"
            >
                Filter
            </button>

            <a
                href="{{ route('outstanding-parts.index') }}"
                class="button button-ghost"
            >
                Reset
            </a>

        </form>

    </section>

    <section
        class="panel"
        style="min-width:0;overflow:hidden"
    >

        <div class="op-panel">
            <strong>
                Daftar Permintaan ({{ $parts->total() }})
            </strong>
        </div>

        <div class="op-table-wrap">

            <table class="op-table">

                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Customer</th>
                        <th>Supply</th>
                        <th>Part Number</th>
                        <th>Nama Part</th>
                        <th>Requested</th>
                        <th>Supplied</th>
                        <th>Outstanding</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($parts as $part)

                        @php
                            $statusCode = $part->fulfillment_status;
                        @endphp

                        <tr>

                            <td>
                                {{ $parts->firstItem() + $loop->index }}
                            </td>

                            <td>
                                {{ $part->request_date?->format('d/m/Y') }}
                            </td>

                            <td>
                                <strong>{{ $part->customer_name }}</strong>

                                <div class="op-muted">
                                    {{ $part->customer_reference ?: '-' }}
                                </div>
                            </td>

                            <td>
                                {{ $part->supply_type }}
                            </td>

                            <td>
                                {{ $part->part_number }}
                            </td>

                            <td>
                                {{ $part->part_name }}
                            </td>

                            <td>
                                {{ $fmtQty($part->requested_quantity) }}
                            </td>

                            <td>
                                {{ $fmtQty($part->supplied_quantity) }}
                            </td>

                            <td>
                                <strong>
                                    {{ $fmtQty($part->outstanding_quantity) }}
                                </strong>
                            </td>

                            <td>

                                <span class="op-tag {{
                                    $statusCode === 'PARTIAL'
                                        ? 'partial'
                                        : (
                                            $statusCode === 'COMPLETED'
                                                ? 'complete'
                                                : ''
                                        )
                                }}">
                                    {{ $statusCode }}
                                </span>

                            </td>

                            <td>
                                <a
                                    href="{{ route('outstanding-parts.show', $part) }}"
                                    class="button button-ghost"
                                >
                                    Detail / Supply
                                </a>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="11"
                                style="text-align:center;padding:30px"
                            >
                                Belum ada data sesuai filter.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="op-panel">
            {{ $parts->links() }}
        </div>

    </section>

    <div class="op-muted">
        Supply dicatat melalui halaman detail beserta
        nomor referensi pengiriman. Fitur ini tidak
        mengubah stok secara otomatis.
    </div>

</div>

@endsection
