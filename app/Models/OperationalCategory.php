<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperationalCategory extends Model
{
    protected $table = 'operational_categories';

    protected $fillable = [
        'name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
