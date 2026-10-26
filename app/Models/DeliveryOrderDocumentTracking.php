<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryOrderDocumentTracking extends Model
{
    protected $table = 'delivery_order_document_trackings';

    protected $fillable = [
        'delivery_order_id',
        'original_received',
        'original_received_date',
        'received_by',
        'document_holder',
        'notes',
        'updated_by',
    ];

    protected $casts = [
        'original_received' => 'boolean',
        'original_received_date' => 'date',
    ];

    public function deliveryOrder(): BelongsTo
    {
        return $this->belongsTo(
            DeliveryOrder::class,
            'delivery_order_id'
        );
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }

    public function getDocumentStatusAttribute(): string
    {
        return $this->original_received
            ? 'COMPLETED'
            : 'OUTSTANDING';
    }
}
