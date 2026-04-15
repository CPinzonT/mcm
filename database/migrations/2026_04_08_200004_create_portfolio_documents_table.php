<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolio_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('portfolio_load_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('advisor_id')->nullable()->constrained()->nullOnDelete();
            $table->string('document_number', 100)->comment('Número de factura / documento');
            $table->string('document_type', 50)->default('FACTURA');
            $table->date('issue_date')->comment('Fecha de emisión');
            $table->date('due_date')->comment('Fecha de vencimiento');
            $table->decimal('original_amount', 18, 2);
            $table->decimal('pending_amount', 18, 2)->comment('Saldo pendiente');
            $table->decimal('collected_amount', 18, 2)->default(0);
            $table->integer('days_overdue')->default(0)->comment('Días de mora al corte');
            $table->enum('risk_level', ['normal', 'low', 'medium', 'high', 'critical'])->default('normal')
                ->comment('Riesgo: normal=0-30, low=31-60, medium=61-90, high=91-180, critical=181+');
            $table->enum('status', ['active', 'partial', 'paid', 'written_off', 'in_process'])->default('active');
            $table->string('currency', 3)->default('COP');
            $table->date('period_date')->comment('Fecha de corte de la cartera');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'status']);
            $table->index(['due_date', 'risk_level']);
            $table->index(['advisor_id', 'period_date']);
            $table->index('period_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_documents');
    }
};
