<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expedition extends Model
{
    protected $table = 'expeditions';

    protected $fillable = [
        'company_name',
        'supports_air',
        'supports_sea',
        'supports_land',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'supports_air' => 'boolean',
            'supports_sea' => 'boolean',
            'supports_land' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function getShippingCategoriesAttribute(): array
    {
        $categories = [];

        if ($this->supports_air) {
            $categories[] = 'Udara';
        }

        if ($this->supports_sea) {
            $categories[] = 'Laut';
        }

        if ($this->supports_land) {
            $categories[] = 'Darat';
        }

        return $categories;
    }
}
