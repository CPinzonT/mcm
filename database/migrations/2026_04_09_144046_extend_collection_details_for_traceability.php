<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('collection_details', function (Blueprint $table) {
            $table->date('payment_date')->nullable()->change();
            $table->unsignedInteger('row_number')->nullable()->after('collection_load_id');
            $table->string('period_key', 7)->nullable()->after('payment_date');
            $table->date('period_date')->nullable()->after('period_key');
            $table->string('seller_name', 120)->nullable()->after('channel');
            $table->json('source_payload')->nullable()->after('seller_name');

            $table->index(['period_key', 'payment_date']);
        });
    }

    public function down(): void
    {
        Schema::table('collection_details', function (Blueprint $table) {
            $table->dropIndex(['period_key', 'payment_date']);
            $table->dropColumn([
                'row_number',
                'period_key',
                'period_date',
                'seller_name',
                'source_payload',
            ]);
            $table->date('payment_date')->nullable(false)->change();
        });
    }
};
