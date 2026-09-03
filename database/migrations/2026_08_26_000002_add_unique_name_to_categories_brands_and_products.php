<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Deduplicate Categories
        $duplicateCategories = DB::table('categories')
            ->select('firm_id', 'name', DB::raw('COUNT(*) as count'))
            ->groupBy('firm_id', 'name')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicateCategories as $dup) {
            $records = DB::table('categories')
                ->where('firm_id', $dup->firm_id)
                ->where('name', $dup->name)
                ->orderBy('id')
                ->get();

            // Skip first record, update subsequent records
            foreach ($records->slice(1) as $record) {
                DB::table('categories')
                    ->where('id', $record->id)
                    ->update(['name' => $record->name . ' (' . $record->id . ')']);
            }
        }

        // 2. Deduplicate Brands
        $duplicateBrands = DB::table('brands')
            ->select('firm_id', 'name', DB::raw('COUNT(*) as count'))
            ->groupBy('firm_id', 'name')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicateBrands as $dup) {
            $records = DB::table('brands')
                ->where('firm_id', $dup->firm_id)
                ->where('name', $dup->name)
                ->orderBy('id')
                ->get();

            foreach ($records->slice(1) as $record) {
                DB::table('brands')
                    ->where('id', $record->id)
                    ->update(['name' => $record->name . ' (' . $record->id . ')']);
            }
        }

        // 3. Deduplicate Products
        $duplicateProducts = DB::table('products')
            ->select('firm_id', 'name', DB::raw('COUNT(*) as count'))
            ->groupBy('firm_id', 'name')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicateProducts as $dup) {
            $records = DB::table('products')
                ->where('firm_id', $dup->firm_id)
                ->where('name', $dup->name)
                ->orderBy('id')
                ->get();

            foreach ($records->slice(1) as $record) {
                DB::table('products')
                    ->where('id', $record->id)
                    ->update(['name' => $record->name . ' (' . $record->id . ')']);
            }
        }

        // Apply Unique Composite Indexes
        Schema::table('categories', function (Blueprint $table) {
            $table->unique(['firm_id', 'name']);
        });

        Schema::table('brands', function (Blueprint $table) {
            $table->unique(['firm_id', 'name']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->unique(['firm_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['firm_id', 'name']);
        });

        Schema::table('brands', function (Blueprint $table) {
            $table->dropUnique(['firm_id', 'name']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['firm_id', 'name']);
        });
    }
};
