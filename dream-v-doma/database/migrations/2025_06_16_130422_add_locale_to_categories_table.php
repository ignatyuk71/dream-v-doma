<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
        {
            Schema::table('categories', function (Blueprint $table) {
                $table->string('locale', 5)->nullable()->after('parent_id')->index();
            });

            // Якщо хочеш одразу встановити всім значення 'ua' — розкоментуй:
            // DB::table('categories')->update(['locale' => 'ua']);
        }

        public function down(): void
        {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('locale');
            });
        }
};
