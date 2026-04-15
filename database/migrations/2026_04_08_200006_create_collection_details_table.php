<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collection_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_load_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('portfolio_document_id')->nullable()->constrained()->nullOnDelete();
            $table->string('document_number', 100)->nullable();
            $table->string('receipt_number', 100)->nullable()->comment('Nro. de recibo');
            $table->string('reconciliation_id', 100)->nullable()->comment('ID Reconciliación');
            $table->string('applied_document_type', 50)->nullable()->comment('Tipo documento aplicado (normalizado)');
            $table->decimal('amount', 18, 2);
            $table->decimal('applied_amount', 18, 2)->nullable()->comment('Importe aplicado al documento');
            $table->decimal('pending_amount_after', 18, 2)->nullable()->comment('Saldo pendiente tras aplicación');
            $table->date('payment_date');
            $table->string('payment_method', 50)->nullable()->comment('Cheque, transferencia, efectivo');
            $table->string('bank', 100)->nullable();
            $table->string('reference', 100)->nullable();
            $table->text('notes')->nullable();
            $table->string('regional', 100)->nullable();
            $table->string('channel', 100)->nullable();
            $table->timestamps();

            $table->index(['client_id', 'payment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_details');
    }
};
