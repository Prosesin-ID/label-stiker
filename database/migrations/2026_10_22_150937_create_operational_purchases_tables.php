
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
        | HEADER PEMBELIAN OPERASIONAL
        |--------------------------------------------------------------------------
        */

        Schema::create('operational_purchases', function (Blueprint $table) {
            $table->id();

            // Contoh format: PMB26.06.020
            $table->string('document_number', 40)->unique();

            $table->date('document_date');

            // Tujuan dokumen (TO)
            $table->string('recipient_name', 200);

            // KEBUTUHAN, KATAGORI, LOKASI
            $table->foreignId('need_id')
                ->constrained('operational_needs')
                ->restrictOnDelete();

            $table->foreignId('category_id')
                ->constrained('operational_categories')
                ->restrictOnDelete();

            $table->foreignId('location_id')
                ->constrained('operational_locations')
                ->restrictOnDelete();

            $table->text('description')->nullable();

            // Ringkasan dokumen
            $table->unsignedInteger('total_items')->default(0);
            $table->decimal('total_quantity', 19, 4)->default(0);
            $table->decimal('grand_total', 19, 2)->default(0);

            // Nama penanggung jawab / tanda tangan
            $table->string('approved_by', 150)->nullable();
            $table->string('prepared_by', 150)->nullable();
            $table->string('received_by', 150)->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('document_date');
        });

        /*
        |--------------------------------------------------------------------------
        | DETAIL ITEM PEMBELIAN OPERASIONAL
        |--------------------------------------------------------------------------
        */

        Schema::create('operational_purchase_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('operational_purchase_id')
                ->constrained('operational_purchases')
                ->cascadeOnDelete();

            $table->unsignedInteger('line_number');

            $table->foreignId('operational_item_id')
                ->constrained('operational_items')
                ->restrictOnDelete();

            /*
            | Simpan snapshot nama dan kode item
            | agar dokumen lama tidak berubah saat
            | master item diperbarui.
            */

            $table->string('item_name', 200);
            $table->string('item_code', 50);

            $table->decimal('quantity', 19, 4);
            $table->decimal('unit_price', 19, 2);
            $table->decimal('total_price', 19, 2);

            $table->text('remark')->nullable();

            $table->timestamps();

            $table->unique([
                'operational_purchase_id',
                'line_number',
            ], 'op_purchase_line_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operational_purchase_items');
        Schema::dropIfExists('operational_purchases');
    }
};
