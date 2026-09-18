<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('products', 'product_identifier')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('product_identifier', 50)->nullable()->unique()->after('name');
            });
        }

        // Backfill existing products with 5-digit numeric codes
        $products = DB::table('products')->get();
        foreach ($products as $p) {
            if (empty($p->product_identifier) || !is_numeric($p->product_identifier) || strlen($p->product_identifier) !== 5) {
                do {
                    $code = (string) mt_rand(10000, 99999);
                } while (DB::table('products')->where('product_identifier', $code)->exists());

                DB::table('products')->where('id', $p->id)->update(['product_identifier' => $code]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'product_identifier')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropUnique(['product_identifier']);
                $table->dropColumn('product_identifier');
            });
        }
    }
};
