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
}
