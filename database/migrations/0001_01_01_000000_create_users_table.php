<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->string('password');
            $table->enum('role', ['admin','librarian','user'])->default('user');
            $table->string('member_id')->nullable()->unique();
            $table->string('school_id')->nullable();
            $table->string('contact', 20)->nullable();
            $table->string('address')->nullable();
            $table->enum('type', ['student','faculty','public'])->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
