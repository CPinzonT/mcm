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
        if (! Schema::hasTable('collection_details')) {
            return;
        }

        Schema::table('collection_details', function (Blueprint $table) {
            $table->string('receipt_number', 100)->nullable()->after('document_number')->comment('Nro. de recibo');
            $table->string('reconciliation_id', 100)->nullable()->after('receipt_number')->comment('ID Reconciliación');
            $table->string('applied_document_type', 50)->nullable()->after('reconciliation_id')->comment('Tipo documento aplicado (normalizado)');
            $table->decimal('applied_amount', 18, 2)->nullable()->after('amount')->comment('Importe aplicado al documento');
            $table->decimal('pending_amount_after', 18, 2)->nullable()->after('applied_amount')->comment('Saldo pendiente tras aplicación');
            $table->string('regional', 100)->nullable()->after('notes');
            $table->string('channel', 100)->nullable()->after('regional');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('collection_details')) {
            return;
        }

        Schema::table('collection_details', function (Blueprint $table) {
            $table->dropColumn(['receipt_number', 'reconciliation_id', 'applied_document_type',
                'applied_amount', 'pending_amount_after', 'regional', 'channel']);
        });
    }
};
