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
        Schema::table('collection_details', function (Blueprint $table) {
            $table->string('client_name', 255)->nullable()->after('client_id');
            $table->string('uen', 120)->nullable()->after('channel');
            $table->string('bucket', 30)->nullable()->after('uen');
            $table->string('reconciliation_status', 40)->nullable()->after('bucket');
            $table->string('reconciliation_notes', 255)->nullable()->after('reconciliation_status');
        });
    }

    public function down(): void
    {
        Schema::table('collection_details', function (Blueprint $table) {
            $table->dropColumn(['client_name', 'uen', 'bucket', 'reconciliation_status', 'reconciliation_notes']);
        });
    }
};
