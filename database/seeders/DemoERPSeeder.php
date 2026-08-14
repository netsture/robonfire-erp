<?php

namespace Database\Seeders;

use App\Models\Firm;
use App\Models\Role;
use App\Models\User;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoERPSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Firms
        $firm1 = Firm::create([
            'name' => 'Acme Enterprise Ltd',
            'slug' => 'acme-enterprise',
            'email' => 'contact@acme.com',
            'phone' => '+1 (555) 100-2000',
            'address' => '500 Enterprise Way, Suite 10',
            'status' => 'active',
        ]);

        $firm2 = Firm::create([
            'name' => 'Apex Global Logistics',
            'slug' => 'apex-logistics',
            'email' => 'info@apexlogistics.com',
            'phone' => '+1 (555) 300-4000',
            'address' => '750 Cargo Hub Blvd',
            'status' => 'active',
        ]);

        // 2. Roles
        $superadminRole = Role::where('slug', 'superadmin')->first();
        $adminRole = Role::where('slug', 'admin')->first();
        $userRole = Role::where('slug', 'user')->first();

        // 3. Users
        $superadminUser = User::create([
            'name' => 'Super Administrator',
            'email' => 'superadmin@gmail.com',
            'password' => Hash::make('123456'),
            'phone' => '+1 (555) 000-0000',
            'role' => 'superadmin',
            'firm_id' => null,
            'status' => 'active',
        ]);
        if ($superadminRole) $superadminUser->roles()->sync([$superadminRole->id]);

        $apexAdmin = User::create([
            'name' => 'Apex Admin',
            'email' => 'firm1@gmail.com',
            'password' => Hash::make('123456'),
            'phone' => '+1 (555) 333-4444',
            'role' => 'admin',
            'firm_id' => $firm1->id,
            'status' => 'active',
        ]);
        if ($adminRole) $apexAdmin->roles()->sync([$adminRole->id]);

        

        $acmeUser = User::create([
            'name' => 'Acme Operator',
            'email' => 'firm2@gmail.com',
            'password' => Hash::make('123456'),
            'phone' => '+1 (555) 111-3333',
            'role' => 'admin',
            'firm_id' => $firm2->id,
            'status' => 'active',
        ]);
        if ($userRole) $acmeUser->roles()->sync([$userRole->id]);

        


        

        // 4. Seed Data for Firm 1 (Acme Enterprise Ltd)
        $catElectronics = Category::create([
            'firm_id' => $firm1->id,
            'name' => 'Electronics & Hardware',
            'slug' => 'electronics-hardware',
            'description' => 'Computer components and tech gear'
        ]);
        $catOffice = Category::create([
            'firm_id' => $firm1->id,
            'name' => 'Office Furniture & Supplies',
            'slug' => 'office-furniture-supplies',
            'description' => 'Desks, chairs, stationary'
        ]);

        $brandDell = Brand::create([
            'firm_id' => $firm1->id,
            'name' => 'Dell Technologies',
            'slug' => 'dell-technologies',
            'description' => 'Enterprise displays and workstations'
        ]);
        $brandHerman = Brand::create([
            'firm_id' => $firm1->id,
            'name' => 'Herman Miller',
            'slug' => 'herman-miller',
            'description' => 'Ergonomic seating and office furniture'
        ]);

        $p1 = Product::create([
            'firm_id' => $firm1->id,
            'name' => 'UltraSharp 27" 4K Monitor',
            'sku' => 'PRD-MON-001',
            'barcode' => '880192837411',
            'hsn_code' => '84713010',
            'category_id' => $catElectronics->id,
            'brand_id' => $brandDell->id,
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
            'firm_id' => $firm1->id,
            'name' => 'Ergonomic Mesh Office Chair',
            'sku' => 'PRD-CHR-002',
            'barcode' => '880192837422',
            'hsn_code' => '94029090',
            'category_id' => $catOffice->id,
            'brand_id' => $brandHerman->id,
            'unit' => 'Pcs',
            'cost_price' => 120.00,
            'selling_price' => 220.00,
            'tax_percent' => 5.00,
            'alert_quantity' => 4,
            'stock_quantity' => 12,
            'description' => 'Lumbar support mesh executive chair',
            'status' => 'active',
        ]);

        $c1 = Customer::create([
            'firm_id' => $firm1->id,
            'name' => 'Apex Tech Solutions',
            'email' => 'contact@apextech.com',
            'phone' => '+1 (555) 234-5678',
            'company_name' => 'Apex Tech Inc',
            'tax_number' => 'TAX-APEX-99',
            'address' => '100 Innovation Way, Tech Park',
            'city' => 'San Jose, CA',
            'current_balance' => 0.00,
            'status' => 'active',
        ]);

        $s1 = Supplier::create([
            'firm_id' => $firm1->id,
            'name' => 'Global Microtech Distributors',
            'email' => 'sales@globalmicro.com',
            'phone' => '+1 (800) 555-0199',
            'company_name' => 'Global Microtech LLC',
            'tax_number' => 'VAT-SUP-101',
            'address' => '88 Logistics Blvd',
            'city' => 'Chicago, IL',
            'current_balance' => 0.00,
            'status' => 'active',
        ]);

        $pur1 = Purchase::create([
            'firm_id' => $firm1->id,
            'reference_no' => 'REF-FRM1-0001',
            'invoice_number' => 'PINV-FRM1-0001',
            'supplier_id' => $s1->id,
            'user_id' => $superadminUser->id,
            'purchase_date' => date('Y-m-d', strtotime('-5 days')),
            'subtotal' => 2800.00,
            'discount_amount' => 100.00,
            'shipping_cost' => 50.00,
            'grand_total' => 2750.00,
            'paid_amount' => 2750.00,
            'payment_status' => 'paid',
            'notes' => 'Firm 1 initial stock batch',
        ]);

        $pur1->items()->create([
            'product_id' => $p1->id,
            'unit_cost' => 280.00,
            'quantity' => 10,
            'subtotal' => 2800.00,
        ]);

        $sale1 = Sale::create([
            'firm_id' => $firm1->id,
            'invoice_number' => 'INV-FRM1-0001',
            'customer_id' => $c1->id,
            'user_id' => $superadminUser->id,
            'sale_date' => date('Y-m-d', strtotime('-2 days')),
            'subtotal' => 1350.00,
            'tax_amount' => 67.50,
            'discount_amount' => 50.00,
            'shipping_cost' => 20.00,
            'grand_total' => 1387.50,
            'paid_amount' => 1387.50,
            'payment_status' => 'paid',
            'notes' => 'Acme POS sale',
        ]);

        $sale1->items()->create([
            'product_id' => $p1->id,
            'unit_price' => 450.00,
            'quantity' => 3,
            'subtotal' => 1350.00,
        ]);

        // 5. Seed Data for Firm 2 (Apex Global Logistics)
        $catNetworking = Category::create([
            'firm_id' => $firm2->id,
            'name' => 'Networking & Storage',
            'slug' => 'networking-storage',
            'description' => 'Switches, routers, NAS servers'
        ]);

        $brandCisco = Brand::create([
            'firm_id' => $firm2->id,
            'name' => 'Cisco Systems',
            'slug' => 'cisco-systems',
            'description' => 'Enterprise networking hardware'
        ]);

        $p3 = Product::create([
            'firm_id' => $firm2->id,
            'name' => 'Gigabit 24-Port Managed Switch',
            'sku' => 'PRD-NET-003',
            'barcode' => '880192837433',
            'hsn_code' => '85176290',
            'category_id' => $catNetworking->id,
            'brand_id' => $brandCisco->id,
            'unit' => 'Pcs',
            'cost_price' => 190.00,
            'selling_price' => 310.00,
            'tax_percent' => 5.00,
            'alert_quantity' => 3,
            'stock_quantity' => 8,
            'description' => 'PoE+ Managed Rackmount Switch',
            'status' => 'active',
        ]);

        $s2 = Supplier::create([
            'firm_id' => $firm2->id,
            'name' => 'Nexus NetCom Wholesale',
            'email' => 'orders@nexusnet.com',
            'phone' => '+1 (800) 444-9911',
            'company_name' => 'Nexus NetCom Inc',
            'tax_number' => 'VAT-NEX-500',
            'address' => '12 Network Parkway',
            'city' => 'Austin, TX',
            'current_balance' => 0.00,
            'status' => 'active',
        ]);

        $pur2 = Purchase::create([
            'firm_id' => $firm2->id,
            'reference_no' => 'REF-FRM2-0001',
            'invoice_number' => 'PINV-FRM2-0001',
            'supplier_id' => $s2->id,
            'user_id' => $apexAdmin->id,
            'purchase_date' => date('Y-m-d', strtotime('-1 day')),
            'subtotal' => 1520.00,
            'discount_amount' => 0.00,
            'shipping_cost' => 30.00,
            'grand_total' => 1550.00,
            'paid_amount' => 1550.00,
            'payment_status' => 'paid',
            'notes' => 'Firm 2 initial switch purchase',
        ]);

        $pur2->items()->create([
            'product_id' => $p3->id,
            'unit_cost' => 190.00,
            'quantity' => 8,
            'subtotal' => 1520.00,
        ]);
    }
}
