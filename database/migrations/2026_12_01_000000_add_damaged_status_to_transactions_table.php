<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE transactions MODIFY status ENUM('active', 'overdue', 'returned', 'lost', 'damaged', 'pending', 'rejected') NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE transactions MODIFY status ENUM('active', 'overdue', 'returned', 'lost', 'pending', 'rejected') NOT NULL DEFAULT 'active'");
    }
};

