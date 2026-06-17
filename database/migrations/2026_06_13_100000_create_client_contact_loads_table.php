<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_contact_loads', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 32)->unique();
            $table->string('original_filename')->nullable();
            $table->string('disk', 20)->default('local');
            $table->string('path');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('updated_rows')->default(0);
            $table->unsignedInteger('not_found_rows')->default(0);
            $table->unsignedInteger('skipped_rows')->default(0);
            $table->unsignedInteger('error_rows')->default(0);
            $table->string('status', 30)->default('pending');
            $table->json('error_log')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_contact_loads');
    }
};
