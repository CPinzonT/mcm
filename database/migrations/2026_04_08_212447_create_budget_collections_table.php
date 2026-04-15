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
        Schema::create('budget_collections', function (Blueprint $table) {
            $table->id();
            $table->date('period_date')->comment('Primer día del mes del presupuesto');
            $table->decimal('amount', 18, 2)->comment('Meta de recaudo del período');
            $table->string('uen', 100)->nullable();
            $table->string('regional', 100)->nullable();
            $table->string('channel', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('period_date');
            $table->unique(['period_date', 'uen', 'regional', 'channel'], 'budget_period_dimension_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_collections');
    }
};
