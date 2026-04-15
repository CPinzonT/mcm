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
        Schema::table('collection_details', function (Blueprint $table) {
            $table->string('document_type', 50)->nullable()->after('document_number')
                ->comment('Tipo de documento del recaudo (del archivo fuente)');
        });
    }

    public function down(): void
    {
        Schema::table('collection_details', function (Blueprint $table) {
            $table->dropColumn('document_type');
        });
    }
};
