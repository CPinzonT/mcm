<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_rows', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->after('sales_load_id')->constrained('clients')->nullOnDelete();
            $table->string('client_code', 50)->nullable()->after('client_nit')->index();
            $table->string('invoice_type', 40)->nullable()->after('document_number');
            $table->decimal('cost_amount', 18, 2)->nullable()->after('sale_amount');
            $table->decimal('gross_profit', 18, 2)->nullable()->after('cost_amount');

            $table->index(['client_id', 'sale_date']);
            $table->index(['client_code', 'sale_date']);
        });
    }

    public function down(): void
    {
        Schema::table('sales_rows', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropIndex(['client_id', 'sale_date']);
            $table->dropIndex(['client_code', 'sale_date']);
            $table->dropColumn(['client_id', 'client_code', 'invoice_type', 'cost_amount', 'gross_profit']);
        });
    }
};
