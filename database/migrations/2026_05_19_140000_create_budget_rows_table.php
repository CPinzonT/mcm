<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_load_id')->constrained('budget_loads')->cascadeOnDelete();
            $table->string('period_key', 7)->index();
            $table->unsignedInteger('row_number')->nullable();
            $table->string('client_name', 255)->nullable();
            $table->string('regional', 80)->nullable()->index();
            $table->string('channel', 120)->nullable()->index();
            $table->string('seller_name', 150)->nullable()->index();
            $table->string('transaction_type', 80)->nullable()->index();
            $table->string('document_number', 100)->nullable()->index();
            $table->date('invoice_date')->nullable()->index();
            $table->date('due_date')->nullable();
            $table->integer('days_overdue')->nullable();
            $table->decimal('initial_amount', 18, 2)->nullable();
            $table->decimal('balance_due', 18, 2)->nullable();
            $table->decimal('aging_1_90', 18, 2)->nullable();
            $table->decimal('aging_over_90', 18, 2)->nullable();
            $table->decimal('not_due_amount', 18, 2)->nullable();
            $table->decimal('rotation', 18, 4)->nullable();
            $table->decimal('budget_amount', 18, 2)->nullable();
            $table->decimal('collection_amount', 18, 2)->nullable();
            $table->string('category', 150)->nullable()->index();
            $table->date('application_date')->nullable()->index();
            $table->timestamps();

            $table->index(['budget_load_id', 'period_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_rows');
    }
};
