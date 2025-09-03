<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->string('variant_sku', 64)->nullable()->after('color');
        });

        // автозаповнення для існуючих (products.sku + 4 рандомних цифри)
        DB::statement("
          UPDATE product_variants pv
          JOIN products p ON p.id = pv.product_id
          SET pv.variant_sku = CONCAT(
            p.sku,
            '-',
            LPAD(FLOOR(RAND() * 10000), 4, '0')
          )
          WHERE pv.variant_sku IS NULL
        ");

        // після апдейту робимо поле унікальним
        Schema::table('product_variants', function (Blueprint $table) {
            $table->unique('variant_sku');
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropUnique(['variant_sku']);
            $table->dropColumn('variant_sku');
        });
    }
};
