<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->decimal('fine_cap', 8, 2)->default(500.00)->after('copies');
            $table->decimal('replacement_cost', 8, 2)->nullable()->after('fine_cap');
            $table->boolean('is_circulating')->default(true)->after('replacement_cost');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['fine_cap', 'replacement_cost', 'is_circulating']);
        });
    }
};
