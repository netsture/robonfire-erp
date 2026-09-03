<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop existing (firm_id, name) unique index on products
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['firm_id', 'name']);
        });

        // 2. Deduplicate any records with matching (firm_id, name, category_id, brand_id)
        $duplicates = DB::table('products')
            ->select('firm_id', 'name', 'category_id', 'brand_id', DB::raw('COUNT(*) as count'))
            ->groupBy('firm_id', 'name', 'category_id', 'brand_id')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicates as $dup) {
            $records = DB::table('products')
                ->where('firm_id', $dup->firm_id)
                ->where('name', $dup->name)
                ->where('category_id', $dup->category_id)
                ->where('brand_id', $dup->brand_id)
                ->orderBy('id')
                ->get();

            foreach ($records->slice(1) as $record) {
                DB::table('products')
                    ->where('id', $record->id)
                    ->update(['name' => $record->name . ' (' . $record->id . ')']);
            }
        }

        // 3. Add composite unique index on (firm_id, name, category_id, brand_id)
        Schema::table('products', function (Blueprint $table) {
            $table->unique(['firm_id', 'name', 'category_id', 'brand_id'], 'products_firm_name_cat_brand_unique');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_firm_name_cat_brand_unique');
            $table->unique(['firm_id', 'name']);
        });
    }
};
