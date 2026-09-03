<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Deduplicate existing duplicate company_names within the same firm before adding unique constraint
        foreach (['customers', 'suppliers'] as $tableName) {
            $duplicates = DB::table($tableName)
                ->select('firm_id', 'company_name', DB::raw('COUNT(*) as count'))
                ->groupBy('firm_id', 'company_name')
                ->having('count', '>', 1)
                ->get();

            foreach ($duplicates as $duplicate) {
                $records = DB::table($tableName)
                    ->where('firm_id', $duplicate->firm_id)
                    ->where('company_name', $duplicate->company_name)
                    ->orderBy('id', 'asc')
                    ->get();

                // Skip the first record, append suffix to remaining duplicates
                $records->slice(1)->each(function ($record) use ($tableName) {
                    DB::table($tableName)
                        ->where('id', $record->id)
                        ->update(['company_name' => $record->company_name . ' (Duplicate #' . $record->id . ')']);
                });
            }
        }

        try {
            Schema::table('customers', function (Blueprint $table) {
                $table->unique(['firm_id', 'company_name']);
            });
        } catch (\Throwable $e) {
            // Index may already exist from partial previous run
        }

        try {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->unique(['firm_id', 'company_name']);
            });
        } catch (\Throwable $e) {
            // Index may already exist
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropUnique(['firm_id', 'company_name']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique(['firm_id', 'company_name']);
        });
    }
};
