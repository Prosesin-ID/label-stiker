<?php

namespace App\Http\Controllers;

use App\Models\Expedition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ExpeditionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | DAFTAR MASTER EKSPEDISI
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $expeditions = Expedition::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(
                    'company_name',
                    'like',
                    '%' . $search . '%'
                );
            })
            ->orderByDesc('is_active')
            ->orderBy('company_name')
            ->paginate(15)
            ->withQueryString();

        return view('expeditions.index', [
            'expeditions' => $expeditions,
            'search' => $search,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | TAMBAH PERUSAHAAN EKSPEDISI
    |--------------------------------------------------------------------------
    */

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateExpedition($request);

        Expedition::create($data);

        return redirect()
            ->route('expeditions.index')
            ->with(
                'success',
                'Perusahaan ekspedisi berhasil ditambahkan.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | HALAMAN EDIT EKSPEDISI
    |--------------------------------------------------------------------------
    */

    public function edit(Expedition $expedition): View
    {
        return view('expeditions.edit', [
            'expedition' => $expedition,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE EKSPEDISI
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Expedition $expedition
    ): RedirectResponse {
        $data = $this->validateExpedition($request);

        $expedition->update($data);

        return redirect()
            ->route('expeditions.index')
            ->with(
                'success',
                'Data ekspedisi berhasil diperbarui.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | HAPUS EKSPEDISI
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Expedition $expedition
    ): RedirectResponse {
        /*
        | Untuk tahap Master Ekspedisi ini,
        | relasi Delivery Order belum dibuat.
        |
        | Nanti ketika ekspedisi terhubung ke DO,
        | penghapusan perlu dilindungi agar histori
        | pengiriman tidak ikut kehilangan referensi.
        */

        $expedition->delete();

        return redirect()
            ->route('expeditions.index')
            ->with(
                'success',
                'Data ekspedisi berhasil dihapus.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDASI DATA EKSPEDISI
    |--------------------------------------------------------------------------
    */

    private function validateExpedition(Request $request): array
    {
        $validated = $request->validate([
            'company_name' => [
                'required',
                'string',
                'max:150',
            ],
            'supports_air' => [
                'nullable',
                'boolean',
            ],
            'supports_sea' => [
                'nullable',
                'boolean',
            ],
            'supports_land' => [
                'nullable',
                'boolean',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $data = [
            'company_name' => trim($validated['company_name']),
            'supports_air' => $request->boolean('supports_air'),
            'supports_sea' => $request->boolean('supports_sea'),
            'supports_land' => $request->boolean('supports_land'),
            'is_active' => $request->boolean('is_active'),
        ];

        /*
        |--------------------------------------------------------------------------
        | MINIMAL SATU KATEGORI PENGIRIMAN
        |--------------------------------------------------------------------------
        */

        if (
            !$data['supports_air']
            && !$data['supports_sea']
            && !$data['supports_land']
        ) {
            throw ValidationException::withMessages([
                'supports_air' =>
                    'Pilih minimal satu kategori pengiriman: Udara, Laut, atau Darat.',
            ]);
        }

        return $data;
    }
}
