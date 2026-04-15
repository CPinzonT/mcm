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
        Schema::create('collection_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_detail_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('portfolio_document_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('collection_load_id')->constrained()->cascadeOnDelete();
            $table->string('document_number', 80);
            $table->string('client_portfolio', 180)->nullable();
            $table->string('client_collection', 180)->nullable();
            $table->decimal('invoice_amount', 18, 2)->default(0);
            $table->decimal('applied_amount', 18, 2)->default(0);
            $table->decimal('portfolio_pending', 18, 2)->default(0);
            $table->decimal('difference', 18, 2)->default(0);
            $table->decimal('resulting_balance', 18, 2)->default(0);
            $table->string('status', 40);
            $table->string('period_portfolio', 7)->nullable();
            $table->string('period_collection', 7)->nullable();
            $table->integer('confidence_level')->default(100);
            $table->text('validation_detail')->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('reconciled_at');
            $table->timestamps();

            $table->index('collection_load_id');
            $table->index('document_number');
            $table->index('status');
            $table->index(['period_portfolio', 'period_collection'], 'cr_periods_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_reconciliations');
    }
};
