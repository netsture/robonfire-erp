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
            'name' => 'Robonfire Safety Systems Ltd',
            'slug' => 'robonfire-safety',
            'email' => 'robonfire@gmail.com',
            'phone' => '9876543211',
            'address' => '100 Fire Tech Park, Suite 10',
            'status' => 'active',
        ]);

        $firm2 = Firm::create([
            'name' => 'PyroShield Fire Protection Ltd',
            'slug' => 'pyroshield-fire',
            'email' => 'fire@gmail.com',
            'phone' => '9876543212',
            'address' => '750 Flame Avenue',
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
            'phone' => '9876543210',
            'role' => 'superadmin',
            'firm_id' => null,
            'status' => 'active',
        ]);
        if ($superadminRole) $superadminUser->roles()->sync([$superadminRole->id]);

        $apexAdmin = User::create([
            'name' => 'Robonfire Admin',
            'email' => 'robonfire@gmail.com',
            'password' => Hash::make('123456'),
            'phone' => '9876543211',
            'role' => 'admin',
            'firm_id' => $firm1->id,
            'status' => 'active',
        ]);
        if ($adminRole) $apexAdmin->roles()->sync([$adminRole->id]);

        $acmeUser = User::create([
            'name' => 'PyroShield Operator',
            'email' => 'fire@gmail.com',
            'password' => Hash::make('123456'),
            'phone' => '9876543212',
            'role' => 'admin',
            'firm_id' => $firm2->id,
            'status' => 'active',
        ]);
        if ($userRole) $acmeUser->roles()->sync([$userRole->id]);

        // 4. Seed Data for Firm 1 (Robonfire Safety Systems Ltd)
        $catElectronics = Category::create([
            'firm_id' => $firm1->id,
            'name' => 'Fire Extinguishers & Suppression',
            'slug' => 'fire-extinguishers-suppression',
            'description' => 'Portable extinguishers and suppression systems'
        ]);
        $catOffice = Category::create([
            'firm_id' => $firm1->id,
            'name' => 'Fire Hydrants & Safety Gear',
            'slug' => 'fire-hydrants-safety-gear',
            'description' => 'Hoses, nozzles, valves and protective gear'
        ]);

        $brandDell = Brand::create([
            'firm_id' => $firm1->id,
            'name' => 'Minimax Fire Protection',
            'slug' => 'minimax-fire-protection',
            'description' => 'High-grade fire safety extinguishers'
        ]);
        $brandHerman = Brand::create([
            'firm_id' => $firm1->id,
            'name' => 'Ceasefire Safety',
            'slug' => 'ceasefire-safety',
            'description' => 'Advanced fire suppression systems'
        ]);

        $p1 = Product::create([
            'firm_id' => $firm1->id,
            'name' => 'ABC Dry Powder Extinguisher 5kg',
            'hsn_code' => '84241000',
            'category_id' => $catElectronics->id,
            'brand_id' => $brandDell->id,
            'unit' => 'Pcs',
            'cost_price' => 280.00,
            'selling_price' => 450.00,
            'tax_percent' => 5.00,
            'alert_quantity' => 5,
            'stock_quantity' => 18,
            'description' => '5kg ABC dry powder extinguisher with pressure gauge',
            'status' => 'active',
        ]);

        $p2 = Product::create([
            'firm_id' => $firm1->id,
            'name' => 'CO2 Fire Extinguisher 4.5kg',
            'hsn_code' => '84241000',
            'category_id' => $catOffice->id,
            'brand_id' => $brandHerman->id,
            'unit' => 'Pcs',
            'cost_price' => 120.00,
            'selling_price' => 220.00,
            'tax_percent' => 5.00,
            'alert_quantity' => 4,
            'stock_quantity' => 12,
            'description' => '4.5kg Carbon Dioxide extinguisher for electrical fires',
            'status' => 'active',
        ]);

        $c1 = Customer::create([
            'firm_id' => $firm1->id,
            'company_name' => 'BlazeProtect Safety Solutions',
            'email' => 'contact@blazeprotect.com',
            'phone' => '9876543213',
            'gst_number' => 'TAX-FIRE-99',
            'address' => '100 Industrial Safety Park',
            'status' => 'active',
        ]);

        $s1 = Supplier::create([
            'firm_id' => $firm1->id,
            'company_name' => 'Global Fire Equipment Depot',
            'email' => 'sales@globalfiredepot.com',
            'phone' => '9876543214',
            'gst_number' => 'VAT-FIRE-101',
            'address' => '88 Safety Blvd',
            'status' => 'active',
        ]);

        $pur1 = Purchase::create([
            'firm_id' => $firm1->id,
            'project_name' => 'Fire Extinguisher Restock Project',
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
            'notes' => 'Robonfire initial stock batch purchase',
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
            'notes' => 'Robonfire equipment supply sale',
        ]);

        $sale1->items()->create([
            'product_id' => $p1->id,
            'unit_price' => 450.00,
            'quantity' => 3,
            'subtotal' => 1350.00,
        ]);

        // 5. Seed Data for Firm 2 (PyroShield Fire Protection Ltd)
        $catNetworking = Category::create([
            'firm_id' => $firm2->id,
            'name' => 'Fire Detection & Alarm Systems',
            'slug' => 'fire-detection-alarm-systems',
            'description' => 'Smoke detectors, heat sensors, alarm panels'
        ]);

        $brandCisco = Brand::create([
            'firm_id' => $firm2->id,
            'name' => 'Kidde Fire Systems',
            'slug' => 'kidde-fire-systems',
            'description' => 'Commercial fire alarm and detection systems'
        ]);

        $p3 = Product::create([
            'firm_id' => $firm2->id,
            'name' => 'Automatic Smoke & Heat Detector',
            'hsn_code' => '85311090',
            'category_id' => $catNetworking->id,
            'brand_id' => $brandCisco->id,
            'unit' => 'Pcs',
            'cost_price' => 190.00,
            'selling_price' => 310.00,
            'tax_percent' => 5.00,
            'alert_quantity' => 3,
            'stock_quantity' => 8,
            'description' => 'Optical smoke sensor with built-in 85dB sounder',
            'status' => 'active',
        ]);

        $s2 = Supplier::create([
            'firm_id' => $firm2->id,
            'company_name' => 'FlameGuard Safety Wholesale',
            'email' => 'orders@flameguard.com',
            'phone' => '9876543215',
            'gst_number' => 'VAT-FLM-500',
            'address' => '12 Network Parkway',
            'status' => 'active',
        ]);

        $pur2 = Purchase::create([
            'firm_id' => $firm2->id,
            'project_name' => 'Safety Gear Procurement Project',
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
            'notes' => 'PyroShield initial detector purchase',
        ]);

        $pur2->items()->create([
            'product_id' => $p3->id,
            'unit_cost' => 190.00,
            'quantity' => 8,
            'subtotal' => 1520.00,
        ]);
    }
}
