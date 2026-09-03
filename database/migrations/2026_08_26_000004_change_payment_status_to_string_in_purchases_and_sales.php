<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Modify MySQL columns using raw SQL to support all status strings (pending, paid, unpaid, partial, due)
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `purchases` MODIFY COLUMN `payment_status` VARCHAR(50) NOT NULL DEFAULT 'pending'");
            DB::statement("ALTER TABLE `sales` MODIFY COLUMN `payment_status` VARCHAR(50) NOT NULL DEFAULT 'pending'");
        } else {
            Schema::table('purchases', function (Blueprint $table) {
                $table->string('payment_status', 50)->default('pending')->change();
            });
            Schema::table('sales', function (Blueprint $table) {
                $table->string('payment_status', 50)->default('pending')->change();
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `purchases` MODIFY COLUMN `payment_status` VARCHAR(50) NOT NULL DEFAULT 'pending'");
            DB::statement("ALTER TABLE `sales` MODIFY COLUMN `payment_status` VARCHAR(50) NOT NULL DEFAULT 'pending'");
        }
    }
};
