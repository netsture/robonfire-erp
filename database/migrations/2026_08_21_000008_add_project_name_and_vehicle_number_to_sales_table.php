<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'project_name')) {
                $table->string('project_name')->nullable()->after('invoice_number');
            }
            if (!Schema::hasColumn('sales', 'vehicle_number')) {
                $table->string('vehicle_number')->nullable()->after('project_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'project_name')) {
                $table->dropColumn('project_name');
            }
            if (Schema::hasColumn('sales', 'vehicle_number')) {
                $table->dropColumn('vehicle_number');
            }
        });
    }
};
