<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deployment_executions', function (Blueprint $table): void {
            $table->string('status', 16)->default('queued')->after('triggered_via');
            $table->index(['status', 'started_at']);
        });

        DB::table('deployment_executions')->where('is_success', true)->update(['status' => 'succeeded']);
        DB::table('deployment_executions')->whereNotNull('finished_at')->where('is_success', false)->update(['status' => 'failed']);
        DB::table('deployment_executions')->whereNull('finished_at')->update(['status' => 'running']);
    }

    public function down(): void
    {
        Schema::table('deployment_executions', function (Blueprint $table): void {
            $table->dropIndex(['status', 'started_at']);
            $table->dropColumn('status');
        });
    }
};
