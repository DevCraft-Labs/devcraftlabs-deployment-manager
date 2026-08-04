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
        Schema::create('deployment_scripts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->string('working_directory');
            $table->longText('script_content');
            $table->unsignedInteger('timeout')->default(300);
            $table->foreignId('telegram_connection_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('smtp_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('redis_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('cron_enabled')->default(false);
            $table->string('cron_expression')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deployment_scripts');
    }
};
