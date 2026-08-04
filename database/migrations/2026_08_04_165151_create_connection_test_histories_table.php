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
        Schema::create('connection_test_histories', function (Blueprint $table) {
            $table->id();
            $table->string('connection_type', 32);
            $table->unsignedBigInteger('connection_id')->nullable();
            $table->boolean('is_success')->default(false);
            $table->unsignedInteger('latency_ms')->nullable();
            $table->integer('ttl')->nullable();
            $table->text('response')->nullable();
            $table->json('meta')->nullable();
            $table->foreignId('tested_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['connection_type', 'connection_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('connection_test_histories');
    }
};
