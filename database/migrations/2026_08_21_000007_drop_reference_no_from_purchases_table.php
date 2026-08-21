<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('purchases', 'reference_no')) {
            Schema::table('purchases', function (Blueprint $table) {
                // Drop unique index on firm_id and reference_no
                $table->dropUnique(['firm_id', 'reference_no']);
                $table->dropColumn('reference_no');
            });
        }

        // Ensure unique index on firm_id and project_name
        Schema::table('purchases', function (Blueprint $table) {
            $table->unique(['firm_id', 'project_name']);
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropUnique(['firm_id', 'project_name']);
            $table->string('reference_no')->nullable();
            $table->unique(['firm_id', 'reference_no']);
        });
    }
};
