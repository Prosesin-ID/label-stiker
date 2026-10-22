<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperationalPurchaseItem extends Model
{
    protected $table = 'operational_purchase_items';

    protected $fillable = [
        'operational_purchase_id',
        'line_number',
        'operational_item_id',
        'item_name',
        'item_code',
        'quantity',
        'unit_price',
        'total_price',
        'remark',
    ];

    protected function casts(): array
    {
        return [
            'operational_purchase_id' => 'integer',
            'line_number' => 'integer',
            'operational_item_id' => 'integer',
            'quantity' => 'decimal:4',
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | RELASI DOKUMEN PEMBELIAN
    |--------------------------------------------------------------------------
    */

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(
            OperationalPurchase::class,
            'operational_purchase_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RELASI MASTER ITEM OPERASIONAL
    |--------------------------------------------------------------------------
    */

    public function operationalItem(): BelongsTo
    {
        return $this->belongsTo(
            OperationalItem::class,
            'operational_item_id'
        );
    }
}
