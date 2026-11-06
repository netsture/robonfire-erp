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
        if (!Schema::hasTable('product_logs')) {
            Schema::create('product_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('firm_id')->nullable()->index();
                $table->unsignedBigInteger('product_id')->nullable()->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('action', 50)->index(); // 'create', 'update', 'delete'
                $table->string('product_name', 255);
                $table->string('product_identifier', 50)->nullable()->index();
                $table->string('hsn_code', 50)->nullable();
                $table->string('category_name', 255)->nullable();
                $table->string('brand_name', 255)->nullable();
                $table->text('description')->nullable();
                $table->json('changes')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_logs');
    }
};

