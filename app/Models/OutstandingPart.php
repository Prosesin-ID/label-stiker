<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutstandingPart extends Model
{
    protected $table = 'outstanding_parts';

    protected $fillable = [
        'request_date',
        'customer_name',
        'customer_reference',
        'supply_type',
        'product_id',
        'part_number',
        'part_name',
        'requested_quantity',
        'supplied_quantity',
        'due_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'request_date' => 'date',
        'due_date' => 'date',
        'requested_quantity' => 'decimal:4',
        'supplied_quantity' => 'decimal:4',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(
            Product::class,
            'product_id'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | JUMLAH OUTSTANDING
    |--------------------------------------------------------------------------
    */

    public function getOutstandingQuantityAttribute(): string
    {
        $requested = (float) $this->requested_quantity;
        $supplied = (float) $this->supplied_quantity;

        return number_format(
            max(0, $requested - $supplied),
            4,
            '.',
            ''
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STATUS PEMENUHAN
    |--------------------------------------------------------------------------
    */

    public function getFulfillmentStatusAttribute(): string
    {
        $requested = (float) $this->requested_quantity;
        $supplied = (float) $this->supplied_quantity;

        if ($requested > 0 && $supplied >= $requested) {
            return 'COMPLETED';
        }

        if ($supplied > 0) {
            return 'PARTIAL';
        }

        return 'PENDING';
    }
}
