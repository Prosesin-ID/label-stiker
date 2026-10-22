
@extends('layouts.app')

@section('title', 'Master Ekspedisi')
@section('eyebrow', 'MASTER DATA')
@section('heading', 'Master ekspedisi')

@section('content')

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

<div class="catalog-layout">

    {{-- DAFTAR EKSPEDISI --}}
    <section class="panel catalog-panel">

        <div class="panel-heading">
            <div>
                <p class="eyebrow">DATA EKSPEDISI</p>
                <h3>{{ $expeditions->total() }} perusahaan ekspedisi</h3>
            </div>

            <form
                method="GET"
                action="{{ route('expeditions.index') }}"
                class="table-search"
            >
                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Cari nama perusahaan..."
                >

                <button type="submit" aria-label="Cari">
                    &#8981;
                </button>
            </form>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Perusahaan</th>
                        <th>Kategori Pengiriman</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($expeditions as $expedition)
                        <tr>
                            <td>
                                {{ $expeditions->firstItem() + $loop->index }}
                            </td>

                            <td>
                                <strong>
                                    {{ $expedition->company_name }}
                                </strong>
                            </td>

                            <td>
                                <div style="display:flex;flex-wrap:wrap;gap:6px;">

                                    @if($expedition->supports_air)
                                        <span class="status done">
                                            Udara
                                        </span>
                                    @endif

                                    @if($expedition->supports_sea)
                                        <span class="status done">
                                            Laut
                                        </span>
                                    @endif

                                    @if($expedition->supports_land)
                                        <span class="status done">
                                            Darat
                                        </span>
                                    @endif

                                </div>
                            </td>

                            <td>
                                <span class="status {{ $expedition->is_active ? 'done' : 'draft' }}">
                                    {{ $expedition->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>

                            <td>
                                <div class="row-actions">

                                    <a
                                        href="{{ route('expeditions.edit', $expedition) }}"
                                        class="action-button"
                                        title="Edit ekspedisi"
                                        aria-label="Edit ekspedisi"
                                    >
                                        &#9998;
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route('expeditions.destroy', $expedition) }}"
                                        onsubmit="return confirm('Yakin ingin menghapus perusahaan ekspedisi ini?')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="action-button danger"
                                            title="Hapus ekspedisi"
                                            aria-label="Hapus ekspedisi"
                                        >
                                            &times;
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty-state">
                                Belum ada perusahaan ekspedisi.
                                Tambahkan melalui form di sebelah kanan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">
            {{ $expeditions->links() }}
        </div>

    </section>


    {{-- FORM TAMBAH EKSPEDISI --}}
    <aside class="panel add-product-panel">

        <p class="eyebrow">TAMBAH DATA</p>

        <h3>Ekspedisi baru</h3>

        <p>
            Daftarkan perusahaan ekspedisi beserta
            kategori pengiriman yang tersedia.
        </p>

        <form
            method="POST"
            action="{{ route('expeditions.store') }}"
        >
            @csrf

            <label for="company_name">
                Nama Perusahaan *
            </label>

            <input
                type="text"
                id="company_name"
                name="company_name"
                value="{{ old('company_name') }}"
                placeholder="Contoh: PT ABC Logistik"
                maxlength="150"
                required
            >

            <div style="margin-top:20px;margin-bottom:12px;">
                <strong>Kategori Pengiriman *</strong>
                <p style="margin-top:5px;">
                    Pilih minimal satu kategori.
                </p>
            </div>

            <label class="active-switch">
                <input
                    type="checkbox"
                    name="supports_air"
                    value="1"
                    @checked(old('supports_air'))
                >

                <span></span>

                <div>
                    <strong>Udara</strong>
                    <small>Pengiriman melalui transportasi udara.</small>
                </div>
            </label>

            <label class="active-switch">
                <input
                    type="checkbox"
                    name="supports_sea"
                    value="1"
                    @checked(old('supports_sea'))
                >

                <span></span>

                <div>
                    <strong>Laut</strong>
                    <small>Pengiriman melalui transportasi laut.</small>
                </div>
            </label>

            <label class="active-switch">
                <input
                    type="checkbox"
                    name="supports_land"
                    value="1"
                    @checked(old('supports_land'))
                >

                <span></span>

                <div>
                    <strong>Darat</strong>
                    <small>Pengiriman melalui transportasi darat.</small>
                </div>
            </label>

            <label class="active-switch">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    @checked(old('is_active', true))
                >

                <span></span>

                <div>
                    <strong>Ekspedisi aktif</strong>
                    <small>
                        Ekspedisi aktif nantinya dapat dipilih
                        saat membuat Delivery Order.
                    </small>
                </div>
            </label>

            <button
                type="submit"
                class="button button-primary button-wide"
            >
                Simpan ekspedisi
            </button>

        </form>
    </aside>

</div>

@endsection
