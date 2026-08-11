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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('party_id')->constrained('parties')->onDelete('cascade');
            $table->enum('type', ['inward', 'outward', 'returnable_material', 'returnable_chalan']);
            $table->date('date');
            $table->string('bill_no')->nullable();
            $table->string('chalan_no')->nullable();
            $table->string('vehicle')->nullable();
            $table->string('reason')->nullable();
            $table->string('project_name')->nullable();
            $table->text('address')->nullable();
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->decimal('grand_total_with_tax', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->onDelete('cascade');
            $table->string('product_name');
            $table->string('brand')->nullable();
            $table->string('category')->nullable();
            $table->string('hsn_code')->nullable();
            $table->decimal('quantity', 10, 2);
            $table->string('type')->nullable(); // e.g. Nos./KG/Mtr.
            $table->decimal('rate', 15, 2);
            $table->decimal('tax_percent', 5, 2);
            $table->decimal('total', 15, 2);
            $table->decimal('total_with_tax', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
    }
};
