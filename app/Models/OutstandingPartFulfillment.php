<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutstandingPartFulfillment extends Model
{
    protected $fillable = [
        'outstanding_part_id',
        'supply_date',
        'quantity',
        'reference_number',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'supply_date' => 'date',
        'quantity' => 'decimal:4',
    ];

    public function outstandingPart(): BelongsTo
    {
        return $this->belongsTo(
            OutstandingPart::class
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }
}
