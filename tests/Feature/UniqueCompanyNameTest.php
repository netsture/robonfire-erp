<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Firm;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UniqueCompanyNameTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Firm $firm;

    protected function setUp(): void
    {
        parent::setUp();

        $this->firm = Firm::create([
            'name'   => 'Test Firm',
            'slug'   => 'test-firm',
            'status' => 'active',
        ]);

        $this->user = User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@testfirm.com',
            'phone'    => '9876543210',
            'password' => Hash::make('password'),
            'role'     => 'admin',
            'firm_id'  => $this->firm->id,
            'status'   => 'active',
        ]);
    }

    public function test_customer_creation_fails_if_company_name_is_not_unique()
    {
        Customer::create([
            'firm_id'      => $this->firm->id,
            'company_name' => 'Acme Corp',
            'email'        => 'acme@example.com',
            'phone'        => '9876543211',
            'gst_number'   => 'GST123',
            'address'      => 'Address 1',
            'status'       => 'active',
        ]);

        $response = $this->actingAs($this->user)->post(route('customers.store'), [
            'company_name' => 'Acme Corp',
            'email'        => 'acme2@example.com',
            'phone'        => '9876543212',
            'gst_number'   => 'GST456',
            'address'      => 'Address 2',
            'status'       => 'active',
        ]);

        $response->assertSessionHasErrors(['company_name']);
    }

    public function test_supplier_creation_fails_if_company_name_is_not_unique()
    {
        Supplier::create([
            'firm_id'      => $this->firm->id,
            'company_name' => 'Global Logistics',
            'email'        => 'global@example.com',
            'phone'        => '9876543213',
            'gst_number'   => 'SUP123',
            'address'      => 'Warehouse 1',
            'status'       => 'active',
        ]);

        $response = $this->actingAs($this->user)->post(route('suppliers.store'), [
            'company_name' => 'Global Logistics',
            'email'        => 'global2@example.com',
            'phone'        => '9876543214',
            'gst_number'   => 'SUP456',
            'address'      => 'Warehouse 2',
            'status'       => 'active',
        ]);

        $response->assertSessionHasErrors(['company_name']);
    }

    public function test_customer_update_allows_same_company_name_for_itself()
    {
        $customer = Customer::create([
            'firm_id'      => $this->firm->id,
            'company_name' => 'Unique Customer',
            'email'        => 'unique@example.com',
            'phone'        => '9876543215',
            'gst_number'   => 'GST789',
            'address'      => 'Address 3',
            'status'       => 'active',
        ]);

        $response = $this->actingAs($this->user)->put(route('customers.update', $customer), [
            'company_name' => 'Unique Customer',
            'email'        => 'unique_updated@example.com',
            'phone'        => '9876543215',
            'gst_number'   => 'GST789',
            'address'      => 'Address 3 Updated',
            'status'       => 'active',
        ]);

        $response->assertRedirect(route('customers.index'));
        $response->assertSessionHasNoErrors();
    }

    public function test_supplier_update_allows_same_company_name_for_itself()
    {
        $supplier = Supplier::create([
            'firm_id'      => $this->firm->id,
            'company_name' => 'Unique Supplier',
            'email'        => 'unique_sup@example.com',
            'phone'        => '9876543216',
            'gst_number'   => 'SUP789',
            'address'      => 'Warehouse 3',
            'status'       => 'active',
        ]);

        $response = $this->actingAs($this->user)->put(route('suppliers.update', $supplier), [
            'company_name' => 'Unique Supplier',
            'email'        => 'unique_sup_updated@example.com',
            'phone'        => '9876543216',
            'gst_number'   => 'SUP789',
            'address'      => 'Warehouse 3 Updated',
            'status'       => 'active',
        ]);

        $response->assertRedirect(route('suppliers.index'));
        $response->assertSessionHasNoErrors();
    }
}
