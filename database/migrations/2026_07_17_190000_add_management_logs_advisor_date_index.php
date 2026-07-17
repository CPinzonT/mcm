<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('management_logs', function (Blueprint $table): void {
            $table->index(['advisor_id', 'contact_date']);
        });
    }

    public function down(): void
    {
        Schema::table('management_logs', function (Blueprint $table): void {
            $table->dropIndex(['advisor_id', 'contact_date']);
        });
    }
};
