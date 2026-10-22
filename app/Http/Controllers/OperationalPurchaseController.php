<?php

namespace App\Http\Controllers;

use App\Models\OperationalCategory;
use App\Models\OperationalItem;
use App\Models\OperationalLocation;
use App\Models\OperationalNeed;
use App\Models\OperationalPurchase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OperationalPurchaseController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | DAFTAR PEMBELIAN OPERASIONAL
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $purchases = OperationalPurchase::query()
            ->with([
                'need',
                'category',
                'location',
                'creator',
            ])
            ->when(
                $search !== '',
                function ($query) use ($search) {
                    $query->where(function ($query) use ($search) {
                        $query
                            ->where(
                                'document_number',
                                'like',
                                '%' . $search . '%'
                            )
                            ->orWhere(
                                'recipient_name',
                                'like',
                                '%' . $search . '%'
                            );
                    });
                }
            )
            ->orderByDesc('document_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view(
            'operational-purchases.index',
            [
                'purchases' => $purchases,
                'search' => $search,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FORM PEMBELIAN OPERASIONAL
    |--------------------------------------------------------------------------
    */

    public function create(): View
    {
        $needs = OperationalNeed::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $categories = OperationalCategory::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $locations = OperationalLocation::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $items = OperationalItem::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'code',
                'name',
                'category_id',
            ]);

        return view(
            'operational-purchases.create',
            [
                'needs' => $needs,
                'categories' => $categories,
                'locations' => $locations,
                'items' => $items,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SIMPAN PEMBELIAN OPERASIONAL
    |--------------------------------------------------------------------------
    */

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'document_date' => [
                'required',
                'date',
            ],

            'recipient_name' => [
                'required',
                'string',
                'max:200',
            ],

            'need_id' => [
                'required',
                'integer',
                Rule::exists('operational_needs', 'id')
                    ->where('is_active', true),
            ],

            'category_id' => [
                'required',
                'integer',
                Rule::exists('operational_categories', 'id')
                    ->where('is_active', true),
            ],

            'location_id' => [
                'required',
                'integer',
                Rule::exists('operational_locations', 'id')
                    ->where('is_active', true),
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'approved_by' => [
                'nullable',
                'string',
                'max:150',
            ],

            'prepared_by' => [
                'nullable',
                'string',
                'max:150',
            ],

            'received_by' => [
                'nullable',
                'string',
                'max:150',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
                'max:100',
            ],

            'items.*.operational_item_id' => [
                'required',
                'integer',
                Rule::exists('operational_items', 'id')
                    ->where('is_active', true),
            ],

            'items.*.quantity' => [
                'required',
                'numeric',
                'gt:0',
                'max:999999',
                'decimal:0,4',
            ],

            'items.*.unit_price' => [
                'required',
                'numeric',
                'min:0',
                'max:9999999999',
                'decimal:0,2',
            ],

            'items.*.remark' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ], [
            'items.required' =>
                'Tambahkan minimal satu item pembelian.',

            'items.min' =>
                'Tambahkan minimal satu item pembelian.',

            'items.*.operational_item_id.required' =>
                'Pilih item operasional.',

            'items.*.quantity.gt' =>
                'Jumlah item harus lebih dari nol.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | AMBIL MASTER ITEM
        |--------------------------------------------------------------------------
        */

        $itemIds = collect($data['items'])
            ->pluck('operational_item_id')
            ->unique()
            ->values();

        $masterItems = OperationalItem::query()
            ->where('is_active', true)
            ->whereIn('id', $itemIds)
            ->get()
            ->keyBy('id');

        /*
        |--------------------------------------------------------------------------
        | HITUNG DETAIL PEMBELIAN
        |--------------------------------------------------------------------------
        */

        $detailItems = [];
        $totalQuantity = 0;
        $grandTotal = 0;

        foreach ($data['items'] as $index => $row) {
            $masterItem = $masterItems->get(
                (int) $row['operational_item_id']
            );

            if ($masterItem === null) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'items' =>
                            'Terdapat item operasional yang tidak ditemukan atau sudah nonaktif.',
                    ]);
            }

            $quantity = round(
                (float) $row['quantity'],
                4
            );

            $unitPrice = round(
                (float) $row['unit_price'],
                2
            );

            $totalPrice = round(
                $quantity * $unitPrice,
                2
            );

            $totalQuantity = round(
                $totalQuantity + $quantity,
                4
            );

            $grandTotal = round(
                $grandTotal + $totalPrice,
                2
            );

            $detailItems[] = [
                'line_number' => $index + 1,
                'operational_item_id' => $masterItem->id,
                'item_name' => $masterItem->name,
                'item_code' => $masterItem->code,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
                'remark' => $row['remark'] ?? null,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | SIMPAN HEADER DAN ITEM
        |--------------------------------------------------------------------------
        */

        $purchase = DB::transaction(function () use (
            $data,
            $detailItems,
            $totalQuantity,
            $grandTotal
        ) {
            $purchase = OperationalPurchase::create([
                'document_number' => 'TMP-' . Str::uuid(),
                'document_date' => $data['document_date'],
                'recipient_name' => trim(
                    $data['recipient_name']
                ),
                'need_id' => $data['need_id'],
                'category_id' => $data['category_id'],
                'location_id' => $data['location_id'],
                'description' => $data['description'] ?? null,
                'total_items' => count($detailItems),
                'total_quantity' => $totalQuantity,
                'grand_total' => $grandTotal,
                'approved_by' => $data['approved_by'] ?? null,
                'prepared_by' => $data['prepared_by'] ?? null,
                'received_by' => $data['received_by'] ?? null,
                'created_by' => auth()->id(),
            ]);

            /*
            | Format contoh: PMB26.10.001
            | Nomor unik berdasarkan ID transaksi.
            */

            $documentDate = \Illuminate\Support\Carbon::parse(
                $data['document_date']
            );

            $documentNumber = sprintf(
                'PMB%s.%s.%s',
                $documentDate->format('y'),
                $documentDate->format('m'),
                str_pad(
                    (string) $purchase->id,
                    3,
                    '0',
                    STR_PAD_LEFT
                )
            );

            $purchase->update([
                'document_number' => $documentNumber,
            ]);

            $purchase->items()->createMany($detailItems);

            return $purchase;
        });

        return redirect()
            ->route('operational-purchases.index')
            ->with(
                'success',
                'Pembelian operasional ' .
                $purchase->document_number .
                ' berhasil disimpan.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | DETAIL DOKUMEN PEMBELIAN OPERASIONAL
    |--------------------------------------------------------------------------
    |
    | Membaca dokumen PMB yang sudah tersimpan.
    | Tidak mengubah data atau nilai pembelian.
    |
    */

    public function show(
        OperationalPurchase $operationalPurchase
    ): View {
        $operationalPurchase->load([
            'need',
            'category',
            'location',
            'creator',
            'items.operationalItem',
        ]);

        return view(
            'operational-purchases.show',
            [
                'purchase' => $operationalPurchase,
            ]
        );
    }
}
