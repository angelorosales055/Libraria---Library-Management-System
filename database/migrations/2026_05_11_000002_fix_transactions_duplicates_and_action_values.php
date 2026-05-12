<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First, fix any invalid action values in transactions
        DB::statement("
            UPDATE transactions 
            SET action = 'checkout' 
            WHERE action NOT IN ('checkout','return','renew','request','reject','renew_request','renew_approved','damage_return')
        ");

        // Remove duplicate transactions, keeping only the latest one for each combination
        DB::statement('
            DELETE FROM transactions 
            WHERE id NOT IN (
                SELECT id FROM (
                    SELECT MAX(id) as id
                    FROM transactions
                    GROUP BY member_id, book_id, issued_date, action
                ) as latest
            )
        ');

        // Add the unique composite constraint
        Schema::table('transactions', function (Blueprint $table) {
            $table->unique(['member_id', 'book_id', 'issued_date', 'action'], 'unique_transaction_composite');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropUnique('unique_transaction_composite');
        });
    }
};
