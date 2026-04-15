<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_document_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 50)->unique();
            $table->text('description')->nullable();
            $table->boolean('required')->default(false)->comment('Si es obligatorio para el castigo');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('castigo_cases', function (Blueprint $table) {
            $table->id();
            $table->string('case_number')->unique();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->decimal('total_amount', 18, 2)->comment('Monto total del castigo');
            $table->enum('status', ['draft', 'in_review', 'approved', 'rejected', 'submitted_dian', 'closed'])
                ->default('draft');
            $table->text('description')->nullable();
            $table->date('case_date');
            $table->date('submitted_at')->nullable()->comment('Fecha de radicación ante DIAN');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'status']);
        });

        Schema::create('castigo_case_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('castigo_case_id')->constrained()->cascadeOnDelete();
            $table->foreignId('portfolio_document_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 18, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('castigo_case_documents');
        Schema::dropIfExists('castigo_cases');
        Schema::dropIfExists('support_document_types');
    }
};
