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
        \DB::statement("ALTER TABLE transactions MODIFY action ENUM('checkout', 'return', 'renew', 'request', 'reject', 'renew_request', 'renew_approved') NOT NULL DEFAULT 'checkout'");
        \DB::statement("ALTER TABLE transactions MODIFY status ENUM('active', 'overdue', 'returned', 'lost', 'pending', 'rejected', 'renew_requested') NOT NULL DEFAULT 'active'");
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('original_transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['original_transaction_id']);
            $table->dropColumn('original_transaction_id');
        });
        \DB::statement("ALTER TABLE transactions MODIFY action ENUM('checkout', 'return', 'renew', 'request', 'reject') NOT NULL DEFAULT 'checkout'");
        \DB::statement("ALTER TABLE transactions MODIFY status ENUM('active', 'overdue', 'returned', 'lost', 'pending', 'rejected') NOT NULL DEFAULT 'active'");
    }
};
