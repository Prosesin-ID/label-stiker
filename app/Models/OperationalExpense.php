<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OperationalExpense extends Model
{
    protected $fillable = [
        'document_number',
        'document_date',
        'recipient_name',
        'need_id',
        'category_id',
        'location_id',
        'description',
        'total_items',
        'grand_total',
        'approved_by',
        'prepared_by',
        'received_by',
        'created_by',
    ];

    protected $casts = [
        'document_date' => 'date',
        'total_items' => 'integer',
        'grand_total' => 'decimal:2',
    ];

    public function need(): BelongsTo
    {
        return $this->belongsTo(
            OperationalNeed::class,
            'need_id'
        );
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(
            OperationalCategory::class,
            'category_id'
        );
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(
            OperationalLocation::class,
            'location_id'
        );
    }

    public function items(): HasMany
    {
        return $this->hasMany(
            OperationalExpenseItem::class,
            'operational_expense_id'
        )->orderBy('line_number');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }
}
