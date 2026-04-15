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
        Schema::create('client_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->dateTime('event_date');
            $table->string('event_type', 30);
            $table->decimal('amount', 18, 2)->default(0);
            $table->text('description');
            $table->foreignId('portfolio_document_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('portfolio_load_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('collection_detail_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['client_id', 'event_date']);
            $table->index('event_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_histories');
    }
};
