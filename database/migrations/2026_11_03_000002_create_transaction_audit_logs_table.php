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
        if (!Schema::hasTable('transaction_audit_logs')) {
            Schema::create('transaction_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('firm_id')->nullable()->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('module', 50)->index(); // 'purchase', 'sale', 'returnable'
                $table->string('action', 50)->index(); // 'create', 'update', 'delete'
                $table->unsignedBigInteger('record_id')->nullable()->index();
                $table->string('reference_no', 100)->nullable()->index();
                $table->string('project_name', 255)->nullable();
                $table->string('party_name', 255)->nullable();
                $table->string('party_type', 50)->nullable();
                $table->integer('items_count')->default(0);
                $table->text('products_summary')->nullable();
                $table->decimal('grand_total', 15, 2)->default(0);
                $table->decimal('paid_amount', 15, 2)->default(0);
                $table->string('payment_status', 50)->nullable();
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
        Schema::dropIfExists('transaction_audit_logs');
    }
};

