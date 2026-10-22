
@extends('layouts.app')

@section('title', 'Edit Ekspedisi')
@section('eyebrow', 'MASTER DATA')
@section('heading', 'Edit ekspedisi')

@section('content')

<div class="edit-product-layout">

    <form
        method="POST"
        action="{{ route('expeditions.update', $expedition) }}"
        class="panel edit-product-form"
    >
        @csrf
        @method('PUT')

        <div class="panel-heading">
            <div>
                <p class="eyebrow">DATA EKSPEDISI</p>
                <h3>{{ $expedition->company_name }}</h3>
            </div>

            <span class="status {{ $expedition->is_active ? 'done' : 'draft' }}">
                {{ $expedition->is_active ? 'Aktif' : 'Nonaktif' }}
            </span>
        </div>

        @if($errors->any())
            <div class="error-box edit-error">
                <strong>Periksa data berikut:</strong>

                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="edit-form-body">

            {{-- NAMA PERUSAHAAN --}}
            <div class="field">
                <label for="company_name">
                    Nama Perusahaan <b>*</b>
                </label>

                <input
                    type="text"
                    id="company_name"
                    name="company_name"
                    value="{{ old('company_name', $expedition->company_name) }}"
                    maxlength="150"
                    placeholder="Nama perusahaan ekspedisi"
                    required
                >
            </div>

            {{-- KATEGORI PENGIRIMAN --}}
            <div class="field">
                <label>
                    Kategori Pengiriman <b>*</b>
                </label>

                <small>
                    Pilih minimal satu kategori pengiriman.
                    Satu perusahaan dapat memiliki lebih dari satu kategori.
                </small>
            </div>

            {{-- UDARA --}}
            <label class="active-switch">
                <input
                    type="checkbox"
                    name="supports_air"
                    value="1"
                    @checked(old('supports_air', $expedition->supports_air))
                >

                <span></span>

                <div>
                    <strong>Udara</strong>
                    <small>
                        Pengiriman melalui transportasi udara.
                    </small>
                </div>
            </label>

            {{-- LAUT --}}
            <label class="active-switch">
                <input
                    type="checkbox"
                    name="supports_sea"
                    value="1"
                    @checked(old('supports_sea', $expedition->supports_sea))
                >

                <span></span>

                <div>
                    <strong>Laut</strong>
                    <small>
                        Pengiriman melalui transportasi laut.
                    </small>
                </div>
            </label>

            {{-- DARAT --}}
            <label class="active-switch">
                <input
                    type="checkbox"
                    name="supports_land"
                    value="1"
                    @checked(old('supports_land', $expedition->supports_land))
                >

                <span></span>

                <div>
                    <strong>Darat</strong>
                    <small>
                        Pengiriman melalui transportasi darat.
                    </small>
                </div>
            </label>

            {{-- STATUS AKTIF --}}
            <label class="active-switch">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    @checked(old('is_active', $expedition->is_active))
                >

                <span></span>

                <div>
                    <strong>Ekspedisi aktif</strong>
                    <small>
                        Ekspedisi aktif dapat digunakan
                        sebagai referensi pengiriman.
                    </small>
                </div>
            </label>

        </div>

        {{-- TOMBOL AKSI --}}
        <div class="edit-form-actions">

            <a
                href="{{ route('expeditions.index') }}"
                class="button button-ghost"
            >
                Batal
            </a>

            <button
                type="submit"
                class="button button-primary"
            >
                Simpan perubahan
            </button>

        </div>

    </form>

    {{-- INFORMASI EKSPEDISI --}}
    <aside class="panel edit-product-note">

        <span>&#9432;</span>

        <h3>Informasi Ekspedisi</h3>

        <p>
            Kelola nama perusahaan dan kategori
            pengiriman yang tersedia.
        </p>

        <dl>
            <div>
                <dt>ID Ekspedisi</dt>
                <dd>{{ $expedition->id }}</dd>
            </div>

            <div>
                <dt>Terdaftar</dt>
                <dd>
                    {{ $expedition->created_at?->format('d/m/Y') ?? '-' }}
                </dd>
            </div>

            <div>
                <dt>Terakhir diubah</dt>
                <dd>
                    {{ $expedition->updated_at?->diffForHumans() ?? '-' }}
                </dd>
            </div>
        </dl>

    </aside>

</div>

@endsection
