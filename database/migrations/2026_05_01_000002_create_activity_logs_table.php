<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 50); // login, logout, payment, approval, checkout, return, reject, renew
            $table->text('details');
            $table->string('status', 30)->default('success'); // success, failed, warning
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['type', 'created_at']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
