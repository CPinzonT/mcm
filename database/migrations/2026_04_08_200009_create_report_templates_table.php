<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['base', 'client'])->default('base');
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branding_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->json('visible_columns')->nullable()->comment('Columnas visibles y su orden');
            $table->json('default_filters')->nullable()->comment('Filtros predefinidos');
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->boolean('show_logo')->default(true);
            $table->boolean('show_header')->default(true);
            $table->boolean('show_footer')->default(true);
            $table->boolean('show_page_numbers')->default(true);
            $table->boolean('active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('report_template_columns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_template_id')->constrained()->cascadeOnDelete();
            $table->string('field_key', 100)->comment('Clave del campo, e.g. document_number');
            $table->string('label', 100)->comment('Etiqueta visible en el reporte');
            $table->string('format', 50)->nullable()->comment('currency, date, percentage, text');
            $table->integer('order')->default(0);
            $table->boolean('visible')->default(true);
            $table->string('width', 20)->nullable();
            $table->string('align', 10)->default('left');
            $table->timestamps();

            $table->index(['report_template_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_template_columns');
        Schema::dropIfExists('report_templates');
    }
};
