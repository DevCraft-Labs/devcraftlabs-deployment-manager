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
        Schema::create('redis_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('host');
            $table->unsignedInteger('port')->default(6379);
            $table->string('username')->nullable();
            $table->text('password')->nullable();
            $table->unsignedInteger('database')->default(0);
            $table->boolean('tls')->default(false);
            $table->unsignedInteger('timeout')->default(5);
            $table->unsignedInteger('default_ttl')->default(300);
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('redis_profiles');
    }
};
