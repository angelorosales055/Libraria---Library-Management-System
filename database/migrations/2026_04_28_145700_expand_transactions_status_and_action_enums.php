<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE transactions MODIFY action ENUM('checkout', 'return', 'renew', 'request', 'reject') NOT NULL DEFAULT 'checkout'");
        DB::statement("ALTER TABLE transactions MODIFY status ENUM('active', 'overdue', 'returned', 'lost', 'pending', 'rejected') NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE transactions MODIFY action ENUM('checkout', 'return', 'renew') NOT NULL DEFAULT 'checkout'");
        DB::statement("ALTER TABLE transactions MODIFY status ENUM('active', 'overdue', 'returned', 'lost') NOT NULL DEFAULT 'active'");
    }
};
