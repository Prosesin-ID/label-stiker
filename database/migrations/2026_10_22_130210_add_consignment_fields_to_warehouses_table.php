
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {

            // Jenis gudang
            $table->string('warehouse_type', 30)
                ->default('normal')
                ->after('name')
                ->index();

            // Relasi gudang consignment customer
            $table->foreignId('customer_id')
                ->nullable()
                ->after('warehouse_type')
                ->constrained('business_partners')
                ->restrictOnDelete();

        });
    }

    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {

            $table->dropForeign(['customer_id']);

            $table->dropColumn([
                'customer_id',
                'warehouse_type',
            ]);

        });
    }
};
