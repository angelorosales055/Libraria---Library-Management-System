<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE transactions MODIFY action ENUM('checkout', 'return', 'renew', 'request', 'reject', 'renew_request', 'renew_approved', 'damage_return') NOT NULL DEFAULT 'checkout'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE transactions MODIFY action ENUM('checkout', 'return', 'renew', 'request', 'reject', 'renew_request', 'renew_approved') NOT NULL DEFAULT 'checkout'");
    }
};
