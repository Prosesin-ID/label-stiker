<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OperationalPurchase extends Model
{
    protected $table = 'operational_purchases';

    protected $fillable = [
        'document_number',
        'document_date',
        'recipient_name',
        'need_id',
        'category_id',
        'location_id',
        'description',
        'total_items',
        'total_quantity',
        'grand_total',
        'approved_by',
        'prepared_by',
        'received_by',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'document_date' => 'date',
            'need_id' => 'integer',
            'category_id' => 'integer',
            'location_id' => 'integer',
            'total_items' => 'integer',
            'total_quantity' => 'decimal:4',
            'grand_total' => 'decimal:2',
            'created_by' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | RELASI KEBUTUHAN OPERASIONAL
    |--------------------------------------------------------------------------
    */

    public function need(): BelongsTo
    {
        return $this->belongsTo(
            OperationalNeed::class,
            'need_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RELASI KATEGORI OPERASIONAL
    |--------------------------------------------------------------------------
    */

    public function category(): BelongsTo
    {
        return $this->belongsTo(
            OperationalCategory::class,
            'category_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RELASI LOKASI OPERASIONAL
    |--------------------------------------------------------------------------
    */

    public function location(): BelongsTo
    {
        return $this->belongsTo(
            OperationalLocation::class,
            'location_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RELASI ITEM PEMBELIAN
    |--------------------------------------------------------------------------
    */

    public function items(): HasMany
    {
        return $this->hasMany(
            OperationalPurchaseItem::class,
            'operational_purchase_id'
        )->orderBy('line_number');
    }

    /*
    |--------------------------------------------------------------------------
    | RELASI PEMBUAT DOKUMEN
    |--------------------------------------------------------------------------
    */

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }
}
