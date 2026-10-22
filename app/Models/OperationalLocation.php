<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperationalLocation extends Model
{
    protected $table = 'operational_locations';

    protected $fillable = [
        'code',
        'name',
        'address',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
