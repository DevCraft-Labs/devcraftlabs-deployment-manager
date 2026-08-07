<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deployment_scripts', function (Blueprint $table): void {
            $table->string('health_check_url', 2048)->nullable()->after('working_directory');
            $table->string('log_directory', 500)->nullable()->after('health_check_url');
        });
    }

    public function down(): void
    {
        Schema::table('deployment_scripts', function (Blueprint $table): void {
            $table->dropColumn(['health_check_url', 'log_directory']);
        });
    }
};