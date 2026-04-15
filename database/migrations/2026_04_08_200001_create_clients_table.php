<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('Código único del cliente');
            $table->string('name');
            $table->string('document_type', 10)->default('NIT')->comment('NIT, CC, CE');
            $table->string('document_number', 30)->unique();
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('region', 100)->nullable()->comment('Regional');
            $table->string('channel', 100)->nullable()->comment('Canal comercial');
            $table->string('uen', 100)->nullable()->comment('Unidad de Negocio');
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone', 30)->nullable();
            $table->boolean('active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
