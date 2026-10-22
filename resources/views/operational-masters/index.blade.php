
@extends('layouts.app')

@section('title', 'Pembelian & Biaya')
@section('eyebrow', 'OPERASIONAL')
@section('heading', 'Pembelian & Biaya')

@section('content')

<style>
    .operational-tabs {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 24px;
        padding: 6px;
        background: #f3f4f6;
        border-radius: 12px;
    }

    .operational-tab {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 16px;
        border-radius: 9px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        color: #374151;
        background: transparent;
    }

    .operational-tab.active {
        background: #fff;
        color: #111827;
        box-shadow: 0 1px 4px rgba(0, 0, 0, .08);
    }

    .operational-master-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
        align-items: start;
    }

    .operational-master-card {
        min-width: 0;
        overflow: hidden;
    }

    .operational-master-body {
        padding: 20px;
    }

    .operational-master-form {
        display: grid;
        gap: 12px;
        margin-bottom: 22px;
    }

    .operational-master-field {
        display: flex;
        flex-direction: column;
        gap: 7px;
        min-width: 0;
    }

    .operational-master-field label {
        font-weight: 600;
        font-size: 13px;
    }

    .operational-master-field input,
    .operational-master-field select {
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

    .operational-master-form .button {
        justify-self: start;
    }

    .operational-master-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .operational-master-record {
        padding: 14px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #fff;
    }

    .operational-master-record form {
        display: grid;
        gap: 10px;
    }

    .operational-master-record-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .operational-master-record-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .operational-master-check {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
    }

    .operational-master-empty {
        padding: 16px;
        border: 1px dashed #d1d5db;
        border-radius: 10px;
        color: #6b7280;
        font-size: 13px;
    }

    .operational-master-section-title {
        margin: 0 0 14px;
        font-size: 14px;
        font-weight: 700;
    }

    @media (max-width: 900px) {
        .operational-master-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 500px) {
        .operational-master-record-grid {
            grid-template-columns: 1fr;
        }

        .operational-tab {
            width: 100%;
        }
    }
</style>

{{-- NAVIGASI PEMBELIAN & BIAYA --}}
<div class="operational-tabs">

    <a
        href="{{ route('operational-masters.index') }}"
        class="operational-tab active"
    >
        Data Tempat, Lokasi, Item DLL
    </a>

    <a
        href="{{ route('operational-purchases.index') }}"
        class="operational-tab"
    >
        Pembelian Kebutuhan Oprasional
    </a>

    {{-- TAB BIAYA OPERASIONAL SUDAH AKTIF --}}
    <a
        href="{{ route('operational-expenses.index') }}"
        class="operational-tab"
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

<div class="operational-master-grid">

    {{-- MASTER KEBUTUHAN --}}
    <section class="panel operational-master-card">

        <div class="panel-heading">
            <div>
                <p class="eyebrow">MASTER KEBUTUHAN</p>
                <h3>Kebutuhan Operasional</h3>
            </div>

            <span class="status done">
                {{ $needs->count() }} data
            </span>
        </div>

        <div class="operational-master-body">

            {{-- TAMBAH KEBUTUHAN --}}
            <form
                method="POST"
                action="{{ route('operational-masters.needs.store') }}"
                class="operational-master-form"
            >
                @csrf

                <div class="operational-master-field">
                    <label for="need_name">
                        Nama kebutuhan *
                    </label>

                    <input
                        id="need_name"
                        type="text"
                        name="name"
                        maxlength="150"
                        placeholder="Contoh: PACKAGING"
                        value="{{ old('name') }}"
                        required
                    >
                </div>

                <button
                    type="submit"
                    class="button button-primary"
                >
                    + Tambah Kebutuhan
                </button>
            </form>

            <h4 class="operational-master-section-title">
                Daftar Kebutuhan
            </h4>

            <div class="operational-master-list">

                @forelse($needs as $need)

                    <div class="operational-master-record">

                        <form
                            method="POST"
                            action="{{ route('operational-masters.needs.update', $need) }}"
                        >
                            @csrf
                            @method('PUT')

                            <div class="operational-master-field">
                                <label for="need_{{ $need->id }}">
                                    Nama kebutuhan
                                </label>

                                <input
                                    id="need_{{ $need->id }}"
                                    type="text"
                                    name="name"
                                    value="{{ $need->name }}"
                                    maxlength="150"
                                    required
                                >
                            </div>

                            <div class="operational-master-record-actions">

                                <label class="operational-master-check">
                                    <input
                                        type="checkbox"
                                        name="is_active"
                                        value="1"
                                        @checked($need->is_active)
                                    >
                                    Aktif
                                </label>

                                <button
                                    type="submit"
                                    class="button button-ghost"
                                >
                                    Simpan Perubahan
                                </button>

                            </div>
                        </form>

                    </div>

                @empty
                    <div class="operational-master-empty">
                        Belum ada data kebutuhan operasional.
                    </div>
                @endforelse

            </div>
        </div>
    </section>

    {{-- MASTER KATEGORI --}}
    <section class="panel operational-master-card">

        <div class="panel-heading">
            <div>
                <p class="eyebrow">MASTER KATEGORI</p>
                <h3>Kategori Operasional</h3>
            </div>

            <span class="status done">
                {{ $categories->count() }} data
            </span>
        </div>

        <div class="operational-master-body">

            {{-- TAMBAH KATEGORI --}}
            <form
                method="POST"
                action="{{ route('operational-masters.categories.store') }}"
                class="operational-master-form"
            >
                @csrf

                <div class="operational-master-field">
                    <label for="category_name">
                        Nama kategori *
                    </label>

                    <input
                        id="category_name"
                        type="text"
                        name="name"
                        maxlength="150"
                        placeholder="Contoh: CONSUMABLE"
                        required
                    >
                </div>

                <button
                    type="submit"
                    class="button button-primary"
                >
                    + Tambah Kategori
                </button>
            </form>

            <h4 class="operational-master-section-title">
                Daftar Kategori
            </h4>

            <div class="operational-master-list">

                @forelse($categories as $category)

                    <div class="operational-master-record">

                        <form
                            method="POST"
                            action="{{ route('operational-masters.categories.update', $category) }}"
                        >
                            @csrf
                            @method('PUT')

                            <div class="operational-master-field">
                                <label for="category_{{ $category->id }}">
                                    Nama kategori
                                </label>

                                <input
                                    id="category_{{ $category->id }}"
                                    type="text"
                                    name="name"
                                    value="{{ $category->name }}"
                                    maxlength="150"
                                    required
                                >
                            </div>

                            <div class="operational-master-record-actions">

                                <label class="operational-master-check">
                                    <input
                                        type="checkbox"
                                        name="is_active"
                                        value="1"
                                        @checked($category->is_active)
                                    >
                                    Aktif
                                </label>

                                <button
                                    type="submit"
                                    class="button button-ghost"
                                >
                                    Simpan Perubahan
                                </button>

                            </div>
                        </form>

                    </div>

                @empty
                    <div class="operational-master-empty">
                        Belum ada data kategori operasional.
                    </div>
                @endforelse

            </div>
        </div>
    </section>

    {{-- MASTER LOKASI --}}
    <section class="panel operational-master-card">

        <div class="panel-heading">
            <div>
                <p class="eyebrow">MASTER LOKASI</p>
                <h3>Lokasi Operasional</h3>
            </div>

            <span class="status done">
                {{ $locations->count() }} data
            </span>
        </div>

        <div class="operational-master-body">

            {{-- TAMBAH LOKASI --}}
            <form
                method="POST"
                action="{{ route('operational-masters.locations.store') }}"
                class="operational-master-form"
            >
                @csrf

                <div class="operational-master-record-grid">

                    <div class="operational-master-field">
                        <label for="location_code">
                            Kode lokasi *
                        </label>

                        <input
                            id="location_code"
                            type="text"
                            name="code"
                            maxlength="30"
                            placeholder="MGK"
                            required
                        >
                    </div>

                    <div class="operational-master-field">
                        <label for="location_name">
                            Nama lokasi *
                        </label>

                        <input
                            id="location_name"
                            type="text"
                            name="name"
                            maxlength="150"
                            placeholder="MGK JAKARTA"
                            required
                        >
                    </div>

                </div>

                <div class="operational-master-field">
                    <label for="location_address">
                        Alamat (opsional)
                    </label>

                    <input
                        id="location_address"
                        type="text"
                        name="address"
                        maxlength="500"
                        placeholder="Alamat lokasi"
                    >
                </div>

                <button
                    type="submit"
                    class="button button-primary"
                >
                    + Tambah Lokasi
                </button>
            </form>

            <h4 class="operational-master-section-title">
                Daftar Lokasi
            </h4>

            <div class="operational-master-list">

                @forelse($locations as $location)

                    <div class="operational-master-record">

                        <form
                            method="POST"
                            action="{{ route('operational-masters.locations.update', $location) }}"
                        >
                            @csrf
                            @method('PUT')

                            <div class="operational-master-record-grid">

                                <div class="operational-master-field">
                                    <label for="location_code_{{ $location->id }}">
                                        Kode
                                    </label>

                                    <input
                                        id="location_code_{{ $location->id }}"
                                        type="text"
                                        name="code"
                                        value="{{ $location->code }}"
                                        maxlength="30"
                                        required
                                    >
                                </div>

                                <div class="operational-master-field">
                                    <label for="location_name_{{ $location->id }}">
                                        Nama lokasi
                                    </label>

                                    <input
                                        id="location_name_{{ $location->id }}"
                                        type="text"
                                        name="name"
                                        value="{{ $location->name }}"
                                        maxlength="150"
                                        required
                                    >
                                </div>

                            </div>

                            <div class="operational-master-field">
                                <label for="location_address_{{ $location->id }}">
                                    Alamat
                                </label>

                                <input
                                    id="location_address_{{ $location->id }}"
                                    type="text"
                                    name="address"
                                    value="{{ $location->address }}"
                                    maxlength="500"
                                >
                            </div>

                            <div class="operational-master-record-actions">

                                <label class="operational-master-check">
                                    <input
                                        type="checkbox"
                                        name="is_active"
                                        value="1"
                                        @checked($location->is_active)
                                    >
                                    Aktif
                                </label>

                                <button
                                    type="submit"
                                    class="button button-ghost"
                                >
                                    Simpan Perubahan
                                </button>

                            </div>
                        </form>

                    </div>

                @empty
                    <div class="operational-master-empty">
                        Belum ada data lokasi operasional.
                    </div>
                @endforelse

            </div>
        </div>
    </section>

    {{-- MASTER ITEM OPERASIONAL --}}
    <section class="panel operational-master-card">

        <div class="panel-heading">
            <div>
                <p class="eyebrow">MASTER ITEM</p>
                <h3>Item Operasional</h3>
            </div>

            <span class="status done">
                {{ $items->count() }} data
            </span>
        </div>

        <div class="operational-master-body">

            {{-- TAMBAH ITEM --}}
            <form
                method="POST"
                action="{{ route('operational-masters.items.store') }}"
                class="operational-master-form"
            >
                @csrf

                <div class="operational-master-record-grid">

                    <div class="operational-master-field">
                        <label for="item_code">
                            Kode item *
                        </label>

                        <input
                            id="item_code"
                            type="text"
                            name="code"
                            maxlength="50"
                            placeholder="078"
                            required
                        >
                    </div>

                    <div class="operational-master-field">
                        <label for="item_name">
                            Nama item *
                        </label>

                        <input
                            id="item_name"
                            type="text"
                            name="name"
                            maxlength="200"
                            placeholder="PLASTIK WREAPING"
                            required
                        >
                    </div>

                </div>

                <div class="operational-master-field">
                    <label for="item_category">
                        Kategori
                    </label>

                    <select
                        id="item_category"
                        name="category_id"
                    >
                        <option value="">
                            -- Tanpa kategori --
                        </option>

                        @foreach($categories->where('is_active', true) as $category)
                            <option value="{{ $category->id }}">
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button
                    type="submit"
                    class="button button-primary"
                >
                    + Tambah Item
                </button>
            </form>

            <h4 class="operational-master-section-title">
                Daftar Item Operasional
            </h4>

            <div class="operational-master-list">

                @forelse($items as $item)

                    <div class="operational-master-record">

                        <form
                            method="POST"
                            action="{{ route('operational-masters.items.update', $item) }}"
                        >
                            @csrf
                            @method('PUT')

                            <div class="operational-master-record-grid">

                                <div class="operational-master-field">
                                    <label for="item_code_{{ $item->id }}">
                                        Kode item
                                    </label>

                                    <input
                                        id="item_code_{{ $item->id }}"
                                        type="text"
                                        name="code"
                                        value="{{ $item->code }}"
                                        maxlength="50"
                                        required
                                    >
                                </div>

                                <div class="operational-master-field">
                                    <label for="item_name_{{ $item->id }}">
                                        Nama item
                                    </label>

                                    <input
                                        id="item_name_{{ $item->id }}"
                                        type="text"
                                        name="name"
                                        value="{{ $item->name }}"
                                        maxlength="200"
                                        required
                                    >
                                </div>

                            </div>

                            <div class="operational-master-field">
                                <label for="item_category_{{ $item->id }}">
                                    Kategori
                                </label>

                                <select
                                    id="item_category_{{ $item->id }}"
                                    name="category_id"
                                >
                                    <option value="">
                                        -- Tanpa kategori --
                                    </option>

                                    @foreach($categories as $category)
                                        <option
                                            value="{{ $category->id }}"
                                            @selected($item->category_id === $category->id)
                                        >
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="operational-master-record-actions">

                                <label class="operational-master-check">
                                    <input
                                        type="checkbox"
                                        name="is_active"
                                        value="1"
                                        @checked($item->is_active)
                                    >
                                    Aktif
                                </label>

                                <button
                                    type="submit"
                                    class="button button-ghost"
                                >
                                    Simpan Perubahan
                                </button>

                            </div>
                        </form>

                    </div>

                @empty
                    <div class="operational-master-empty">
                        Belum ada item operasional.
                    </div>
                @endforelse

            </div>
        </div>
    </section>

</div>

@endsection
