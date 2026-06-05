<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('management_logs', function (Blueprint $table): void {
            if (! Schema::hasColumn('management_logs', 'contact_time')) {
                $table->time('contact_time')->nullable()->after('contact_date');
            }
            if (! Schema::hasColumn('management_logs', 'uen')) {
                $table->string('uen', 100)->nullable()->after('contact_time');
            }
            if (! Schema::hasColumn('management_logs', 'channel')) {
                $table->string('channel', 100)->nullable()->after('uen');
            }
        });
    }

    public function down(): void
    {
        Schema::table('management_logs', function (Blueprint $table): void {
            foreach (['channel', 'uen', 'contact_time'] as $column) {
                if (Schema::hasColumn('management_logs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
