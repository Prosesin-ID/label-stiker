<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperationalExpenseItem extends Model
{
    protected $fillable = [
        'operational_expense_id',
        'line_number',
        'operational_item_id',
        'item_name',
        'expense_amount',
        'total_amount',
        'remark',
    ];

    protected $casts = [
        'line_number' => 'integer',
        'expense_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function expense(): BelongsTo
    {
        return $this->belongsTo(
            OperationalExpense::class,
            'operational_expense_id'
        );
    }

    public function operationalItem(): BelongsTo
    {
        return $this->belongsTo(
            OperationalItem::class,
            'operational_item_id'
        );
    }
}
