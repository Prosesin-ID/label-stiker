<?php

namespace App\Http\Controllers;

use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderDocumentEvent;
use App\Models\DeliveryOrderDocumentTracking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OutstandingDoController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | DAFTAR SELURUH DO
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): View
    {
        $search = trim(
            (string) $request->query('search', '')
        );

        $status = (string) $request->query('status', '');

        $orders = DeliveryOrder::query()
            ->leftJoin(
                'delivery_order_document_trackings as dt',
                'dt.delivery_order_id',
                '=',
                'delivery_orders.id'
            )
            ->select('delivery_orders.*')
            ->addSelect([
                'dt.id as tracking_id',
                'dt.original_received as tracking_received',
                'dt.original_received_date as tracking_received_date',
                'dt.document_holder as tracking_holder',
            ])

            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where(
                            'delivery_orders.number',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'delivery_orders.to_company',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'delivery_orders.ship_to_company',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'delivery_orders.purchase_order_no',
                            'like',
                            "%{$search}%"
                        );
                });
            })

            ->when(
                $status === 'UNVERIFIED',
                fn ($query) => $query->whereNull('dt.id')
            )

            ->when(
                $status === 'OUTSTANDING',
                fn ($query) => $query
                    ->whereNotNull('dt.id')
                    ->where('dt.original_received', false)
            )

            ->when(
                $status === 'RETURNED',
                fn ($query) => $query
                    ->where('dt.original_received', true)
            )

            ->orderByDesc('delivery_orders.delivery_date')
            ->orderByDesc('delivery_orders.id')
            ->paginate(15)
            ->withQueryString();

        return view('outstanding-dos.index', compact(
            'orders',
            'search',
            'status'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | DETAIL TRACKING DOKUMEN DO
    |--------------------------------------------------------------------------
    */

    public function show(DeliveryOrder $deliveryOrder): View
    {
        $tracking = DeliveryOrderDocumentTracking::query()
            ->where(
                'delivery_order_id',
                $deliveryOrder->id
            )
            ->first();

        $events = DeliveryOrderDocumentEvent::query()
            ->with('updater')
            ->where(
                'delivery_order_id',
                $deliveryOrder->id
            )
            ->orderByDesc('id')
            ->get();

        return view('outstanding-dos.show', compact(
            'deliveryOrder',
            'tracking',
            'events'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | SIMPAN STATUS PENGEMBALIAN DO ASLI
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        DeliveryOrder $deliveryOrder
    ): RedirectResponse {
        $data = $request->validate([
            'document_status' => [
                'required',
                Rule::in([
                    'OUTSTANDING',
                    'RETURNED',
                ]),
            ],

            'original_received_date' => [
                'nullable',
                'required_if:document_status,RETURNED',
                'date',
            ],

            'received_by' => [
                'nullable',
                'required_if:document_status,RETURNED',
                'string',
                'max:150',
            ],

            'document_holder' => [
                'nullable',
                'string',
                'max:150',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        DB::transaction(function () use (
            $deliveryOrder,
            $data
        ) {
            // Cegah update bersamaan pada DO yang sama.
            DeliveryOrder::query()
                ->whereKey($deliveryOrder->id)
                ->lockForUpdate()
                ->firstOrFail();

            $returned =
                $data['document_status'] === 'RETURNED';

            $values = [
                'original_received' => $returned,

                'original_received_date' => $returned
                    ? $data['original_received_date']
                    : null,

                'received_by' => $returned
                    ? trim($data['received_by'])
                    : null,

                'document_holder' =>
                    $data['document_holder'] ?? null,

                'notes' => $data['notes'] ?? null,

                'updated_by' => auth()->id(),
            ];

            // Status terbaru disimpan di tabel tracking.
            $tracking = DeliveryOrderDocumentTracking::firstOrNew([
                'delivery_order_id' => $deliveryOrder->id,
            ]);

            $tracking->fill($values);
            $tracking->save();

            // Seluruh perubahan dicatat di histori.
            DeliveryOrderDocumentEvent::create(
                array_merge(
                    $values,
                    [
                        'delivery_order_id' =>
                            $deliveryOrder->id,
                    ]
                )
            );
        });

        return redirect()
            ->route('outstanding-dos.show', $deliveryOrder)
            ->with(
                'success',
                'Status dokumen DO asli berhasil dicatat beserta historinya.'
            );
    }
}
