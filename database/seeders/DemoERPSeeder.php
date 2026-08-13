<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoERPSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Categories
        $catElectronics = Category::create(['name' => 'Electronics & Hardware', 'slug' => 'electronics-hardware', 'description' => 'Computer components and tech gear']);
        $catOffice = Category::create(['name' => 'Office Furniture & Supplies', 'slug' => 'office-furniture-supplies', 'description' => 'Desks, chairs, stationary']);
        $catNetworking = Category::create(['name' => 'Networking & Storage', 'slug' => 'networking-storage', 'description' => 'Switches, routers, NAS servers']);

        // 2. Products
        $p1 = Product::create([
            'name' => 'UltraSharp 27" 4K Monitor',
            'sku' => 'PRD-MON-001',
            'barcode' => '880192837411',
            'category_id' => $catElectronics->id,
            'unit' => 'Pcs',
            'cost_price' => 280.00,
            'selling_price' => 450.00,
            'tax_percent' => 5.00,
            'alert_quantity' => 5,
            'stock_quantity' => 18,
            'description' => 'IPS 4K Display with USB-C Hub',
            'status' => 'active',
        ]);

        $p2 = Product::create([
            'name' => 'Ergonomic Mesh Office Chair',
            'sku' => 'PRD-CHR-002',
            'barcode' => '880192837422',
            'category_id' => $catOffice->id,
            'unit' => 'Pcs',
            'cost_price' => 120.00,
            'selling_price' => 220.00,
            'tax_percent' => 5.00,
            'alert_quantity' => 4,
            'stock_quantity' => 12,
            'description' => 'Lumbar support mesh executive chair',
            'status' => 'active',
        ]);

        $p3 = Product::create([
            'name' => 'Gigabit 24-Port Managed Switch',
            'sku' => 'PRD-NET-003',
            'barcode' => '880192837433',
            'category_id' => $catNetworking->id,
            'unit' => 'Pcs',
            'cost_price' => 190.00,
            'selling_price' => 310.00,
            'tax_percent' => 5.00,
            'alert_quantity' => 3,
            'stock_quantity' => 2, // Low stock!
            'description' => 'PoE+ Managed Rackmount Switch',
            'status' => 'active',
        ]);

        // 3. Customers
        $c1 = Customer::create([
            'name' => 'Apex Tech Solutions',
            'email' => 'contact@apextech.com',
            'phone' => '+1 (555) 234-5678',
            'company_name' => 'Apex Tech Inc',
            'tax_number' => 'TAX-APEX-99',
            'address' => '100 Innovation Way, Tech Park',
            'city' => 'San Jose, CA',
            'credit_limit' => 10000.00,
            'opening_balance' => 0.00,
            'current_balance' => 0.00,
            'status' => 'active',
        ]);

        $c2 = Customer::create([
            'name' => 'Horizon Global Services',
            'email' => 'billing@horizonglobal.org',
            'phone' => '+1 (555) 876-5432',
            'company_name' => 'Horizon Corp',
            'tax_number' => 'TAX-HORIZON-44',
            'address' => '45 Financial Ave',
            'city' => 'New York, NY',
            'credit_limit' => 15000.00,
            'opening_balance' => 0.00,
            'current_balance' => 0.00,
            'status' => 'active',
        ]);

        // 4. Suppliers
        $s1 = Supplier::create([
            'name' => 'Global Microtech Distributors',
            'email' => 'sales@globalmicro.com',
            'phone' => '+1 (800) 555-0199',
            'company_name' => 'Global Microtech LLC',
            'tax_number' => 'VAT-SUP-101',
            'address' => '88 Logistics Blvd',
            'city' => 'Chicago, IL',
            'opening_balance' => 0.00,
            'current_balance' => 0.00,
            'status' => 'active',
        ]);

        // 5. Sample Purchases
        $admin = User::first();

        $pur1 = Purchase::create([
            'reference_no' => 'PO-202608-001',
            'supplier_id' => $s1->id,
            'user_id' => $admin->id,
            'purchase_date' => date('Y-m-d', strtotime('-5 days')),
            'subtotal' => 2800.00,
            'discount_amount' => 100.00,
            'shipping_cost' => 50.00,
            'grand_total' => 2750.00,
            'paid_amount' => 2750.00,
            'payment_status' => 'paid',
            'notes' => 'Received via Freight Express',
        ]);

        $pur1->items()->create([
            'product_id' => $p1->id,
            'unit_cost' => 280.00,
            'quantity' => 10,
            'subtotal' => 2800.00,
        ]);

        // 6. Sample Sales
        $sale1 = Sale::create([
            'invoice_number' => 'INV-202608-1001',
            'customer_id' => $c1->id,
            'user_id' => $admin->id,
            'sale_date' => date('Y-m-d', strtotime('-2 days')),
            'subtotal' => 1350.00,
            'tax_amount' => 67.50,
            'discount_amount' => 50.00,
            'shipping_cost' => 20.00,
            'grand_total' => 1387.50,
            'paid_amount' => 1387.50,
            'payment_status' => 'paid',
            'notes' => 'Paid via Credit Card POS',
        ]);

        $sale1->items()->create([
            'product_id' => $p1->id,
            'unit_price' => 450.00,
            'quantity' => 3,
            'subtotal' => 1350.00,
        ]);
    }
}
