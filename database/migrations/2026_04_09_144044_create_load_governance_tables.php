<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('period_controls', function (Blueprint $table) {
            $table->id();
            $table->string('period_key', 7)->unique();
            $table->date('period_date')->unique();
            $table->foreignId('portfolio_load_id')->nullable()->constrained('portfolio_loads')->nullOnDelete();
            $table->unsignedInteger('portfolio_version')->default(0);
            $table->timestamp('portfolio_loaded_at')->nullable();
            $table->foreignId('collection_load_id')->nullable()->constrained('collection_loads')->nullOnDelete();
            $table->unsignedInteger('collection_version')->default(0);
            $table->timestamp('collection_loaded_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('portfolio_load_errors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_load_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('row_number')->nullable();
            $table->string('field', 100)->nullable();
            $table->string('error_code', 100)->nullable();
            $table->text('message');
            $table->json('row_payload')->nullable();
            $table->timestamps();

            $table->index(['portfolio_load_id', 'row_number']);
        });

        Schema::create('collection_load_errors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_load_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('row_number')->nullable();
            $table->string('field', 100)->nullable();
            $table->string('error_code', 100)->nullable();
            $table->text('message');
            $table->json('row_payload')->nullable();
            $table->timestamps();

            $table->index(['collection_load_id', 'row_number']);
        });

        Schema::create('load_audits', function (Blueprint $table) {
            $table->id();
            $table->morphs('auditable');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('module', 50);
            $table->string('action', 100);
            $table->text('description');
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['module', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('load_audits');
        Schema::dropIfExists('collection_load_errors');
        Schema::dropIfExists('portfolio_load_errors');
        Schema::dropIfExists('period_controls');
    }
};
