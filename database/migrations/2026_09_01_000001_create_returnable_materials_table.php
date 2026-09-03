<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('returnable_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firm_id')->constrained()->onDelete('cascade');
            $table->string('return_number');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('return_date');
            $table->string('project_name');
            $table->string('vehicle_number')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0.00);
            $table->decimal('tax_amount', 12, 2)->default(0.00);
            $table->decimal('discount_amount', 12, 2)->default(0.00);
            $table->decimal('shipping_cost', 12, 2)->default(0.00);
            $table->decimal('grand_total', 12, 2)->default(0.00);
            $table->string('status', 50)->default('returned');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['firm_id', 'return_number']);
        });

        Schema::create('returnable_material_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('returnable_material_id')->constrained('returnable_materials')->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->decimal('unit_price', 12, 2)->default(0.00);
            $table->integer('quantity');
            $table->decimal('subtotal', 12, 2)->default(0.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('returnable_material_items');
        Schema::dropIfExists('returnable_materials');
    }
};
