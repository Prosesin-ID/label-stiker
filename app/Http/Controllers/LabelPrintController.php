<?php

namespace App\Http\Controllers;

use App\Enums\StockMovementType;
use App\Http\Requests\StoreLabelPrintRequest;
use App\Models\LabelPrint;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\BarcodeService;
use App\Services\LabelBrandingService;
use App\Services\StockService;
use Barryvdh\DomPDF\Facade\Pdf;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class LabelPrintController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->toString();

        $labels = LabelPrint::query()
            ->with(['product', 'creator'])
            ->when(
                $search,
                fn ($query) => $query->where(
                    function ($query) use ($search) {
                        $query
                            ->where(
                                'purchase_order_no',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'delivery_note_no',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'customer_part_no',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'sender_address',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'recipient_address',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhereHas(
                                'product',
                                fn ($product) => $product
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
                            );
                    }
                )
            )
            ->when(
                $status === 'printed',
                fn ($query) =>
                    $query->whereNotNull('printed_at')
            )
            ->when(
                $status === 'ready',
                fn ($query) =>
                    $query->whereNull('printed_at')
            )
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view(
            'labels.index',
            compact(
                'labels',
                'search',
                'status'
            )
        );
    }

    public function create(
        BarcodeService $barcode
    ): View {
        /*
        |--------------------------------------------------------------------------
        | Produk Inventory
        |--------------------------------------------------------------------------
        |
        | Hanya produk aktif yang menggunakan tracking inventory.
        |
        */

        $products = Product::query()
            ->select([
                'id',
                'sku',
                'name',
                'description',
                'customer_part_no',
                'supplier_code',
                'barcode_value',
                'uom',
                'product_category_id',
                'track_inventory',
            ])
            ->with('category')
            ->where('is_active', true)
            ->where('track_inventory', true)
            ->withSum(
                'stockBalances as inventory_stock',
                'quantity'
            )
            ->orderBy('name')
            ->get()
            ->map(
                fn (Product $product) => [
                    'id' =>
                        $product->id,

                    'sku' =>
                        $product->sku,

                    'name' =>
                        $product->name,

                    'description' =>
                        $product->description,

                    'customer_part_no' =>
                        $product->customer_part_no,

                    'supplier_code' =>
                        $product->supplier_code,

                    'barcode_value' =>
                        $product->barcode_value,

                    'uom' =>
                        $product->uom,

                    'inventory_stock' =>
                        (float) (
                            $product->inventory_stock
                            ?? 0
                        ),

                    'category_name' =>
                        $product
                            ->category
                            ?->name,

                    'catalog_url' =>
                        $product->catalogUrl(),
                ]
            );

        /*
        |--------------------------------------------------------------------------
        | Gudang Aktif
        |--------------------------------------------------------------------------
        */

        $warehouses = Warehouse::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'code',
                'name',
            ]);

        /*
        |--------------------------------------------------------------------------
        | QR Catalog
        |--------------------------------------------------------------------------
        */

        $catalogQrs = $products
            ->pluck('catalog_url')
            ->push(
                route(
                    'catalogs.patria'
                )
            )
            ->unique()
            ->mapWithKeys(
                fn ($url) => [
                    $url =>
                        $barcode->qrDataUri(
                            $url
                        ),
                ]
            );

        return view(
            'labels.create',
            [
                'products' =>
                    $products,

                'warehouses' =>
                    $warehouses,

                'catalogQrs' =>
                    $catalogQrs,

                'catalogQrDataUri' =>
                    $barcode->qrDataUri(
                        route(
                            'catalogs.patria'
                        )
                    ),
            ]
        );
    }

    public function store(
        StoreLabelPrintRequest $request,
        StockService $stock
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Validasi Stock
        |--------------------------------------------------------------------------
        |
        | Warehouse tidak disimpan langsung ke tabel label_prints.
        | Warehouse digunakan untuk menentukan gudang mana yang stock-nya
        | akan dikurangi.
        |
        */

        $stockInput = $request->validate([
            'warehouse_id' => [
                'required',
                'integer',
                'exists:warehouses,id',
            ],

            'quantity' => [
                'required',
                'numeric',
                'gt:0',
                'max:99999999',
            ],
        ], [], [
            'warehouse_id' => 'gudang',
            'quantity' => 'jumlah',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Product
        |--------------------------------------------------------------------------
        */

        $product = Product::query()
            ->whereKey(
                $request->integer(
                    'product_id'
                )
            )
            ->where('is_active', true)
            ->where('track_inventory', true)
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Warehouse
        |--------------------------------------------------------------------------
        */

        $warehouse = Warehouse::query()
            ->whereKey(
                (int) $stockInput[
                    'warehouse_id'
                ]
            )
            ->where('is_active', true)
            ->firstOrFail();

        $quantity =
            (float) $stockInput[
                'quantity'
            ];

        /*
        |--------------------------------------------------------------------------
        | UUID Label
        |--------------------------------------------------------------------------
        */

        $uuid =
            (string) Str::uuid();

        $logoPath = null;

        /*
        |--------------------------------------------------------------------------
        | Logo Label
        |--------------------------------------------------------------------------
        */

        if (
            $request->hasFile(
                'logo'
            )
        ) {
            $logoPath =
                $request
                    ->file('logo')
                    ->store(
                        'logos/labels',
                        'public'
                    );
        } elseif (
            $product->logo_path &&
            Storage::disk('public')
                ->exists(
                    $product->logo_path
                )
        ) {
            $extension =
                pathinfo(
                    $product->logo_path,
                    PATHINFO_EXTENSION
                )
                ?: 'png';

            $logoPath =
                "logos/labels/{$uuid}.{$extension}";

            Storage::disk('public')
                ->copy(
                    $product->logo_path,
                    $logoPath
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Validated Label Data
        |--------------------------------------------------------------------------
        */

        $validated =
            collect(
                $request->validated()
            )
                ->except([
                    'logo',
                    'warehouse_id',
                ])
                ->all();

        /*
         * Pastikan quantity menggunakan hasil validasi
         * stock di atas.
         */
        $validated[
            'quantity'
        ] = $quantity;

        /*
        |--------------------------------------------------------------------------
        | Snapshot Stock Sebelum Label Dibuat
        |--------------------------------------------------------------------------
        |
        | Nilai ini tetap disimpan sebagai snapshot inventory pada saat
        | label dibuat.
        |
        */

        $inventoryStockBefore =
            (float) $product
                ->stockBalances()
                ->sum(
                    'quantity'
                );

        /*
        |--------------------------------------------------------------------------
        | Create Label + Stock Out
        |--------------------------------------------------------------------------
        |
        | Kedua proses berada dalam database transaction.
        |
        | Jika stock tidak cukup:
        | - Label tidak jadi dibuat
        | - Stock tidak berubah
        |
        */

        try {
            $labelPrint =
                DB::transaction(
                    function () use (
                        $request,
                        $stock,
                        $product,
                        $warehouse,
                        $quantity,
                        $uuid,
                        $logoPath,
                        $validated,
                        $inventoryStockBefore
                    ) {
                        /*
                        |--------------------------------------------------------------------------
                        | Create Label
                        |--------------------------------------------------------------------------
                        */

                        $labelPrint =
                            LabelPrint::create([
                                ...$validated,

                                'purchase_order_no' =>
                                    $validated[
                                        'purchase_order_no'
                                    ]
                                    ?? '',

                                'quantity' =>
                                    $quantity,

                                'uuid' =>
                                    $uuid,

                                'created_by' =>
                                    $request
                                        ->user()
                                        ->id,

                                'barcode_value' =>
                                    $product
                                        ->barcode_value,

                                /*
                                 * Snapshot stock sebelum
                                 * dikurangi oleh label.
                                 */
                                'inventory_stock' =>
                                    $inventoryStockBefore,

                                'logo_path' =>
                                    $logoPath,

                                'product_snapshot' => [
                                    ...$product->only([
                                        'sku',
                                        'name',
                                        'description',
                                        'customer_part_no',
                                        'supplier_code',
                                        'barcode_value',
                                        'uom',
                                    ]),

                                    'category_name' =>
                                        $product
                                            ->category
                                            ?->name,

                                    'catalog_url' =>
                                        $product
                                            ->catalogUrl(),
                                ],
                            ]);

                        /*
                        |--------------------------------------------------------------------------
                        | Stock Keluar Otomatis
                        |--------------------------------------------------------------------------
                        |
                        | Qty label menjadi stock keluar.
                        |
                        | Contoh:
                        | Stock awal = 100
                        | Qty label  = 20
                        | Stock akhir = 80
                        |
                        */

                        $stock->post(
                            $product,
                            $warehouse,
                            -$quantity,
                            StockMovementType::StockOut,
                            0,
                            reference: $labelPrint,
                            postedBy:
                                $request
                                    ->user()
                                    ->id,
                            notes:
                                'Stock keluar otomatis dari pembuatan label '
                                . $labelPrint->uuid
                                . '.',
                        );

                        return $labelPrint;
                    },
                    3
                );
        } catch (
            DomainException $exception
        ) {
            /*
             * Kalau stock tidak cukup,
             * hapus file logo yang sempat dibuat.
             */
            $this->deleteStoredLabelLogo(
                $logoPath
            );

            return back()
                ->withErrors([
                    'quantity' =>
                        $exception
                            ->getMessage(),
                ])
                ->withInput();
        } catch (
            Throwable $exception
        ) {
            /*
             * Kalau terjadi error database lainnya,
             * jangan tinggalkan file logo sampah.
             */
            $this->deleteStoredLabelLogo(
                $logoPath
            );

            throw $exception;
        }

        return redirect()
            ->route(
                'labels.show',
                $labelPrint
            )
            ->with(
                'success',
                'Label berhasil dibuat dan stock inventory otomatis berkurang '
                . $quantity
                . ' '
                . $product->uom
                . ' dari gudang '
                . $warehouse->code
                . '.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | HAPUS LABEL
    |--------------------------------------------------------------------------
    */

    public function destroy(
        LabelPrint $labelPrint
    ): RedirectResponse {
        /*
         * Untuk sementara penghapusan label hanya
         * menghapus riwayat label.
         *
         * Stock yang sudah keluar TIDAK dikembalikan
         * otomatis.
         *
         * Jika nanti mentor/client meminta sistem
         * reversal stock, fitur tersebut bisa
         * ditambahkan kemudian.
         */

        $this->deleteStoredLabelLogo(
            $labelPrint->logo_path
        );

        $labelPrint->delete();

        return redirect()
            ->route(
                'labels.index'
            )
            ->with(
                'success',
                'Label berhasil dihapus.'
            );
    }

    public function show(
        LabelPrint $labelPrint,
        BarcodeService $barcode,
        LabelBrandingService $branding
    ): View {
        return view(
            'labels.show',
            $this->viewData(
                $labelPrint,
                $barcode,
                $branding
            )
        );
    }

    public function pdf(
        LabelPrint $labelPrint,
        BarcodeService $barcode,
        LabelBrandingService $branding
    ) {
        /*
         * Membuka / mencetak ulang PDF
         * TIDAK mengurangi stock lagi.
         */
        $labelPrint->update([
            'printed_at' =>
                now(),
        ]);

        return Pdf::loadView(
            'labels.pdf',
            [
                'pages' =>
                    $this->printPages(
                        collect([
                            $labelPrint->fresh(),
                        ]),
                        [],
                        $barcode,
                        $branding
                    ),
            ]
        )
            ->setPaper([
                0,
                0,
                283.465,
                283.465,
            ])
            ->stream(
                'label-'
                . (
                    $labelPrint
                        ->purchase_order_no
                    ?: $labelPrint
                        ->uuid
                )
                . '.pdf'
            );
    }

    public function bulkPdf(
        Request $request,
        BarcodeService $barcode,
        LabelBrandingService $branding
    ) {
        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([
                'label_ids' => [
                    'required',
                    'array',
                    'min:1',
                    'max:100',
                ],

                'label_ids.*' => [
                    'integer',
                    'distinct',
                    'exists:label_prints,id',
                ],

                'copies' => [
                    'nullable',
                    'array',
                ],

                'copies.*' => [
                    'nullable',
                    'integer',
                    'min:1',
                    'max:50',
                ],
            ]);

        /*
        |--------------------------------------------------------------------------
        | Label IDs
        |--------------------------------------------------------------------------
        */

        $ids =
            collect(
                $validated[
                    'label_ids'
                ]
            )
                ->map(
                    fn ($id) =>
                        (int) $id
                )
                ->values();

        /*
        |--------------------------------------------------------------------------
        | Copies
        |--------------------------------------------------------------------------
        */

        $copies =
            collect(
                $validated[
                    'copies'
                ]
                ?? []
            );

        /*
        |--------------------------------------------------------------------------
        | Total Pages
        |--------------------------------------------------------------------------
        */

        $totalPages =
            $ids->sum(
                fn ($id) =>
                    (int) (
                        $copies->get(
                            $id,
                            1
                        )
                    )
            );

        if (
            $totalPages > 300
        ) {
            return back()
                ->withErrors([
                    'label_ids' =>
                        'Maksimal 300 halaman dalam satu file PDF.',
                ])
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | Load Labels
        |--------------------------------------------------------------------------
        */

        $labelsById =
            LabelPrint::with(
                'product'
            )
                ->whereIn(
                    'id',
                    $ids
                )
                ->get()
                ->keyBy(
                    'id'
                );

        $labels =
            $ids
                ->map(
                    fn ($id) =>
                        $labelsById->get(
                            $id
                        )
                )
                ->filter();

        /*
        |--------------------------------------------------------------------------
        | Mark as Printed
        |--------------------------------------------------------------------------
        |
        | Bulk print hanya mengubah status printed.
        | Tidak mengurangi stock.
        |
        */

        LabelPrint::whereIn(
            'id',
            $ids
        )->update([
            'printed_at' =>
                now(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Generate PDF
        |--------------------------------------------------------------------------
        */

        return Pdf::loadView(
            'labels.pdf',
            [
                'pages' =>
                    $this->printPages(
                        $labels,
                        $copies,
                        $barcode,
                        $branding
                    ),
            ]
        )
            ->setPaper([
                0,
                0,
                283.465,
                283.465,
            ])
            ->stream(
                'label-bulk-'
                . now()->format(
                    'Ymd-His'
                )
                . '.pdf'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | VIEW DATA
    |--------------------------------------------------------------------------
    */

    private function viewData(
        LabelPrint $labelPrint,
        BarcodeService $barcode,
        LabelBrandingService $branding
    ): array {
        $labelPrint->loadMissing(
            'product'
        );

        return [
            'label' =>
                $labelPrint,

            'partBarcode' =>
                $barcode->html(
                    $labelPrint
                        ->barcode_value
                ),

            'catalogQrDataUri' =>
                $barcode->qrDataUri(
                    $labelPrint
                        ->product_snapshot[
                            'catalog_url'
                        ]
                    ?? route(
                        'catalogs.patria'
                    )
                ),

            'logoDataUri' =>
                $branding->dataUriForPath(
                    $labelPrint
                        ->logo_path
                )
                ?: $branding
                    ->publicLabelLogoDataUri()
                ?: $branding
                    ->logoDataUri(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | PRINT PAGES
    |--------------------------------------------------------------------------
    */

    private function printPages(
        Collection $labels,
        Collection|array $copies,
        BarcodeService $barcode,
        LabelBrandingService $branding
    ): array {
        $copies =
            collect(
                $copies
            );

        $pages = [];

        foreach (
            $labels as $label
        ) {
            $label->loadMissing(
                'product'
            );

            /*
             * QR katalog.
             */
            $catalogQrDataUri =
                $barcode->qrDataUri(
                    $label
                        ->product_snapshot[
                            'catalog_url'
                        ]
                    ?? route(
                        'catalogs.patria'
                    )
                );

            /*
             * Logo.
             */
            $logoDataUri =
                $branding->dataUriForPath(
                    $label->logo_path
                )
                ?: $branding
                    ->publicLabelLogoDataUri()
                ?: $branding
                    ->logoDataUri();

            /*
             * Buat halaman sesuai jumlah copy.
             *
             * Jumlah copy tidak mempengaruhi stock.
             */
            foreach (
                range(
                    1,
                    (int) $copies->get(
                        $label->id,
                        1
                    )
                )
                as $copy
            ) {
                $pages[] = [
                    'label' =>
                        $label,

                    'catalogQrDataUri' =>
                        $catalogQrDataUri,

                    'logoDataUri' =>
                        $logoDataUri,
                ];
            }
        }

        return $pages;
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE STORED LABEL LOGO
    |--------------------------------------------------------------------------
    */

    private function deleteStoredLabelLogo(
        ?string $logoPath
    ): void {
        if (
            ! $logoPath
        ) {
            return;
        }

        if (
            ! str_starts_with(
                $logoPath,
                'logos/labels/'
            )
        ) {
            return;
        }

        if (
            ! Storage::disk('public')
                ->exists(
                    $logoPath
                )
        ) {
            return;
        }

        Storage::disk('public')
            ->delete(
                $logoPath
            );
    }
}