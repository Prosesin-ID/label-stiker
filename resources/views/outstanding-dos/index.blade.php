
@extends('layouts.app')

@section('title', 'Outstanding DO')
@section('eyebrow', 'DELIVERY ORDER')
@section('heading', 'Outstanding Dokumen DO Asli')

@section('content')

<style>
.od-wrap {
    display: grid;
    gap: 18px;
    min-width: 0;
}
.od-panel {
    padding: 22px;
}
.od-tools {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    align-items: end;
}
.od-tools input,
.od-tools select {
    padding: 11px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    background: #fff;
    color: #111827;
    font: inherit;
}
.od-tools label {
    display: grid;
    gap: 6px;
    font-size: 12px;
    font-weight: 700;
}
.od-table-wrap {
    overflow-x: auto;
}
.od-table {
    width: 100%;
    min-width: 900px;
    border-collapse: collapse;
}
.od-table th,
.od-table td {
    padding: 12px;
    text-align: left;
    font-size: 13px;
    border-bottom: 1px solid #e5e7eb;
}
.od-table th {
    background: #f9fafb;
}
.od-tag {
    display: inline-block;
    padding: 5px 9px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    background: #e5e7eb;
}
.od-tag.out {
    background: #fef3c7;
    color: #92400e;
}
.od-tag.ok {
    background: #dcfce7;
    color: #166534;
}
.od-note {
    color: #64748b;
    font-size: 12px;
    line-height: 1.6;
}
.od-small {
    color: #6b7280;
    font-size: 11px;
}
</style>

<div class="od-wrap">

    <section class="panel od-panel">

        <h3>Monitoring Pengembalian DO Asli</h3>

        <p class="od-note">
            Status dokumen fisik dicatat terpisah
            dari status pengiriman.

            DO yang belum pernah diperiksa ditampilkan
            sebagai <strong>Belum Dicatat</strong>,
            bukan otomatis dinyatakan Outstanding.
        </p>

        <form
            class="od-tools"
            action="{{ route('outstanding-dos.index') }}"
            method="GET"
        >

            <label>
                Cari DO / Customer / PO

                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Nomor DO atau perusahaan"
                >
            </label>

            <label>
                Status

                <select name="status">

                    <option value="">Semua</option>

                    <option
                        value="UNVERIFIED"
                        @selected($status === 'UNVERIFIED')
                    >
                        Belum Dicatat
                    </option>

                    <option
                        value="OUTSTANDING"
                        @selected($status === 'OUTSTANDING')
                    >
                        Outstanding
                    </option>

                    <option
                        value="RETURNED"
                        @selected($status === 'RETURNED')
                    >
                        Sudah Kembali
                    </option>

                </select>
            </label>

            <button
                class="button button-primary"
                type="submit"
            >
                Filter
            </button>

            <a
                href="{{ route('outstanding-dos.index') }}"
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

        <div class="od-panel">

            <strong>
                Daftar Delivery Order
                ({{ $orders->total() }})
            </strong>

        </div>

        <div class="od-table-wrap">

            <table class="od-table">

                <thead>
                    <tr>
                        <th>No</th>
                        <th>DO Number</th>
                        <th>Tanggal</th>
                        <th>Customer / Tujuan</th>
                        <th>PO Customer</th>
                        <th>Pemegang Dokumen</th>
                        <th>Status DO Asli</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($orders as $order)

                        @php
                            $label = !$order->tracking_id
                                ? 'BELUM DICATAT'
                                : (
                                    (bool) $order->tracking_received
                                        ? 'SUDAH KEMBALI'
                                        : 'OUTSTANDING'
                                );

                            $badge = !$order->tracking_id
                                ? ''
                                : (
                                    (bool) $order->tracking_received
                                        ? 'ok'
                                        : 'out'
                                );
                        @endphp

                        <tr>

                            <td>
                                {{ $orders->firstItem() + $loop->index }}
                            </td>

                            <td>
                                <strong>
                                    {{ $order->number }}
                                </strong>
                            </td>

                            <td>
                                {{ $order->delivery_date?->format('d/m/Y') ?? '-' }}
                            </td>

                            <td>
                                {{ $order->to_company }}

                                <div class="od-small">
                                    Kirim ke:
                                    {{ $order->ship_to_company }}
                                </div>
                            </td>

                            <td>
                                {{ $order->purchase_order_no ?: '-' }}
                            </td>

                            <td>
                                {{ $order->tracking_holder ?: '-' }}
                            </td>

                            <td>
                                <span class="od-tag {{ $badge }}">
                                    {{ $label }}
                                </span>
                            </td>

                            <td>
                                <a
                                    href="{{ route('outstanding-dos.show', $order) }}"
                                    class="button button-ghost"
                                >
                                    Detail / Update
                                </a>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="8"
                                style="text-align:center;padding:25px"
                            >
                                Tidak ada DO yang sesuai filter.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="od-panel">
            {{ $orders->links() }}
        </div>

    </section>

</div>

@endsection
