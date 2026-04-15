<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolio_rotation_snapshots', function (Blueprint $table) {
            $table->id();
            $table->date('period_date')->comment('Fecha de corte del snapshot');
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete()
                ->comment('NULL = snapshot global');
            $table->decimal('total_portfolio', 18, 2)->default(0);
            $table->decimal('total_overdue', 18, 2)->default(0);
            $table->decimal('total_collected_period', 18, 2)->default(0);
            $table->integer('total_documents')->default(0);
            $table->integer('overdue_documents')->default(0);
            $table->decimal('dso', 8, 2)->nullable()
                ->comment('Days Sales Outstanding: (cartera / ventas periodo) * dias');
            $table->decimal('rotation_index', 8, 4)->nullable()
                ->comment('Índice de rotación = cobrado / cartera inicial');
            $table->decimal('overdue_rate', 8, 4)->nullable()
                ->comment('Tasa de mora = vencido / total cartera');
            $table->json('risk_distribution')->nullable()
                ->comment('Distribución por riesgo: {normal, low, medium, high, critical}');
            $table->string('formula_version', 20)->default('v1')
                ->comment('Versión de fórmula usada. PENDIENTE validar con negocio.');
            $table->timestamps();

            $table->unique(['period_date', 'client_id']);
            $table->index('period_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_rotation_snapshots');
    }
};
