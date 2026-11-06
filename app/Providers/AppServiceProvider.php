<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
        Schema::defaultStringLength(191);

        try {
            if (Schema::hasTable('purchase_items') && !Schema::hasColumn('purchase_items', 'tax_percent')) {
                Schema::table('purchase_items', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->decimal('tax_percent', 8, 2)->default(0)->after('subtotal');
                    $table->decimal('tax_amount', 12, 2)->default(0)->after('tax_percent');
                });

                $purchases = \App\Models\Purchase::with('items.product')->get();
                foreach ($purchases as $p) {
                    $items = $p->items;
                    $itemCount = $items->count();
                    if ($itemCount === 1 && (float)$p->subtotal > 0) {
                        $taxPct = round(((float)$p->tax_amount / (float)$p->subtotal) * 100, 2);
                        $items->first()->update([
                            'tax_percent' => $taxPct,
                            'tax_amount'  => (float)$p->tax_amount,
                        ]);
                    } elseif ($itemCount > 1) {
                        foreach ($items as $it) {
                            $prodTax = $it->product ? (float)$it->product->tax_percent : 0;
                            $itTax = round(((float)$it->subtotal * $prodTax) / 100, 2);
                            $it->update([
                                'tax_percent' => $prodTax,
                                'tax_amount'  => $itTax,
                            ]);
                        }
                    }
                }
            }

            if (Schema::hasTable('sale_items') && !Schema::hasColumn('sale_items', 'tax_percent')) {
                Schema::table('sale_items', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->decimal('tax_percent', 8, 2)->default(0)->after('subtotal');
                    $table->decimal('tax_amount', 12, 2)->default(0)->after('tax_percent');
                });

                $sales = \App\Models\Sale::with('items.product')->get();
                foreach ($sales as $s) {
                    $items = $s->items;
                    $itemCount = $items->count();
                    if ($itemCount === 1 && (float)$s->subtotal > 0) {
                        $taxPct = round(((float)$s->tax_amount / (float)$s->subtotal) * 100, 2);
                        $items->first()->update([
                            'tax_percent' => $taxPct,
                            'tax_amount'  => (float)$s->tax_amount,
                        ]);
                    } elseif ($itemCount > 1) {
                        foreach ($items as $it) {
                            $prodTax = $it->product ? (float)$it->product->tax_percent : 0;
                            $itTax = round(((float)$it->subtotal * $prodTax) / 100, 2);
                            $it->update([
                                'tax_percent' => $prodTax,
                                'tax_amount'  => $itTax,
                            ]);
                        }
                    }
                }
            }

            if (Schema::hasTable('returnable_material_items') && !Schema::hasColumn('returnable_material_items', 'tax_percent')) {
                Schema::table('returnable_material_items', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->decimal('tax_percent', 8, 2)->default(0)->after('subtotal');
                    $table->decimal('tax_amount', 12, 2)->default(0)->after('tax_percent');
                });

                $returns = \App\Models\ReturnableMaterial::with('items.product')->get();
                foreach ($returns as $r) {
                    $items = $r->items;
                    $itemCount = $items->count();
                    if ($itemCount === 1 && (float)$r->subtotal > 0) {
                        $taxPct = round(((float)$r->tax_amount / (float)$r->subtotal) * 100, 2);
                        $items->first()->update([
                            'tax_percent' => $taxPct,
                            'tax_amount'  => (float)$r->tax_amount,
                        ]);
                    } elseif ($itemCount > 1) {
                        foreach ($items as $it) {
                            $prodTax = $it->product ? (float)$it->product->tax_percent : 0;
                            $itTax = round(((float)$it->subtotal * $prodTax) / 100, 2);
                            $it->update([
                                'tax_percent' => $prodTax,
                                'tax_amount'  => $itTax,
                            ]);
                        }
                    }
                }
            }

            // Sync unpopulated tax columns for sales with positive tax
            if (Schema::hasTable('sale_items') && Schema::hasColumn('sale_items', 'tax_percent')) {
                $unfilledSales = \App\Models\Sale::where('tax_amount', '>', 0)
                    ->whereHas('items', function ($q) {
                        $q->where(function ($sub) {
                            $sub->whereNull('tax_percent')->orWhere('tax_percent', 0);
                        })->where(function ($sub) {
                            $sub->whereNull('tax_amount')->orWhere('tax_amount', 0);
                        });
                    })
                    ->with('items.product')
                    ->get();

                foreach ($unfilledSales as $s) {
                    $items = $s->items;
                    $itemCount = $items->count();
                    if ($itemCount === 1 && (float)$s->subtotal > 0) {
                        $taxPct = round(((float)$s->tax_amount / (float)$s->subtotal) * 100, 2);
                        $items->first()->update([
                            'tax_percent' => $taxPct,
                            'tax_amount'  => (float)$s->tax_amount,
                        ]);
                    } elseif ($itemCount > 1) {
                        foreach ($items as $it) {
                            if ((float)($it->tax_percent ?? 0) == 0 && (float)($it->tax_amount ?? 0) == 0) {
                                $prodTax = $it->product ? (float)$it->product->tax_percent : 0;
                                if ($prodTax == 0 && (float)$s->subtotal > 0) {
                                    $prodTax = round(((float)$s->tax_amount / (float)$s->subtotal) * 100, 2);
                                }
                                $itTax = round(((float)$it->subtotal * $prodTax) / 100, 2);
                                $it->update([
                                    'tax_percent' => $prodTax,
                                    'tax_amount'  => $itTax,
                                ]);
                            }
                        }
                    }
                }
            }

            // Ensure product_logs table exists
            if (!Schema::hasTable('product_logs')) {
                Schema::create('product_logs', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('firm_id')->nullable()->index();
                    $table->unsignedBigInteger('product_id')->nullable()->index();
                    $table->unsignedBigInteger('user_id')->nullable()->index();
                    $table->string('action', 50)->index();
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

            // Seed initial creation logs for existing products if log table is empty
            if (Schema::hasTable('product_logs') && \App\Models\ProductLog::count() === 0 && Schema::hasTable('products') && \App\Models\Product::count() > 0) {
                $existingProducts = \App\Models\Product::with(['category', 'brand'])->get();
                $firstAdminId = \App\Models\User::where('role', 'admin')->orWhere('role', 'superadmin')->value('id') ?? \App\Models\User::value('id');
                foreach ($existingProducts as $ep) {
                    \App\Models\ProductLog::create([
                        'firm_id'            => $ep->firm_id,
                        'product_id'         => $ep->id,
                        'user_id'            => $firstAdminId,
                        'action'             => 'create',
                        'product_name'       => $ep->name,
                        'product_identifier' => $ep->product_identifier,
                        'hsn_code'           => $ep->hsn_code,
                        'category_name'      => $ep->category->name ?? null,
                        'brand_name'         => $ep->brand->name ?? null,
                        'description'        => "Initial product '{$ep->name}' registered in inventory.",
                        'changes'            => [
                            'attributes' => [
                                'name'               => $ep->name,
                                'product_identifier' => $ep->product_identifier,
                                'hsn_code'           => $ep->hsn_code,
                                'category'           => $ep->category->name ?? null,
                                'brand'              => $ep->brand->name ?? null,
                                'cost_price'         => (float) $ep->cost_price,
                                'selling_price'      => (float) $ep->selling_price,
                                'tax_percent'        => (float) $ep->tax_percent,
                                'stock_quantity'     => (int) $ep->stock_quantity,
                                'status'             => $ep->status,
                            ]
                        ],
                        'ip_address'         => '127.0.0.1',
                        'created_at'         => $ep->created_at ?? now(),
                        'updated_at'         => $ep->created_at ?? now(),
                    ]);
                }
            }

            // Ensure transaction_audit_logs table exists
            if (!Schema::hasTable('transaction_audit_logs')) {
                Schema::create('transaction_audit_logs', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('firm_id')->nullable()->index();
                    $table->unsignedBigInteger('user_id')->nullable()->index();
                    $table->string('module', 50)->index();
                    $table->string('action', 50)->index();
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

            // Seed initial creation logs for existing transactions if table is empty
            if (Schema::hasTable('transaction_audit_logs') && \App\Models\TransactionAuditLog::count() === 0) {
                if (Schema::hasTable('purchases')) {
                    $existingPurchases = \App\Models\Purchase::with(['supplier', 'user', 'items.product'])->get();
                    foreach ($existingPurchases as $p) {
                        \App\Models\TransactionAuditLog::record('purchase', 'create', $p, null, null, "Initial Purchase Order #{$p->invoice_number} entry recorded.");
                    }
                }
                if (Schema::hasTable('sales')) {
                    $existingSales = \App\Models\Sale::with(['customer', 'user', 'items.product'])->get();
                    foreach ($existingSales as $s) {
                        \App\Models\TransactionAuditLog::record('sale', 'create', $s, null, null, "Initial Sales Order #{$s->invoice_number} entry recorded.");
                    }
                }
                if (Schema::hasTable('returnable_materials')) {
                    $existingReturns = \App\Models\ReturnableMaterial::with(['customer', 'user', 'items.product'])->get();
                    foreach ($existingReturns as $r) {
                        \App\Models\TransactionAuditLog::record('returnable', 'create', $r, null, null, "Initial Returnable Entry #{$r->return_number} recorded.");
                    }
                }
            }
        } catch (\Throwable $e) {
            // Ignore schema errors during boot
        }
    }
}
