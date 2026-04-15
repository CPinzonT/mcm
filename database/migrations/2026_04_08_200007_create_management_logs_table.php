<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('management_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('portfolio_document_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('advisor_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->enum('type', ['call', 'email', 'visit', 'agreement', 'legal', 'other'])->default('call');
            $table->string('subject');
            $table->text('description');
            $table->date('contact_date');
            $table->enum('result', ['no_contact', 'promise_to_pay', 'partial_payment', 'refused', 'arrangement', 'other'])
                ->nullable();
            $table->date('follow_up_date')->nullable();
            $table->decimal('promised_amount', 18, 2)->nullable();
            $table->date('promised_date')->nullable();
            $table->enum('status', ['open', 'closed', 'pending'])->default('open');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'contact_date']);
            $table->index('follow_up_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('management_logs');
    }
};
