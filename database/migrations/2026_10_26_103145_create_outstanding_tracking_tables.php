
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | OUTSTANDING PARTS
        |--------------------------------------------------------------------------
        |
        | Mencatat permintaan customer dan jumlah yang belum disuplai.
        | Berlaku untuk pengiriman NORMAL maupun CONSIGNMENT.
        |
        | Tahap awal: supplied_quantity dicatat melalui transaksi
        | pemenuhan yang akan dibuat pada tahap berikutnya.
        | Belum mengubah stok fisik secara otomatis.
        |
        */

        Schema::create('outstanding_parts', function (Blueprint $table) {
            $table->id();

            $table->date('request_date');

            $table->string('customer_name', 200);

            $table->string('customer_reference', 100)
                ->nullable();

            $table->string('supply_type', 20);

            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->nullOnDelete();

            $table->string('part_number', 100);
            $table->string('part_name', 255);

            $table->decimal(
                'requested_quantity',
                14,
                4
            );

            $table->decimal(
                'supplied_quantity',
                14,
                4
            )->default(0);

            $table->date('due_date')->nullable();

            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('customer_name');
            $table->index('supply_type');
            $table->index('request_date');
        });

        /*
        |--------------------------------------------------------------------------
        | OUTSTANDING DO
        |--------------------------------------------------------------------------
        |
        | Tracking pengembalian DOKUMEN DO ASLI.
        | Status pengiriman barang tidak berubah.
        |
        | Satu Delivery Order memiliki maksimal satu record tracking.
        |
        */

        Schema::create('delivery_order_document_trackings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('delivery_order_id')
                ->unique()
                ->constrained('delivery_orders')
                ->cascadeOnDelete();

            $table->boolean('original_received')
                ->default(false);

            $table->date('original_received_date')
                ->nullable();

            $table->string('received_by', 150)
                ->nullable();

            $table->string('document_holder', 150)
                ->nullable();

            $table->text('notes')->nullable();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('original_received');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'delivery_order_document_trackings'
        );

        Schema::dropIfExists(
            'outstanding_parts'
        );
    }
};
