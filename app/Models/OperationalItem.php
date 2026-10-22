<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperationalItem extends Model
{
    protected $table = 'operational_items';

    protected $fillable = [
        'code',
        'name',
        'category_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'category_id' => 'integer',
            'is_active' => 'boolean',
        ];
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
}
