<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('purchases', 'project_name')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->string('project_name')->nullable()->after('reference_no');
            });

            // Backfill existing rows with reference_no if null
            DB::statement("UPDATE purchases SET project_name = reference_no WHERE project_name IS NULL OR project_name = ''");
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('purchases', 'project_name')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->dropColumn('project_name');
            });
        }
    }
};
