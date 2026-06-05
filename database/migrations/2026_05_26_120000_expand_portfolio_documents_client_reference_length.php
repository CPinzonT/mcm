<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portfolio_documents', function (Blueprint $table) {
            $table->string('client_reference', 250)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('portfolio_documents', function (Blueprint $table) {
            $table->string('client_reference', 100)->nullable()->change();
        });
    }
};
