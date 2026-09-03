<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('returnable_materials', function (Blueprint $table) {
            $table->renameColumn('vehicle_number', 'return_reason');
        });
    }

    public function down(): void
    {
        Schema::table('returnable_materials', function (Blueprint $table) {
            $table->renameColumn('return_reason', 'vehicle_number');
        });
    }
};
