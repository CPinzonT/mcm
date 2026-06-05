<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            if (! Schema::hasColumn('clients', 'credit_limit')) {
                $table->decimal('credit_limit', 18, 2)->nullable()->after('uen')
                    ->comment('Cupo asignado al cliente (maestro SAP)');
            }
            if (! Schema::hasColumn('clients', 'payment_term_days')) {
                $table->unsignedSmallInteger('payment_term_days')->nullable()->after('credit_limit')
                    ->comment('Plazo contratado en días (vencimiento - emisión)');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            if (Schema::hasColumn('clients', 'payment_term_days')) {
                $table->dropColumn('payment_term_days');
            }
            if (Schema::hasColumn('clients', 'credit_limit')) {
                $table->dropColumn('credit_limit');
            }
        });
    }
};
