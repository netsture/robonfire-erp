<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Firm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SuperAdminPermissionTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $firmAdmin;
    private Firm $firm;
    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->firm = Firm::create([
            'name'   => 'Test Firm',
            'slug'   => 'test-firm',
            'status' => 'active',
        ]);

        $this->superAdmin = User::create([
            'name'     => 'Super Admin',
            'email'    => 'superadmin@system.com',
            'phone'    => '9999999999',
            'password' => Hash::make('password'),
            'role'     => 'superadmin',
            'firm_id'  => null,
            'status'   => 'active',
        ]);

        $this->firmAdmin = User::create([
            'name'     => 'Firm Admin',
            'email'    => 'admin@testfirm.com',
            'phone'    => '8888888888',
            'password' => Hash::make('password'),
            'role'     => 'admin',
            'firm_id'  => $this->firm->id,
            'status'   => 'active',
        ]);

        $this->customer = Customer::create([
            'firm_id'      => $this->firm->id,
            'company_name' => 'Existing Customer',
            'email'        => 'customer@example.com',
            'phone'        => '9876543210',
            'gst_number'   => 'GST001',
            'address'      => 'Main Street',
            'status'       => 'active',
        ]);
    }

    public function test_superadmin_can_access_firm_management_routes()
    {
        $response = $this->actingAs($this->superAdmin)->get(route('firms.index'));
        $response->assertOk();

        $response = $this->actingAs($this->superAdmin)->get(route('firms.create'));
        $response->assertOk();
    }

    public function test_superadmin_can_view_non_firm_indexes_and_shows()
    {
        $response = $this->actingAs($this->superAdmin)->get(route('customers.index'));
        $response->assertOk();

        $response = $this->actingAs($this->superAdmin)->get(route('customers.show', $this->customer));
        $response->assertOk();

        $response = $this->actingAs($this->superAdmin)->get(route('suppliers.index'));
        $response->assertOk();

        $response = $this->actingAs($this->superAdmin)->get(route('products.index'));
        $response->assertOk();

        $response = $this->actingAs($this->superAdmin)->get(route('users.index'));
        $response->assertOk();

        $response = $this->actingAs($this->superAdmin)->get(route('sales.index'));
        $response->assertOk();

        $response = $this->actingAs($this->superAdmin)->get(route('purchases.index'));
        $response->assertOk();

        $response = $this->actingAs($this->superAdmin)->get(route('categories.index'));
        $response->assertOk();

        $response = $this->actingAs($this->superAdmin)->get(route('brands.index'));
        $response->assertOk();
    }

    public function test_superadmin_is_blocked_from_creating_or_updating_non_firm_records()
    {
        // Customer create
        $response = $this->actingAs($this->superAdmin)->get(route('customers.create'));
        $response->assertStatus(403);

        // Customer store
        $response = $this->actingAs($this->superAdmin)->post(route('customers.store'), [
            'company_name' => 'New Customer',
            'phone'        => '9876543211',
            'gst_number'   => 'GST002',
            'address'      => 'Second Street',
            'status'       => 'active',
        ]);
        $response->assertStatus(403);

        // Customer edit
        $response = $this->actingAs($this->superAdmin)->get(route('customers.edit', $this->customer));
        $response->assertStatus(403);

        // Customer delete
        $response = $this->actingAs($this->superAdmin)->delete(route('customers.destroy', $this->customer));
        $response->assertStatus(403);

        // Supplier create
        $response = $this->actingAs($this->superAdmin)->get(route('suppliers.create'));
        $response->assertStatus(403);

        // User create
        $response = $this->actingAs($this->superAdmin)->get(route('users.create'));
        $response->assertStatus(403);

        // User edit
        $response = $this->actingAs($this->superAdmin)->get(route('users.edit', $this->firmAdmin));
        $response->assertStatus(403);

        // User update
        $response = $this->actingAs($this->superAdmin)->put(route('users.update', $this->firmAdmin), [
            'name' => 'Attempted Edit',
            'email' => 'admin@testfirm.com',
            'phone' => '8888888888',
            'role_id' => 1,
            'status' => 'active',
        ]);
        $response->assertStatus(403);

        // User toggle status
        $response = $this->actingAs($this->superAdmin)->post(route('users.toggle-status', $this->firmAdmin));
        $response->assertStatus(403);

        // User destroy
        $response = $this->actingAs($this->superAdmin)->delete(route('users.destroy', $this->firmAdmin));
        $response->assertStatus(403);

        $testRole = \App\Models\Role::create(['name' => 'Custom Role', 'slug' => 'custom-role']);

        // Role create
        $response = $this->actingAs($this->superAdmin)->get(route('roles.create'));
        $response->assertStatus(403);

        // Role store
        $response = $this->actingAs($this->superAdmin)->post(route('roles.store'), [
            'name' => 'New Unauthorized Role',
        ]);
        $response->assertStatus(403);

        // Role edit
        $response = $this->actingAs($this->superAdmin)->get(route('roles.edit', $testRole));
        $response->assertStatus(403);

        // Role update
        $response = $this->actingAs($this->superAdmin)->put(route('roles.update', $testRole), [
            'name' => 'Updated Role Name',
        ]);
        $response->assertStatus(403);

        // Role destroy
        $response = $this->actingAs($this->superAdmin)->delete(route('roles.destroy', $testRole));
        $response->assertStatus(403);
    }

    public function test_superadmin_users_index_shows_view_only_interface()
    {
        $response = $this->actingAs($this->superAdmin)->get(route('users.index'));
        $response->assertOk();
        $response->assertDontSee('Add New User');
        $response->assertDontSee(route('users.create'));
        $response->assertDontSee(route('users.edit', $this->firmAdmin));
        $response->assertDontSee(route('users.toggle-status', $this->firmAdmin));
        $response->assertSee('View User Details');
        $response->assertSee('viewUserModal_' . $this->firmAdmin->id);

        $firmAdminResponse = $this->actingAs($this->firmAdmin)->get(route('users.index'));
        $firmAdminResponse->assertOk();
        $firmAdminResponse->assertSee('Add New User');
        $firmAdminResponse->assertSee(route('users.create'));
        $firmAdminResponse->assertSee(route('users.edit', $this->firmAdmin));
        $firmAdminResponse->assertSee(route('users.toggle-status', $this->firmAdmin));
    }

    public function test_superadmin_roles_index_shows_view_only_interface()
    {
        $testRole = \App\Models\Role::create(['name' => 'Staff Role', 'slug' => 'staff-role']);

        $response = $this->actingAs($this->superAdmin)->get(route('roles.index'));
        $response->assertOk();
        $response->assertDontSee('Create New Role');
        $response->assertDontSee(route('roles.create'));
        $response->assertDontSee(route('roles.edit', $testRole));
        $response->assertSee('View Permissions');
        $response->assertSee('viewRoleModal_' . $testRole->id);

        $firmAdminResponse = $this->actingAs($this->firmAdmin)->get(route('roles.index'));
        $firmAdminResponse->assertOk();
        $firmAdminResponse->assertSee('Create New Role');
        $firmAdminResponse->assertSee(route('roles.create'));
        $firmAdminResponse->assertSee(route('roles.edit', $testRole));
    }

    public function test_firm_admin_can_create_customer()
    {
        $response = $this->actingAs($this->firmAdmin)->get(route('customers.create'));
        $response->assertOk();

        $response = $this->actingAs($this->firmAdmin)->post(route('customers.store'), [
            'company_name' => 'New Firm Customer',
            'phone'        => '9876543212',
            'gst_number'   => 'GST003',
            'address'      => 'Third Street',
            'status'       => 'active',
        ]);
        $response->assertRedirect(route('customers.index'));
    }

    public function test_superadmin_can_view_all_firms_data_and_filter_by_firm()
    {
        $firmB = Firm::create([
            'name'   => 'Second Firm',
            'slug'   => 'second-firm',
            'status' => 'active',
        ]);

        $customerB = Customer::create([
            'firm_id'      => $firmB->id,
            'company_name' => 'Firm B Customer',
            'email'        => 'customerB@example.com',
            'phone'        => '9876543299',
            'gst_number'   => 'GST999',
            'address'      => 'Second Firm Street',
            'status'       => 'active',
        ]);

        // Superadmin sees both customers on index
        $response = $this->actingAs($this->superAdmin)->get(route('customers.index'));
        $response->assertOk();
        $response->assertSee('Existing Customer');
        $response->assertSee('Firm B Customer');

        // Superadmin can filter by Firm B
        $response = $this->actingAs($this->superAdmin)->get(route('customers.index', ['firm_id' => $firmB->id]));
        $response->assertOk();
        $response->assertSee('Firm B Customer');
        $response->assertDontSee('Existing Customer');
    }

    public function test_superadmin_can_search_firm_by_name_email_phone_and_address()
    {
        $firm = Firm::create([
            'name'    => 'Fire Safety Solutions Pvt Ltd',
            'slug'    => 'fire-safety-solutions-pvt-ltd',
            'email'   => 'contact@firesafety.com',
            'phone'   => '9123456789',
            'address' => '789 Safety Boulevard, Zone 5',
            'status'  => 'active',
        ]);

        // Search by name
        $response = $this->actingAs($this->superAdmin)->get(route('firms.index', ['search' => 'Fire Safety']));
        $response->assertOk();
        $response->assertSee('Fire Safety Solutions Pvt Ltd');

        // Search by email
        $response = $this->actingAs($this->superAdmin)->get(route('firms.index', ['search' => 'firesafety.com']));
        $response->assertOk();
        $response->assertSee('Fire Safety Solutions Pvt Ltd');

        // Search by phone
        $response = $this->actingAs($this->superAdmin)->get(route('firms.index', ['search' => '9123456789']));
        $response->assertOk();
        $response->assertSee('Fire Safety Solutions Pvt Ltd');

        // Search by address
        $response = $this->actingAs($this->superAdmin)->get(route('firms.index', ['search' => 'Safety Boulevard']));
        $response->assertOk();
        $response->assertSee('Fire Safety Solutions Pvt Ltd');
    }
}
