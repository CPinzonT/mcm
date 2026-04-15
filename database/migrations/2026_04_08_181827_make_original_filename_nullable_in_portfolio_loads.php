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
        if (! Schema::hasTable('portfolio_loads') || ! Schema::hasColumn('portfolio_loads', 'original_filename')) {
            return;
        }

        Schema::table('portfolio_loads', function (Blueprint $table) {
            $table->string('original_filename')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('portfolio_loads') || ! Schema::hasColumn('portfolio_loads', 'original_filename')) {
            return;
        }

        Schema::table('portfolio_loads', function (Blueprint $table) {
            $table->string('original_filename')->nullable(false)->change();
        });
    }
};
