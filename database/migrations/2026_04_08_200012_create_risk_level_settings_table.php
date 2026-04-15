<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risk_level_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('level', ['normal', 'low', 'medium', 'high', 'critical']);
            $table->string('label', 50);
            $table->integer('days_min')->comment('Días de mora mínimo (inclusive)');
            $table->integer('days_max')->nullable()->comment('Días de mora máximo (null = sin límite)');
            $table->string('color', 7)->comment('Color hex para UI y reportes');
            $table->string('badge_color', 30)->default('gray')->comment('Color Filament badge');
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risk_level_settings');
    }
};
