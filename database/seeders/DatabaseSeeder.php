<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Tenant;
use App\Models\Party;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Run Role and Permission Seeder
        $this->call(RoleAndPermissionSeeder::class);

        // 2. Create Super Admin User
        $superAdmin = User::create([
            'tenant_id' => null,
            'name' => 'Super Admin',
            'email' => 'superadmin@robonfire.com',
            'mobile' => '0000000000',
            'username' => 'superadmin',
            'password' => Hash::make('password'),
            'status' => 'Active',
        ]);
        $superAdmin->assignRole('Super Admin');

        // 3. Create a Default Tenant
        $tenant = Tenant::create([
            'name' => 'Netsture Technologies',
            'email' => 'admin@netsture.com',
            'mobile' => '9876543210',
            'status' => 'Active',
        ]);

        // 4. Create Tenant Admin
        $tenantAdmin = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Samir Patel',
            'email' => 'admin@netsture.com',
            'mobile' => '9876543210',
            'username' => 'samiradmin',
            'password' => Hash::make('password'),
            'status' => 'Active',
        ]);
        $tenantAdmin->assignRole('Admin');

        // 5. Create Default Parties for this Tenant
        $party1 = Party::create([
            'tenant_id' => $tenant->id,
            'name' => 'Kanex Fire Safety Pvt Ltd',
            'email' => 'party@kanex.com',
            'mobile' => '9988776655',
            'address' => 'Ahmedabad.',
        ]);

        $party2 = Party::create([
            'tenant_id' => $tenant->id,
            'name' => 'Fire Safe Solutions Ltd',
            'email' => 'contact@firesafe.com',
            'mobile' => '8877665544',
            'address' => 'Mumbai, MH',
        ]);

        // 6. Seed default Purchase records matching the screenshot
        
        // --- INWARD ---
        $purchaseInward = Purchase::create([
            'tenant_id' => $tenant->id,
            'party_id' => $party1->id,
            'type' => 'inward',
            'date' => '2026-08-01',
            'bill_no' => '0230/2026',
            'project_name' => 'Tata - Sanad',
            'address' => 'Ahmedabad.',
            'grand_total' => 12500.00,
            'grand_total_with_tax' => 14750.00,
        ]);

        PurchaseItem::create([
            'purchase_id' => $purchaseInward->id,
            'product_name' => 'ABC 6 KG Fire Extinguisher',
            'brand' => 'Kanex',
            'category' => 'Fire Extinguisher',
            'hsn_code' => '8424',
            'quantity' => 10.00,
            'type' => 'Nos.',
            'rate' => 1250.00,
            'tax_percent' => 18.00,
            'total' => 12500.00,
            'total_with_tax' => 14750.00,
        ]);

        // --- OUTWARD ---
        $purchaseOutward = Purchase::create([
            'tenant_id' => $tenant->id,
            'party_id' => $party1->id,
            'type' => 'outward',
            'date' => '2026-08-01',
            'chalan_no' => '0230/2026',
            'vehicle' => 'GJ01HZ7154',
            'project_name' => 'Tata - Sanad',
            'address' => 'Ahmedabad.',
            'grand_total' => 0.00,
            'grand_total_with_tax' => 0.00,
        ]);

        PurchaseItem::create([
            'purchase_id' => $purchaseOutward->id,
            'product_name' => 'ABC 6 KG Fire Extinguisher',
            'brand' => 'Kanex',
            'category' => 'Fire Extinguisher',
            'hsn_code' => '8424',
            'quantity' => 10.00,
            'type' => 'Nos.',
            'rate' => 0.00,
            'tax_percent' => 18.00,
            'total' => 0.00,
            'total_with_tax' => 0.00,
        ]);

        // --- RETURNABLE MATERIAL ---
        $purchaseRetMat = Purchase::create([
            'tenant_id' => $tenant->id,
            'party_id' => $party1->id,
            'type' => 'returnable_material',
            'date' => '2026-08-01',
            'chalan_no' => '0230/2026',
            'reason' => 'Cancel order',
            'project_name' => 'Tata - Sanad',
            'address' => 'Ahmedabad.',
            'grand_total' => 0.00,
            'grand_total_with_tax' => 0.00,
        ]);

        PurchaseItem::create([
            'purchase_id' => $purchaseRetMat->id,
            'product_name' => 'ABC 6 KG Fire Extinguisher',
            'brand' => 'Kanex',
            'category' => 'Fire Extinguisher',
            'hsn_code' => '8424',
            'quantity' => 10.00,
            'type' => 'Nos.',
            'rate' => 0.00,
            'tax_percent' => 18.00,
            'total' => 0.00,
            'total_with_tax' => 0.00,
        ]);

        // --- RETURNABLE CHALAN ---
        $purchaseRetChal = Purchase::create([
            'tenant_id' => $tenant->id,
            'party_id' => $party1->id,
            'type' => 'returnable_chalan',
            'date' => '2026-08-01',
            'chalan_no' => '0230/2026',
            'project_name' => 'Tata - Sanad',
            'address' => 'Ahmedabad.',
            'grand_total' => 0.00,
            'grand_total_with_tax' => 0.00,
        ]);

        PurchaseItem::create([
            'purchase_id' => $purchaseRetChal->id,
            'product_name' => 'ABC 6 KG Fire Extinguisher',
            'brand' => 'Kanex',
            'category' => 'Fire Extinguisher',
            'hsn_code' => '8424',
            'quantity' => 10.00,
            'type' => 'Nos.',
            'rate' => 0.00,
            'tax_percent' => 18.00,
            'total' => 0.00,
            'total_with_tax' => 0.00,
        ]);
    }
}
