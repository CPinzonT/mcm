<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branding_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_default')->default(false);
            $table->string('company_name');
            $table->string('logo_path')->nullable();
            $table->string('primary_color', 7)->default('#1e3a5f');
            $table->string('secondary_color', 7)->default('#2563eb');
            $table->string('accent_color', 7)->default('#f59e0b');
            $table->string('font_family')->default('Inter');
            $table->text('header_text')->nullable();
            $table->text('footer_text')->nullable();
            $table->string('address')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branding_profiles');
    }
};
