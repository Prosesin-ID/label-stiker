
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expeditions', function (Blueprint $table) {
            $table->id();

            $table->string('company_name', 150);

            $table->boolean('supports_air')->default(false);
            $table->boolean('supports_sea')->default(false);
            $table->boolean('supports_land')->default(false);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('company_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expeditions');
    }
};
