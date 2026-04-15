<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portfolio_documents', function (Blueprint $table) {
            $table->string('status', 30)->default('active')->change();
            $table->string('account', 50)->nullable()->after('advisor_id');
            $table->string('logical_key', 191)->nullable()->after('account');
            $table->string('client_reference', 100)->nullable()->after('document_number');
            $table->date('activation_date')->nullable()->after('issue_date');
            $table->json('aging_buckets')->nullable()->after('days_overdue');
            $table->timestamp('closed_at')->nullable()->after('notes');
            $table->text('closure_reason')->nullable()->after('closed_at');

            $table->index(['logical_key', 'period_date']);
            $table->index(['portfolio_load_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('portfolio_documents', function (Blueprint $table) {
            $table->dropIndex(['logical_key', 'period_date']);
            $table->dropIndex(['portfolio_load_id', 'status']);
            $table->dropColumn([
                'account',
                'logical_key',
                'client_reference',
                'activation_date',
                'aging_buckets',
                'closed_at',
                'closure_reason',
            ]);
            $table->enum('status', ['active', 'partial', 'paid', 'written_off', 'in_process'])->default('active')->change();
        });
    }
};
