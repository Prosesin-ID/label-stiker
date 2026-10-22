
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
        | KEBUTUHAN OPERASIONAL
        |--------------------------------------------------------------------------
        | Contoh: PACKAGING, PEMBAYARAN BULANAN
        */

        Schema::create('operational_needs', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | KATEGORI OPERASIONAL
        |--------------------------------------------------------------------------
        | Contoh: CONSUMABLE, SERVICE CHARGE DLL
        */

        Schema::create('operational_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | LOKASI OPERASIONAL
        |--------------------------------------------------------------------------
        | Contoh: MGK, DADAP
        */

        Schema::create('operational_locations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 150);
            $table->string('address', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | ITEM OPERASIONAL
        |--------------------------------------------------------------------------
        | Contoh: Kode 078 - PLASTIK WREAPING
        */

        Schema::create('operational_items', function (Blueprint $table) {
            $table->id();

            $table->string('code', 50)->unique();
            $table->string('name', 200);

            $table->foreignId('category_id')
                ->nullable()
                ->constrained('operational_categories')
                ->restrictOnDelete();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operational_items');
        Schema::dropIfExists('operational_locations');
        Schema::dropIfExists('operational_categories');
        Schema::dropIfExists('operational_needs');
    }
};
