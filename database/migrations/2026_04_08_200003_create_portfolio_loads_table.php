<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolio_loads', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique()->comment('Referencia única de la carga');
            $table->string('original_filename')->nullable();
            $table->string('disk')->default('local');
            $table->string('path');
            $table->integer('total_rows')->default(0);
            $table->integer('processed_rows')->default(0);
            $table->integer('error_rows')->default(0);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('notes')->nullable();
            $table->json('error_log')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('uploaded_by')->constrained('users');
            $table->date('period_date')->comment('Fecha de corte de la cartera cargada');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_loads');
    }
};
