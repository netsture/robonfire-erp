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
        // Deduplicate Projects with identical firm_id and project_name
        $duplicateProjects = DB::table('projects')
            ->select('firm_id', 'project_name', DB::raw('COUNT(*) as count'))
            ->groupBy('firm_id', 'project_name')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicateProjects as $dup) {
            $records = DB::table('projects')
                ->where('firm_id', $dup->firm_id)
                ->where('project_name', $dup->project_name)
                ->orderBy('id')
                ->get();

            foreach ($records->slice(1) as $record) {
                DB::table('projects')
                    ->where('id', $record->id)
                    ->update(['project_name' => $record->project_name . ' (' . $record->id . ')']);
            }
        }

        Schema::table('projects', function (Blueprint $table) {
            $table->unique(['firm_id', 'project_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropUnique(['firm_id', 'project_name']);
        });
    }
};
