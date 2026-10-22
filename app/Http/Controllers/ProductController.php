<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\LabelBrandingService;
use App\Services\ProductImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Inventory
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): View
    {
        $search = $request
            ->string('search')
            ->trim()
            ->toString();

        /*
        |--------------------------------------------------------------------------
        | Data Inventory
        |--------------------------------------------------------------------------
        |
        | inventory_stock merupakan total saldo stock dari stock_balances.
        |
        | Dengan begitu stock yang tampil pada halaman Inventory otomatis
        | mengikuti proses Stock Masuk dan Stock Keluar.
        |
        */

        $products = Product::query()
            ->with('category')
            ->withSum(
                'stockBalances as inventory_stock',
                'quantity'
            )
            ->when(
                $search,
                fn ($query) =>
                    $query->where(
                        function ($query) use ($search) {

                            $query
                                ->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'sku',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'customer_part_no',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'supplier_code',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhereHas(
                                    'category',
                                    fn ($category) =>
                                        $category->where(
                                            'name',
                                            'like',
                                            "%{$search}%"
                                        )
                                );
                        }
                    )
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $categories = ProductCategory::query()
            ->orderBy('name')
            ->get();

        return view(
            'products.index',
            compact(
                'products',
                'search',
                'categories'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Simpan Item Inventory
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request
    ): RedirectResponse {

        $data = $request->validate([

            'product_category_id' => [
                'nullable',
                'integer',
                'exists:product_categories,id',
            ],

            'sku' => [
                'required',
                'string',
                'max:100',
                'unique:products,sku',
            ],

            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'description' => [
                'nullable',
                'string',
                'max:200',
            ],

            'customer_part_no' => [
                'nullable',
                'string',
                'max:100',
            ],

            'supplier_code' => [
                'nullable',
                'string',
                'max:100',
            ],

            'barcode_value' => [
                'nullable',
                'string',
                'max:150',
                'unique:products,barcode_value',
            ],

            'uom' => [
                'required',
                'string',
                'max:20',
            ],

            'logo' => [
                'nullable',
                'image',
                'mimes:png,jpg,jpeg,webp',
                'max:1024',
                'dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000',
            ],

        ]);


        /*
        |--------------------------------------------------------------------------
        | Logo
        |--------------------------------------------------------------------------
        */

        unset(
            $data['logo']
        );

        if (
            $request->hasFile(
                'logo'
            )
        ) {

            $data['logo_path'] =
                $request
                    ->file('logo')
                    ->store(
                        'logos/products',
                        'public'
                    );
        }


        /*
        |--------------------------------------------------------------------------
        | Barcode
        |--------------------------------------------------------------------------
        */

        $data['barcode_value'] =
            $data['barcode_value']
            ?? $data['sku'];


        if (
            Product::withTrashed()
                ->where(
                    'barcode_value',
                    $data['barcode_value']
                )
                ->exists()
        ) {

            return back()
                ->withErrors([
                    'barcode_value' =>
                        'Kode barcode otomatis dari SKU sudah dipakai item lain.',
                ])
                ->withInput();
        }


        /*
        |--------------------------------------------------------------------------
        | Buat Item
        |--------------------------------------------------------------------------
        |
        | Stock tidak dimasukkan secara manual di sini.
        |
        | Saldo stock dikelola melalui:
        |
        | - Stock Masuk
        | - Stock Keluar
        |
        */

        Product::create(
            $data
        );


        return back()->with(
            'success',
            'Item baru berhasil ditambahkan ke inventory.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Edit Item
    |--------------------------------------------------------------------------
    */

    public function edit(
        Product $product,
        LabelBrandingService $branding
    ): View {

        return view(
            'products.edit',
            [
                'product' =>
                    $product,

                'categories' =>
                    ProductCategory::query()
                        ->orderBy('name')
                        ->get(),

                'productLogoDataUri' =>
                    $branding->dataUriForPath(
                        $product->logo_path
                    ),
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Item
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Product $product
    ): RedirectResponse {

        $data = $request->validate([

            'product_category_id' => [
                'nullable',
                'integer',
                'exists:product_categories,id',
            ],

            'sku' => [
                'required',
                'string',
                'max:100',
                Rule::unique(
                    'products',
                    'sku'
                )->ignore(
                    $product
                ),
            ],

            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'description' => [
                'nullable',
                'string',
                'max:200',
            ],

            'customer_part_no' => [
                'nullable',
                'string',
                'max:100',
            ],

            'supplier_code' => [
                'nullable',
                'string',
                'max:100',
            ],

            'barcode_value' => [
                'nullable',
                'string',
                'max:150',
                Rule::unique(
                    'products',
                    'barcode_value'
                )->ignore(
                    $product
                ),
            ],

            'uom' => [
                'required',
                'string',
                'max:20',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'logo' => [
                'nullable',
                'image',
                'mimes:png,jpg,jpeg,webp',
                'max:1024',
                'dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000',
            ],

        ]);


        /*
        |--------------------------------------------------------------------------
        | Logo
        |--------------------------------------------------------------------------
        */

        unset(
            $data['logo']
        );

        if (
            $request->hasFile(
                'logo'
            )
        ) {

            $oldLogo =
                $product->logo_path;

            $data['logo_path'] =
                $request
                    ->file('logo')
                    ->store(
                        'logos/products',
                        'public'
                    );


            if (
                $oldLogo
            ) {

                Storage::disk(
                    'public'
                )->delete(
                    $oldLogo
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Barcode
        |--------------------------------------------------------------------------
        */

        $data['barcode_value'] =
            $data['barcode_value']
            ?? $data['sku'];


        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        $data['is_active'] =
            $request->boolean(
                'is_active'
            );


        /*
        |--------------------------------------------------------------------------
        | Cek Barcode
        |--------------------------------------------------------------------------
        */

        $barcodeConflict =
            Product::withTrashed()
                ->where(
                    'barcode_value',
                    $data['barcode_value']
                )
                ->whereKeyNot(
                    $product->id
                )
                ->exists();


        if (
            $barcodeConflict
        ) {

            return back()
                ->withErrors([
                    'barcode_value' =>
                        'Kode barcode sudah dipakai item lain.',
                ])
                ->withInput();
        }


        /*
        |--------------------------------------------------------------------------
        | Update
        |--------------------------------------------------------------------------
        */

        $product->update(
            $data
        );


        return redirect()
            ->route(
                'products.index'
            )
            ->with(
                'success',
                'Data inventory berhasil diperbarui.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Hapus Item
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Product $product
    ): RedirectResponse {

        $product->delete();


        return redirect()
            ->route(
                'products.index'
            )
            ->with(
                'success',
                'Item dihapus dari inventory. Histori label tetap aman.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Import Inventory
    |--------------------------------------------------------------------------
    */

    public function import(
        Request $request,
        ProductImportService $importer
    ): RedirectResponse {

        $request->validate(
            [
                'product_file' => [
                    'required',
                    'file',
                    'mimes:xlsx,xls,csv',
                    'max:10240',
                ],
            ],
            [
                'product_file.required' =>
                    'Pilih file Excel yang akan diimport.',

                'product_file.mimes' =>
                    'Format file harus XLSX, XLS, atau CSV.',

                'product_file.max' =>
                    'Ukuran file maksimal 10 MB.',
            ]
        );


        $result =
            $importer->import(
                $request->file(
                    'product_file'
                )
            );


        return back()
            ->with(
                'import_result',
                $result
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Template Import
    |--------------------------------------------------------------------------
    */

    public function importTemplate(
        ProductImportService $importer
    ) {

        return response()
            ->streamDownload(
                function () use ($importer) {

                    $importer->writeTemplate(
                        'php://output'
                    );
                },
                'template-import-inventory.xlsx',
                [
                    'Content-Type' =>
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ]
            );
    }
}