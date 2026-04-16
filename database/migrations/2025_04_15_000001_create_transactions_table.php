<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('books')->cascadeOnDelete();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('action', ['checkout','return','renew'])->default('checkout');
            $table->enum('status', ['active','overdue','returned','lost'])->default('active');
            $table->date('issued_date');
            $table->date('due_date');
            $table->date('returned_date')->nullable();
            $table->decimal('fine', 8, 2)->default(0);
            $table->boolean('fine_paid')->default(false);
            $table->string('payment_method', 20)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
