<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('budget_rows')) {
            return;
        }

        Schema::table('budget_rows', function (Blueprint $table) {
            if (! Schema::hasColumn('budget_rows', 'client_name')) {
                $table->string('client_name', 255)->nullable()->after('row_number');
            }
            if (! Schema::hasColumn('budget_rows', 'regional')) {
                $table->string('regional', 80)->nullable()->after('row_number')->index();
            }
            if (! Schema::hasColumn('budget_rows', 'channel')) {
                $table->string('channel', 120)->nullable()->after('row_number')->index();
            }
            if (! Schema::hasColumn('budget_rows', 'seller_name')) {
                $table->string('seller_name', 150)->nullable()->after('row_number')->index();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('budget_rows')) {
            return;
        }

        $columns = array_filter(
            ['client_name', 'regional', 'channel', 'seller_name'],
            static fn (string $col) => Schema::hasColumn('budget_rows', $col),
        );

        if ($columns === []) {
            return;
        }

        Schema::table('budget_rows', function (Blueprint $table) use ($columns) {
            $table->dropColumn($columns);
        });
    }
};
