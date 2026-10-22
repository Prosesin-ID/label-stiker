<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsignmentCustomerItem extends Model
{
    protected $table = 'consignment_customer_items';

    protected $fillable = [
        'customer_id',
        'product_id',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(
            BusinessPartner::class,
            'customer_id'
        );
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(
            Product::class,
            'product_id'
        );
    }
}
