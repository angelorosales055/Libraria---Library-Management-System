<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->timestamp('paid_at')->nullable()->after('fine_paid');
            $table->foreignId('collected_by')->nullable()->after('paid_at')->constrained('users')->nullOnDelete();
            $table->string('receipt_no', 30)->nullable()->after('collected_by');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['paid_at', 'collected_by', 'receipt_no']);
        });
    }
};

