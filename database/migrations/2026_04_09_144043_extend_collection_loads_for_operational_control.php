<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('collection_loads', function (Blueprint $table) {
            $table->string('status', 30)->default('pending')->change();
            $table->date('period_date')->nullable()->change();
            $table->string('file_hash', 64)->nullable()->after('path')->index();
            $table->unsignedInteger('valid_rows')->default(0)->after('processed_rows');
            $table->unsignedInteger('empty_rows')->default(0)->after('error_rows');
            $table->unsignedInteger('duplicate_rows')->default(0)->after('empty_rows');
            $table->unsignedInteger('detail_count')->default(0)->after('duplicate_rows');
            $table->string('period_key', 7)->nullable()->after('period_date')->index();
            $table->unsignedInteger('version')->default(1)->after('period_key');
            $table->boolean('is_active')->default(false)->after('version')->index();
            $table->json('validation_summary')->nullable()->after('error_log');
            $table->timestamp('cancelled_at')->nullable()->after('processed_at');
            $table->foreignId('cancelled_by')->nullable()->after('uploaded_by')->constrained('users')->nullOnDelete();
            $table->text('cancellation_reason')->nullable()->after('cancelled_at');
        });
    }

    public function down(): void
    {
        Schema::table('collection_loads', function (Blueprint $table) {
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn([
                'file_hash',
                'valid_rows',
                'empty_rows',
                'duplicate_rows',
                'detail_count',
                'period_key',
                'version',
                'is_active',
                'validation_summary',
                'cancelled_at',
                'cancelled_by',
                'cancellation_reason',
            ]);
            $table->date('period_date')->nullable(false)->change();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending')->change();
        });
    }
};
