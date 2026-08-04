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
        Schema::create('application_settings', function (Blueprint $table) {
            $table->id();
            $table->string('server_name')->default('DevCraft Labs Server');
            $table->string('timezone')->default('UTC');
            $table->foreignId('default_telegram_connection_id')->nullable()->constrained('telegram_connections')->nullOnDelete();
            $table->foreignId('default_smtp_profile_id')->nullable()->constrained('smtp_profiles')->nullOnDelete();
            $table->foreignId('default_redis_profile_id')->nullable()->constrained('redis_profiles')->nullOnDelete();
            $table->unsignedInteger('retention_days')->default(30);
            $table->boolean('log_cleanup')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_settings');
    }
};
