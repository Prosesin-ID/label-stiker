@extends('layouts.app')

@section('title', 'Bulk Print')
@section('eyebrow', 'PRINT QUEUE')
@section('heading', 'Bulk print label')

@section('content')

    {{-- =========================================================
        HERO
    ========================================================== --}}
    <section class="hero-row bulk-hero">
        <div>
            <h2>
                Cetak banyak, sekali jalan.
            </h2>

            <p>
                Pilih label dan tentukan jumlah copy.
                Setiap copy menjadi satu halaman 100 × 100 mm.
            </p>
        </div>

        <span class="printer-chip">
            Zebra GC420t · 203 dpi
        </span>

        <a
            href="{{ route('labels.create') }}"
            class="button button-primary"
        >
            + Buat label baru
        </a>
    </section>


    {{-- =========================================================
        SUCCESS MESSAGE
    ========================================================== --}}
    @if(session('success'))
        <div
            class="error-box"
            style="
                border-color: #b7dfc8;
                background: #f2fbf5;
                color: #176c3a;
            "
        >
            {{ session('success') }}
        </div>
    @endif


    {{-- =========================================================
        ERROR MESSAGE
    ========================================================== --}}
    @if($errors->any())
        <div class="error-box bulk-error">
            <strong>
                Bulk print belum dapat diproses:
            </strong>

            <ul>
                @foreach($errors->all() as $error)
                    <li>
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif


    {{-- =========================================================
        BULK PRINT PANEL
    ========================================================== --}}
    <section
        class="panel bulk-panel"
        data-bulk-print
    >

        {{-- =====================================================
            HEADER / FILTER
        ====================================================== --}}
        <div class="panel-heading bulk-toolbar">
            <div>
                <p class="eyebrow">
                    RIWAYAT LABEL
                </p>

                <h3>
                    {{ $labels->total() }} label tersedia
                </h3>
            </div>


            <form
                method="GET"
                action="{{ route('labels.index') }}"
                class="bulk-filters"
            >
                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Cari PO, surat jalan, part, atau alamat..."
                >

                <select name="status">
                    <option value="">
                        Semua status
                    </option>

                    <option
                        value="ready"
                        @selected($status === 'ready')
                    >
                        Siap cetak
                    </option>

                    <option
                        value="printed"
                        @selected($status === 'printed')
                    >
                        Pernah dicetak
                    </option>
                </select>

                <button
                    type="submit"
                    class="button button-ghost"
                >
                    Cari
                </button>
            </form>
        </div>


        {{-- =====================================================
            BULK PRINT FORM
        ====================================================== --}}
        <form
            method="POST"
            action="{{ route('labels.bulk-pdf') }}"
            id="bulkPrintForm"
        >
            @csrf


            {{-- =================================================
                TABLE
            ================================================== --}}
            <div class="table-wrap">
                <table class="bulk-table">

                    <thead>
                        <tr>

                            {{-- CHECKBOX --}}
                            <th class="check-cell">
                                <input
                                    type="checkbox"
                                    id="selectAllLabels"
                                    aria-label="Pilih semua label di halaman ini"
                                >
                            </th>


                            {{-- PART --}}
                            <th>
                                Part
                            </th>


                            {{-- NOMOR PO --}}
                            <th>
                                Nomor PO
                            </th>


                            {{-- SURAT JALAN --}}
                            <th>
                                Surat Jalan
                            </th>


                            {{-- CUSTOMER PART --}}
                            <th>
                                Customer Part
                            </th>


                            {{-- QTY --}}
                            <th>
                                Qty Produk
                            </th>


                            {{-- STOCK --}}
                            <th>
                                Stock Inv.
                            </th>


                            {{-- CREATED --}}
                            <th>
                                Dibuat
                            </th>


                            {{-- STATUS --}}
                            <th>
                                Status
                            </th>


                            {{-- COPY --}}
                            <th>
                                Copy Label
                            </th>


                            {{-- AKSI --}}
                            <th>
                                Aksi
                            </th>

                        </tr>
                    </thead>


                    <tbody>

                        @forelse($labels as $label)

                            <tr>

                                {{-- =========================================
                                    CHECKBOX
                                ========================================== --}}
                                <td class="check-cell">
                                    <input
                                        class="label-checkbox"
                                        type="checkbox"
                                        name="label_ids[]"
                                        value="{{ $label->id }}"
                                        id="label-{{ $label->id }}"
                                    >
                                </td>


                                {{-- =========================================
                                    PART
                                ========================================== --}}
                                <td>
                                    <label for="label-{{ $label->id }}">
                                        <strong>
                                            {{ $label->product_snapshot['name'] ?? '-' }}
                                        </strong>

                                        <small>
                                            {{ $label->product_snapshot['sku'] ?? '-' }}
                                        </small>
                                    </label>
                                </td>


                                {{-- =========================================
                                    NOMOR PO
                                ========================================== --}}
                                <td class="mono">
                                    {{ $label->purchase_order_no ?: '-' }}
                                </td>


                                {{-- =========================================
                                    SURAT JALAN
                                ========================================== --}}
                                <td class="mono">
                                    {{ $label->delivery_note_no ?: '-' }}
                                </td>


                                {{-- =========================================
                                    CUSTOMER PART
                                ========================================== --}}
                                <td class="mono">
                                    {{ $label->customer_part_no ?: '-' }}
                                </td>


                                {{-- =========================================
                                    QTY PRODUK
                                ========================================== --}}
                                <td>
                                    <strong>
                                        {{
                                            rtrim(
                                                rtrim(
                                                    number_format(
                                                        (float) $label->quantity,
                                                        4,
                                                        ',',
                                                        '.'
                                                    ),
                                                    '0'
                                                ),
                                                ','
                                            )
                                        }}
                                    </strong>

                                    <div>
                                        {{ $label->uom }}
                                    </div>
                                </td>


                                {{-- =========================================
                                    STOCK INVENTORY
                                ========================================== --}}
                                <td>
                                    <strong>
                                        {{
                                            rtrim(
                                                rtrim(
                                                    number_format(
                                                        (float) $label->inventory_stock,
                                                        4,
                                                        ',',
                                                        '.'
                                                    ),
                                                    '0'
                                                ),
                                                ','
                                            )
                                        }}
                                    </strong>

                                    <div>
                                        {{ $label->uom }}
                                    </div>
                                </td>


                                {{-- =========================================
                                    CREATED AT
                                ========================================== --}}
                                <td>
                                    {{
                                        $label
                                            ->created_at
                                            ->translatedFormat('d M Y, H:i')
                                    }}
                                </td>


                                {{-- =========================================
                                    STATUS
                                ========================================== --}}
                                <td>
                                    <span
                                        class="status {{ $label->printed_at ? 'done' : 'draft' }}"
                                    >
                                        {{
                                            $label->printed_at
                                                ? 'DICETAK'
                                                : 'SIAP CETAK'
                                        }}
                                    </span>
                                </td>


                                {{-- =========================================
                                    COPY LABEL
                                ========================================== --}}
                                <td>
                                    <div class="copy-control">

                                        <button
                                            type="button"
                                            data-copy-minus
                                            aria-label="Kurangi copy"
                                        >
                                            −
                                        </button>


                                        <input
                                            class="copy-input"
                                            type="number"
                                            name="copies[{{ $label->id }}]"
                                            value="1"
                                            min="1"
                                            max="50"
                                            disabled
                                        >


                                        <button
                                            type="button"
                                            data-copy-plus
                                            aria-label="Tambah copy"
                                        >
                                            +
                                        </button>

                                    </div>
                                </td>


                                {{-- =========================================
                                    AKSI
                                ========================================== --}}
                                <td>
                                    <div
                                        class="row-actions"
                                        style="
                                            display: flex;
                                            align-items: center;
                                            gap: 8px;
                                            white-space: nowrap;
                                        "
                                    >

                                        {{-- PREVIEW --}}
                                        <a
                                            href="{{ route('labels.show', $label) }}"
                                            class="action-button"
                                            title="Lihat label"
                                            aria-label="Lihat label"
                                        >
                                            →
                                        </a>


                                        {{-- HAPUS --}}
                                        <button
                                            type="submit"
                                            form="delete-label-{{ $label->id }}"
                                            class="action-button danger"
                                            title="Hapus label"
                                            aria-label="Hapus label"
                                            style="
                                                color: #c0392b;
                                                cursor: pointer;
                                            "
                                            onclick="
                                                return confirm(
                                                    'Yakin ingin menghapus label ini? Data label yang sudah dihapus tidak dapat dikembalikan.'
                                                );
                                            "
                                        >
                                            ×
                                        </button>

                                    </div>
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td
                                    colspan="11"
                                    class="empty-state"
                                >
                                    Label tidak ditemukan.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>
            </div>


            {{-- =================================================
                BULK ACTION
            ================================================== --}}
            <div class="bulk-actionbar">

                <div>
                    <strong id="selectedLabelCount">
                        0 label dipilih
                    </strong>

                    <small id="selectedPageCount">
                        0 halaman PDF
                    </small>
                </div>


                <span class="bulk-note">
                    Maks. 50 copy/label · 300 halaman/file
                </span>


                <button
                    type="submit"
                    class="button button-primary"
                    id="bulkPrintButton"
                    disabled
                >
                    ▦ Lihat bulk PDF
                </button>

            </div>

        </form>

    </section>


    {{-- =========================================================
        DELETE FORMS
        Diletakkan di luar bulkPrintForm supaya tidak nested form.
    ========================================================== --}}
    @foreach($labels as $label)

        <form
            id="delete-label-{{ $label->id }}"
            method="POST"
            action="{{ route('labels.destroy', $label) }}"
            style="display: none;"
        >
            @csrf
            @method('DELETE')
        </form>

    @endforeach


    {{-- =========================================================
        PAGINATION
    ========================================================== --}}
    <div class="pagination bulk-pagination">
        {{ $labels->links() }}
    </div>

@endsection