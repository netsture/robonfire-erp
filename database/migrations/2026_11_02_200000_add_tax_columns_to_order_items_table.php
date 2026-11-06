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
        if (Schema::hasTable('purchase_items') && !Schema::hasColumn('purchase_items', 'tax_percent')) {
            Schema::table('purchase_items', function (Blueprint $table) {
                $table->decimal('tax_percent', 8, 2)->default(0)->after('subtotal');
                $table->decimal('tax_amount', 12, 2)->default(0)->after('tax_percent');
            });
        }

        if (Schema::hasTable('sale_items') && !Schema::hasColumn('sale_items', 'tax_percent')) {
            Schema::table('sale_items', function (Blueprint $table) {
                $table->decimal('tax_percent', 8, 2)->default(0)->after('subtotal');
                $table->decimal('tax_amount', 12, 2)->default(0)->after('tax_percent');
            });
        }

        if (Schema::hasTable('returnable_material_items') && !Schema::hasColumn('returnable_material_items', 'tax_percent')) {
            Schema::table('returnable_material_items', function (Blueprint $table) {
                $table->decimal('tax_percent', 8, 2)->default(0)->after('subtotal');
                $table->decimal('tax_amount', 12, 2)->default(0)->after('tax_percent');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('purchase_items') && Schema::hasColumn('purchase_items', 'tax_percent')) {
            Schema::table('purchase_items', function (Blueprint $table) {
                $table->dropColumn(['tax_percent', 'tax_amount']);
            });
        }

        if (Schema::hasTable('sale_items') && Schema::hasColumn('sale_items', 'tax_percent')) {
            Schema::table('sale_items', function (Blueprint $table) {
                $table->dropColumn(['tax_percent', 'tax_amount']);
            });
        }

        if (Schema::hasTable('returnable_material_items') && Schema::hasColumn('returnable_material_items', 'tax_percent')) {
            Schema::table('returnable_material_items', function (Blueprint $table) {
                $table->dropColumn(['tax_percent', 'tax_amount']);
            });
        }
    }
};

