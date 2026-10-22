
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consignment_customer_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')
                ->constrained('business_partners')
                ->restrictOnDelete();

            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete();

            $table->timestamps();

            $table->unique(
                ['customer_id', 'product_id'],
                'consignment_customer_product_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consignment_customer_items');
    }
};
