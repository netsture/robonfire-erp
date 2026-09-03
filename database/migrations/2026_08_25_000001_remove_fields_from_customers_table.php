<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'name') && !Schema::hasColumn('customers', 'company_name')) {
                $table->renameColumn('name', 'company_name');
            }
            if (Schema::hasColumn('customers', 'tax_number') && !Schema::hasColumn('customers', 'gst_number')) {
                $table->renameColumn('tax_number', 'gst_number');
            }
        });

        Schema::table('suppliers', function (Blueprint $table) {
            if (Schema::hasColumn('suppliers', 'name') && !Schema::hasColumn('suppliers', 'company_name')) {
                $table->renameColumn('name', 'company_name');
            }
            if (Schema::hasColumn('suppliers', 'tax_number') && !Schema::hasColumn('suppliers', 'gst_number')) {
                $table->renameColumn('tax_number', 'gst_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            if (Schema::hasColumn('suppliers', 'company_name')) {
                $table->renameColumn('company_name', 'name');
            }
            if (Schema::hasColumn('suppliers', 'gst_number')) {
                $table->renameColumn('gst_number', 'tax_number');
            }
        });

        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'company_name')) {
                $table->renameColumn('company_name', 'name');
            }
            if (Schema::hasColumn('customers', 'gst_number')) {
                $table->renameColumn('gst_number', 'tax_number');
            }
        });
    }
};
