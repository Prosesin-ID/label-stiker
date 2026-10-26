
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outstanding_part_fulfillments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('outstanding_part_id')
                ->constrained('outstanding_parts')
                ->restrictOnDelete();

            $table->date('supply_date');
            $table->decimal('quantity', 14, 4);
            $table->string('reference_number', 100);
            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index([
                'outstanding_part_id',
                'supply_date',
            ]);
        });

        Schema::create('delivery_order_document_events', function (Blueprint $table) {
            $table->id();

            $table->foreignId('delivery_order_id')
                ->constrained('delivery_orders')
                ->restrictOnDelete();

            $table->boolean('original_received');

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

            $table->index([
                'delivery_order_id',
                'created_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'delivery_order_document_events'
        );

        Schema::dropIfExists(
            'outstanding_part_fulfillments'
        );
    }
};
