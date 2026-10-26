<?php

namespace App\Http\Controllers;

use App\Models\OutstandingPart;
use App\Models\OutstandingPartFulfillment;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OutstandingPartController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | DAFTAR OUTSTANDING PARTS
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $supplyType = (string) $request->query('supply_type', '');
        $status = (string) $request->query('status', '');

        $parts = OutstandingPart::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_reference', 'like', "%{$search}%")
                        ->orWhere('part_number', 'like', "%{$search}%")
                        ->orWhere('part_name', 'like', "%{$search}%");
                });
            })

            ->when(
                in_array($supplyType, ['NORMAL', 'CONSIGNMENT'], true),
                fn ($query) => $query->where(
                    'supply_type',
                    $supplyType
                )
            )

            ->when(
                $status === 'PENDING',
                fn ($query) => $query->where(
                    'supplied_quantity',
                    0
                )
            )

            ->when(
                $status === 'PARTIAL',
                fn ($query) => $query
                    ->where('supplied_quantity', '>', 0)
                    ->whereColumn(
                        'supplied_quantity',
                        '<',
                        'requested_quantity'
                    )
            )

            ->when(
                $status === 'COMPLETED',
                fn ($query) => $query->whereColumn(
                    'supplied_quantity',
                    '>=',
                    'requested_quantity'
                )
            )

            ->orderByDesc('request_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('outstanding-parts.index', compact(
            'parts',
            'search',
            'supplyType',
            'status'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | FORM PERMINTAAN
    |--------------------------------------------------------------------------
    */

    public function create(): View
    {
        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'sku', 'name']);

        return view(
            'outstanding-parts.create',
            compact('products')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SIMPAN PERMINTAAN PARTS
    |--------------------------------------------------------------------------
    */

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'request_date' => [
                'required',
                'date',
            ],

            'customer_name' => [
                'required',
                'string',
                'max:200',
            ],

            'customer_reference' => [
                'nullable',
                'string',
                'max:100',
            ],

            'supply_type' => [
                'required',
                Rule::in([
                    'NORMAL',
                    'CONSIGNMENT',
                ]),
            ],

            'product_id' => [
                'nullable',
                'integer',
                Rule::exists('products', 'id')
                    ->where('is_active', true),
            ],

            'part_number' => [
                'required_without:product_id',
                'nullable',
                'string',
                'max:100',
            ],

            'part_name' => [
                'required_without:product_id',
                'nullable',
                'string',
                'max:255',
            ],

            'requested_quantity' => [
                'required',
                'numeric',
                'gt:0',
                'max:9999999999',
                'decimal:0,4',
            ],

            'due_date' => [
                'nullable',
                'date',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        $product = !empty($data['product_id'])
            ? Product::query()
                ->where('is_active', true)
                ->findOrFail($data['product_id'])
            : null;

        $partNumber = $product?->sku
            ?? trim((string) ($data['part_number'] ?? ''));

        $partName = $product?->name
            ?? trim((string) ($data['part_name'] ?? ''));

        if ($partNumber === '' || $partName === '') {
            throw ValidationException::withMessages([
                'part_number' =>
                    'Kode part dan nama part wajib tersedia.',
            ]);
        }

        $part = OutstandingPart::create([
            'request_date' => $data['request_date'],

            'customer_name' => trim(
                $data['customer_name']
            ),

            'customer_reference' =>
                $data['customer_reference'] ?? null,

            'supply_type' => $data['supply_type'],

            'product_id' => $product?->id,

            'part_number' => $partNumber,

            'part_name' => $partName,

            'requested_quantity' =>
                $data['requested_quantity'],

            'supplied_quantity' => 0,

            'due_date' => $data['due_date'] ?? null,

            'notes' => $data['notes'] ?? null,

            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('outstanding-parts.show', $part)
            ->with(
                'success',
                "Permintaan #{$part->id} berhasil dicatat."
            );
    }

    /*
    |--------------------------------------------------------------------------
    | DETAIL & HISTORI PARTS
    |--------------------------------------------------------------------------
    */

    public function show(OutstandingPart $outstandingPart): View
    {
        $outstandingPart->load([
            'product',
            'creator',
        ]);

        $fulfillments = OutstandingPartFulfillment::query()
            ->with('creator')
            ->where(
                'outstanding_part_id',
                $outstandingPart->id
            )
            ->orderByDesc('supply_date')
            ->orderByDesc('id')
            ->get();

        return view('outstanding-parts.show', [
            'part' => $outstandingPart,
            'fulfillments' => $fulfillments,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CATAT SUPPLY PARSIAL
    |--------------------------------------------------------------------------
    */

    public function storeFulfillment(
        Request $request,
        OutstandingPart $outstandingPart
    ): RedirectResponse {
        $data = $request->validate([
            'supply_date' => [
                'required',
                'date',
            ],

            'quantity' => [
                'required',
                'numeric',
                'gt:0',
                'max:9999999999',
                'decimal:0,4',
            ],

            'reference_number' => [
                'required',
                'string',
                'max:100',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        DB::transaction(function () use (
            $data,
            $outstandingPart
        ) {
            $part = OutstandingPart::query()
                ->lockForUpdate()
                ->findOrFail($outstandingPart->id);

            // Simpan presisi sampai 4 angka desimal.
            $requested = (int) round(
                (float) $part->requested_quantity * 10000
            );

            $supplied = (int) round(
                (float) $part->supplied_quantity * 10000
            );

            $incoming = (int) round(
                (float) $data['quantity'] * 10000
            );

            $remaining = $requested - $supplied;

            // Cegah Qty melebihi sisa outstanding.
            if ($remaining <= 0 || $incoming > $remaining) {
                throw ValidationException::withMessages([
                    'quantity' =>
                        'Qty suplai melebihi sisa outstanding atau permintaan sudah selesai.',
                ]);
            }

            // Simpan histori setiap pemenuhan.
            OutstandingPartFulfillment::create([
                'outstanding_part_id' => $part->id,

                'supply_date' => $data['supply_date'],

                'quantity' => number_format(
                    $incoming / 10000,
                    4,
                    '.',
                    ''
                ),

                'reference_number' => trim(
                    $data['reference_number']
                ),

                'notes' => $data['notes'] ?? null,

                'created_by' => auth()->id(),
            ]);

            // Update total barang yang sudah disuplai.
            $part->update([
                'supplied_quantity' => number_format(
                    ($supplied + $incoming) / 10000,
                    4,
                    '.',
                    ''
                ),
            ]);
        });

        return redirect()
            ->route('outstanding-parts.show', $outstandingPart)
            ->with(
                'success',
                'Pemenuhan parts berhasil dicatat dalam histori.'
            );
    }
}
