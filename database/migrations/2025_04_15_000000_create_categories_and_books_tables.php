<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('isbn')->unique();
            $table->string('accession_no')->nullable()->unique();
            $table->string('title');
            $table->string('author');
            $table->foreignId('category_id')->nullable()->constrained('categories', 'id')->onDelete('set null');
            $table->integer('copies')->default(1);
            $table->integer('available_copies')->default(1);
            $table->string('shelf', 100)->nullable();
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
        Schema::dropIfExists('categories');
    }
};
