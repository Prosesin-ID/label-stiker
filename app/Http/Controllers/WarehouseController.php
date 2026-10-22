<?php

namespace App\Http\Controllers;

use App\Models\BusinessPartner;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | DAFTAR MASTER GUDANG
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $warehouses = Warehouse::query()
            ->withCount(['stockBalances', 'stockMovements'])
            ->when(
                $search,
                fn ($query) => $query->where(function ($query) use ($search) {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                })
            )
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $customers = $this->activeCustomers();

        return view(
            'warehouses.index',
            compact('warehouses', 'search', 'customers')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SIMPAN GUDANG BARU
    |--------------------------------------------------------------------------
    */

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        Warehouse::create($data);

        return redirect()
            ->route('warehouses.index')
            ->with('success', 'Gudang baru berhasil disimpan.');
    }

    /*
    |--------------------------------------------------------------------------
    | FORM EDIT GUDANG
    |--------------------------------------------------------------------------
    */

    public function edit(Warehouse $warehouse): View
    {
        return view('warehouses.edit', [
            'warehouse' => $warehouse,
            'customers' => $this->activeCustomers(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE GUDANG
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Warehouse $warehouse
    ): RedirectResponse {
        $data = $this->validated($request, $warehouse);

        /*
        | Gudang yang sudah digunakan tidak boleh
        | dipindahkan ke kategori/pelanggan lain.
        */

        $typeChanged = $data['warehouse_type']
            !== $warehouse->warehouse_type;

        $customerChanged = (
            $data['customer_id'] !== (
                $warehouse->customer_id === null
                    ? null
                    : (int) $warehouse->customer_id
            )
        );

        if (
            ($typeChanged || $customerChanged)
            && (
                $warehouse->stockMovements()->exists()
                || $warehouse->stockBalances()
                    ->where('quantity', '!=', 0)
                    ->exists()
            )
        ) {
            throw ValidationException::withMessages([
                'warehouse_type' =>
                    'Jenis gudang atau pelanggan tidak dapat diubah karena gudang sudah memiliki histori atau saldo stok. Buat gudang baru jika diperlukan.',
            ]);
        }

        $warehouse->update($data);

        return redirect()
            ->route('warehouses.index')
            ->with('success', 'Data gudang berhasil diperbarui.');
    }

    /*
    |--------------------------------------------------------------------------
    | HAPUS GUDANG
    |--------------------------------------------------------------------------
    */

    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        if (
            $warehouse->stockMovements()->exists()
            || $warehouse->stockBalances()
                ->where('quantity', '!=', 0)
                ->exists()
        ) {
            return back()->withErrors([
                'warehouse' =>
                    'Gudang yang sudah punya histori stok tidak bisa dihapus. Nonaktifkan gudang jika tidak dipakai lagi.',
            ]);
        }

        $warehouse->delete();

        return redirect()
            ->route('warehouses.index')
            ->with('success', 'Gudang berhasil dihapus.');
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDASI MASTER GUDANG
    |--------------------------------------------------------------------------
    */

    private function validated(
        Request $request,
        ?Warehouse $warehouse = null
    ): array {
        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:30',
                'regex:/^[A-Za-z0-9._-]+$/',
                Rule::unique('warehouses', 'code')
                    ->ignore($warehouse),
            ],
            'name' => [
                'required',
                'string',
                'max:120',
            ],
            'address' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'warehouse_type' => [
                'required',
                Rule::in([
                    'normal',
                    'consignment_waf',
                    'consignment_customer',
                ]),
            ],
            'customer_id' => [
                'nullable',
                'required_if:warehouse_type,consignment_customer',
                'integer',
                Rule::exists('business_partners', 'id')
                    ->where('is_customer', true)
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],
            'allow_negative_stock' => [
                'nullable',
                'boolean',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ], [
            'code.regex' =>
                'Kode gudang hanya boleh berisi huruf, angka, titik, strip, dan underscore.',
            'customer_id.required_if' =>
                'Pilih pelanggan untuk gudang Consignment Customer.',
        ], [
            'code' => 'kode gudang',
            'name' => 'nama gudang',
            'address' => 'alamat',
            'warehouse_type' => 'jenis gudang',
            'customer_id' => 'pelanggan',
            'allow_negative_stock' => 'izin stok minus',
            'is_active' => 'status aktif',
        ]);

        $data['code'] = strtoupper($data['code']);

        $data['allow_negative_stock'] = $request->boolean(
            'allow_negative_stock'
        );

        $data['is_active'] = $request->boolean(
            'is_active',
            true
        );

        /*
        | Gudang selain consignment customer
        | tidak boleh memiliki relasi pelanggan.
        */

        if ($data['warehouse_type'] !== 'consignment_customer') {
            $data['customer_id'] = null;
        } else {
            $data['customer_id'] = (int) $data['customer_id'];
        }

        return $data;
    }

    /*
    |--------------------------------------------------------------------------
    | MASTER PELANGGAN AKTIF
    |--------------------------------------------------------------------------
    */

    private function activeCustomers()
    {
        return BusinessPartner::query()
            ->where('is_customer', true)
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'code',
                'name',
            ]);
    }
}
