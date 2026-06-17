<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_loads', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 32)->unique();
            $table->string('original_filename')->nullable();
            $table->string('source_url')->nullable();
            $table->string('disk', 20)->default('local');
            $table->string('path');
            $table->string('file_hash', 64)->nullable();
            $table->string('period_key', 7)->nullable()->index();
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('valid_rows')->default(0);
            $table->unsignedInteger('processed_rows')->default(0);
            $table->unsignedInteger('error_rows')->default(0);
            $table->decimal('total_amount', 18, 2)->default(0);
            $table->string('status', 30)->default('pending');
            $table->text('notes')->nullable();
            $table->json('validation_summary')->nullable();
            $table->json('error_log')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('sales_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_load_id')->constrained('sales_loads')->cascadeOnDelete();
            $table->unsignedInteger('row_number');
            $table->date('sale_date')->nullable()->index();
            $table->string('document_number', 80)->nullable()->index();
            $table->string('client_name')->nullable();
            $table->string('client_nit', 40)->nullable();
            $table->string('product_code', 80)->nullable();
            $table->string('product_name')->nullable();
            $table->decimal('quantity', 18, 4)->nullable();
            $table->decimal('sale_amount', 18, 2)->default(0);
            $table->string('seller_name', 150)->nullable();
            $table->string('uen', 80)->nullable();
            $table->string('regional', 80)->nullable();
            $table->string('channel', 120)->nullable();
            $table->timestamps();

            $table->index(['sales_load_id', 'row_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_rows');
        Schema::dropIfExists('sales_loads');
    }
};
