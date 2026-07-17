<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_rows', function (Blueprint $table): void {
            $table->index(['client_nit', 'sale_date']);
        });
    }

    public function down(): void
    {
        Schema::table('sales_rows', function (Blueprint $table): void {
            $table->dropIndex(['client_nit', 'sale_date']);
        });
    }
};
