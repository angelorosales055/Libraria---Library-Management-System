<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('queue')->index();
            $table->longText('payload');
            $table->longText('output')->nullable();
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('timeout')->nullable();
            $table->unsignedInteger('delay')->nullable();
            $table->dateTime('reserved_at')->nullable();
            $table->dateTime('available_at');
            $table->dateTime('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
