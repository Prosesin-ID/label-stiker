<?php

namespace App\Http\Controllers;

use App\Models\BusinessPartner;
use App\Models\ConsignmentCustomerItem;
use App\Models\Product;
use App\Models\StockBalance;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ConsignmentCustomerController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | DATA STOCK CONSIGNMENT CUSTOMER
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): View
    {
        /*
        |--------------------------------------------------------------------------
        | MASTER PELANGGAN
        |--------------------------------------------------------------------------
        */

        $customers = BusinessPartner::query()
            ->where('is_customer', true)
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'code',
                'name',
                'address',
            ]);

        /*
        |--------------------------------------------------------------------------
        | MASTER ITEM PARTS
        |--------------------------------------------------------------------------
        */

        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'sku',
                'name',
                'customer_part_no',
                'uom',
            ]);

        /*
        |--------------------------------------------------------------------------
        | FILTER PELANGGAN
        |--------------------------------------------------------------------------
        */

        $selectedCustomerId = $request->integer('customer_id');

        /*
        |--------------------------------------------------------------------------
        | GUDANG CONSIGNMENT CUSTOMER
        |--------------------------------------------------------------------------
        |
        | Hanya mengambil gudang milik pelanggan
        | dengan tipe consignment_customer.
        |
        | Gudang normal dan consignment WAF
        | tidak dimasukkan ke daftar ini.
        |
        */

        $customerWarehouses = Warehouse::query()
            ->where('warehouse_type', 'consignment_customer')
            ->with('customer')
            ->when(
                $selectedCustomerId > 0,
                fn ($query) => $query->where(
                    'customer_id',
                    $selectedCustomerId
                )
            )
            ->orderBy('name')
            ->get([
                'id',
                'code',
                'name',
                'customer_id',
                'warehouse_type',
                'is_active',
            ]);

        /*
        |--------------------------------------------------------------------------
        | ITEM CONSIGNMENT TERDAFTAR
        |--------------------------------------------------------------------------
        */

        $consignmentItems = ConsignmentCustomerItem::query()
            ->with([
                'customer',
                'product',
            ])
            ->when(
                $selectedCustomerId > 0,
                fn ($query) => $query->where(
                    'customer_id',
                    $selectedCustomerId
                )
            )
            ->orderByDesc('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | SALDO STOK GUDANG CUSTOMER
        |--------------------------------------------------------------------------
        |
        | Saldo diambil hanya dari gudang customer
        | yang terhubung dengan pelanggan.
        |
        | Data tidak diambil dari total Inventory.
        |
        */

        $warehouseIds = $customerWarehouses->pluck('id');

        $stockBalances = StockBalance::query()
            ->whereIn('warehouse_id', $warehouseIds)
            ->get([
                'product_id',
                'warehouse_id',
                'quantity',
            ]);

        /*
        |--------------------------------------------------------------------------
        | KELOMPOKKAN SALDO BERDASARKAN GUDANG
        |--------------------------------------------------------------------------
        */

        $balancesByWarehouse = $stockBalances
            ->groupBy('warehouse_id');

        /*
        |--------------------------------------------------------------------------
        | TAMPILKAN HALAMAN
        |--------------------------------------------------------------------------
        */

        return view(
            'consignment-customers.index',
            [
                'customers' => $customers,
                'products' => $products,
                'consignmentItems' => $consignmentItems,
                'customerWarehouses' => $customerWarehouses,
                'balancesByWarehouse' => $balancesByWarehouse,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | TAMBAH ITEM PARTS CONSIGNMENT
    |--------------------------------------------------------------------------
    */

    public function store(Request $request): RedirectResponse
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDASI DATA
        |--------------------------------------------------------------------------
        */

        $data = $request->validate([
            'customer_id' => [
                'required',
                'integer',
                Rule::exists('business_partners', 'id')
                    ->where('is_customer', true)
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],

            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | CEK ITEM DUPLIKAT
        |--------------------------------------------------------------------------
        */

        $exists = ConsignmentCustomerItem::query()
            ->where('customer_id', $data['customer_id'])
            ->where('product_id', $data['product_id'])
            ->exists();

        if ($exists) {
            return redirect()
                ->route(
                    'consignment-customer.index',
                    [
                        'customer_id' => $data['customer_id'],
                    ]
                )
                ->withErrors([
                    'product_id' =>
                        'Item ini sudah terdaftar sebagai consignment pelanggan tersebut.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | SIMPAN ITEM
        |--------------------------------------------------------------------------
        */

        ConsignmentCustomerItem::create($data);

        return redirect()
            ->route(
                'consignment-customer.index',
                [
                    'customer_id' => $data['customer_id'],
                ]
            )
            ->with(
                'success',
                'Item Parts Consignment berhasil ditambahkan.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT DATA PELANGGAN
    |--------------------------------------------------------------------------
    |
    | Mengubah nama dan kode pelanggan tanpa mengubah ID.
    | Data stok consignment tetap terhubung ke ID pelanggan.
    |--------------------------------------------------------------------------
    */

    public function updateCustomer(
        Request $request,
        BusinessPartner $customer
    ): RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | PASTIKAN PELANGGAN AKTIF
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $customer->is_customer && $customer->is_active,
            404,
            'Data pelanggan tidak ditemukan atau tidak aktif.'
        );

        /*
        |--------------------------------------------------------------------------
        | BERSIHKAN SPASI INPUT
        |--------------------------------------------------------------------------
        */

        $name = $request->input('name');
        $code = $request->input('code');

        $request->merge([
            'name' => is_string($name) ? trim($name) : $name,
            'code' => is_string($code) ? trim($code) : $code,
        ]);

        /*
        |--------------------------------------------------------------------------
        | VALIDASI SESUAI STRUKTUR DATABASE
        |--------------------------------------------------------------------------
        |
        | business_partners.name maksimal 160 karakter.
        | business_partners.code maksimal 40 karakter.
        |
        | Kode harus unik, termasuk terhadap kode
        | pelanggan yang pernah di-soft delete.
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate(
            [
                'name' => [
                    'required',
                    'string',
                    'max:160',
                ],

                'code' => [
                    'required',
                    'string',
                    'max:40',
                    Rule::unique('business_partners', 'code')
                        ->ignore($customer->id),
                ],

                'return_product_id' => [
                    'nullable',
                    'integer',
                    Rule::exists('products', 'id')
                        ->where('is_active', true)
                        ->whereNull('deleted_at'),
                ],
            ],
            [
                'name.required' =>
                    'Nama pelanggan wajib diisi.',

                'name.max' =>
                    'Nama pelanggan maksimal 160 karakter.',

                'code.required' =>
                    'Kode pelanggan wajib diisi.',

                'code.max' =>
                    'Kode pelanggan maksimal 40 karakter.',

                'code.unique' =>
                    'Kode pelanggan sudah digunakan oleh data pelanggan lain.',

                'return_product_id.exists' =>
                    'Item parts yang dipilih tidak ditemukan.',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | SIMPAN PERUBAHAN
        |--------------------------------------------------------------------------
        |
        | Hanya nama dan kode yang diperbarui.
        | ID, status, alamat, dan relasi pelanggan
        | tidak diubah.
        |--------------------------------------------------------------------------
        */

        $customer->name = $validated['name'];
        $customer->code = $validated['code'];

        $customer->save();

        /*
        |--------------------------------------------------------------------------
        | PERTAHANKAN FILTER SETELAH UPDATE
        |--------------------------------------------------------------------------
        */

        $filters = [
            'customer_id' => $customer->id,
        ];

        if (!empty($validated['return_product_id'])) {
            $filters['product_id'] = $validated['return_product_id'];
        }

        /*
        |--------------------------------------------------------------------------
        | KEMBALI KE HALAMAN STOCK CONSIGNMENT
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'consignment-customer.index',
                $filters
            )
            ->with(
                'success',
                'Data pelanggan berhasil diperbarui.'
            );
    }
}