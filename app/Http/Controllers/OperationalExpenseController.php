<?php

namespace App\Http\Controllers;

use App\Models\OperationalCategory;
use App\Models\OperationalExpense;
use App\Models\OperationalItem;
use App\Models\OperationalLocation;
use App\Models\OperationalNeed;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OperationalExpenseController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | DAFTAR BIAYA OPERASIONAL
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $expenses = OperationalExpense::query()
            ->with([
                'need',
                'category',
                'location',
                'creator',
            ])
            ->when($search !== '', function ($query) use ($search) {
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
            })
            ->orderByDesc('document_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('operational-expenses.index', [
            'expenses' => $expenses,
            'search' => $search,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | FORM TAMBAH BIAYA OPERASIONAL
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
            ]);

        return view('operational-expenses.create', [
            'needs' => $needs,
            'categories' => $categories,
            'locations' => $locations,
            'items' => $items,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | SIMPAN BIAYA OPERASIONAL
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
                'nullable',
                'integer',
                Rule::exists('operational_items', 'id')
                    ->where('is_active', true),
            ],

            'items.*.item_name' => [
                'required',
                'string',
                'max:200',
            ],

            'items.*.expense_amount' => [
                'required',
                'numeric',
                'min:0',
                'max:99999999999999999',
                'decimal:0,2',
            ],

            'items.*.remark' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ], [
            'items.required' =>
                'Tambahkan minimal satu item biaya operasional.',

            'items.min' =>
                'Tambahkan minimal satu item biaya operasional.',

            'items.*.item_name.required' =>
                'Nama item biaya wajib diisi.',

            'items.*.expense_amount.required' =>
                'Nominal biaya wajib diisi.',

            'items.*.expense_amount.min' =>
                'Nominal biaya tidak boleh negatif.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | PERSIAPKAN DETAIL BIAYA
        |--------------------------------------------------------------------------
        |
        | Format dokumen client tidak memiliki kolom QTY.
        | Karena itu nilai BIAYA RP. menjadi TOTAL RP.
        | untuk setiap baris.
        |
        */

        $detailItems = [];
        $grandTotalInCents = 0;

        foreach ($data['items'] as $index => $row) {
            $amount = number_format(
                (float) $row['expense_amount'],
                2,
                '.',
                ''
            );

            $amountInCents = (int) round(
                (float) $amount * 100
            );

            $grandTotalInCents += $amountInCents;

            $detailItems[] = [
                'line_number' => $index + 1,

                'operational_item_id' =>
                    $row['operational_item_id'] ?? null,

                'item_name' => trim($row['item_name']),

                'expense_amount' => $amount,

                'total_amount' => $amount,

                'remark' => $row['remark'] ?? null,
            ];
        }

        $grandTotal = number_format(
            $grandTotalInCents / 100,
            2,
            '.',
            ''
        );

        /*
        |--------------------------------------------------------------------------
        | SIMPAN HEADER DAN DETAIL DALAM TRANSAKSI DATABASE
        |--------------------------------------------------------------------------
        */

        $expense = DB::transaction(function () use (
            $data,
            $detailItems,
            $grandTotal
        ) {
            $expense = OperationalExpense::create([
                'document_number' => 'TMP-' . Str::uuid(),

                'document_date' => $data['document_date'],

                'recipient_name' => trim(
                    $data['recipient_name']
                ),

                'need_id' => $data['need_id'],

                'category_id' => $data['category_id'],

                'location_id' => $data['location_id'],

                'description' =>
                    $data['description'] ?? null,

                'total_items' => count($detailItems),

                'grand_total' => $grandTotal,

                'approved_by' =>
                    $data['approved_by'] ?? null,

                'prepared_by' =>
                    $data['prepared_by'] ?? null,

                'received_by' =>
                    $data['received_by'] ?? null,

                'created_by' => auth()->id(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | NOMOR DOKUMEN BIO
            |--------------------------------------------------------------------------
            |
            | Contoh BIO26.10.001
            | Nomor menggunakan ID unik transaksi biaya.
            |
            */

            $documentDate =
                \Illuminate\Support\Carbon::parse(
                    $data['document_date']
                );

            $documentNumber = sprintf(
                'BIO%s.%s.%s',
                $documentDate->format('y'),
                $documentDate->format('m'),
                str_pad(
                    (string) $expense->id,
                    3,
                    '0',
                    STR_PAD_LEFT
                )
            );

            $expense->update([
                'document_number' => $documentNumber,
            ]);

            $expense->items()->createMany($detailItems);

            return $expense;
        });

        return redirect()
            ->route('operational-expenses.index')
            ->with(
                'success',
                'Biaya operasional ' .
                $expense->document_number .
                ' berhasil disimpan.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | DETAIL DOKUMEN BIO
    |--------------------------------------------------------------------------
    */

    public function show(
        OperationalExpense $operationalExpense
    ): View {
        $operationalExpense->load([
            'need',
            'category',
            'location',
            'creator',
            'items.operationalItem',
        ]);

        return view('operational-expenses.show', [
            'expense' => $operationalExpense,
        ]);
    }
}
