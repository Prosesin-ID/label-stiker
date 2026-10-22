
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
        | HEADER DOKUMEN BIAYA OPERASIONAL
        |--------------------------------------------------------------------------
        */

        Schema::create('operational_expenses', function (Blueprint $table) {
            $table->id();

            $table->string('document_number', 40)->unique();
            $table->date('document_date');

            $table->string('recipient_name', 200);

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

            $table->unsignedInteger('total_items')->default(0);

            $table->decimal('grand_total', 19, 2)->default(0);

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
        | DETAIL ITEM BIAYA OPERASIONAL
        |--------------------------------------------------------------------------
        */

        Schema::create('operational_expense_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('operational_expense_id')
                ->constrained('operational_expenses')
                ->cascadeOnDelete();

            $table->unsignedInteger('line_number');

            /*
             | Item boleh dipilih dari master atau diisi manual.
             | Jika master item dihapus, riwayat biaya tetap ada.
             */

            $table->foreignId('operational_item_id')
                ->nullable()
                ->constrained('operational_items')
                ->nullOnDelete();

            $table->string('item_name', 200);

            /*
             | Kolom mengikuti format dokumen client:
             | BIAYA RP. dan TOTAL RP.
             */

            $table->decimal('expense_amount', 19, 2)->default(0);
            $table->decimal('total_amount', 19, 2)->default(0);

            $table->text('remark')->nullable();

            $table->timestamps();

            $table->unique(
                ['operational_expense_id', 'line_number'],
                'operational_expense_line_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operational_expense_items');
        Schema::dropIfExists('operational_expenses');
    }
};
