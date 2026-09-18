<?php

namespace Tests\Feature;

use App\Models\Firm;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UniqueProjectNameValidationTest extends TestCase
{
    use RefreshDatabase;

    private Firm $firm1;
    private Firm $firm2;
    private User $admin1;
    private User $admin2;
    private Project $project1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->firm1 = Firm::create(['name' => 'Firm Alpha', 'slug' => 'firm-alpha', 'status' => 'active']);
        $this->firm2 = Firm::create(['name' => 'Firm Beta', 'slug' => 'firm-beta', 'status' => 'active']);

        $this->admin1 = User::create([
            'name'     => 'Admin Alpha',
            'email'    => 'admin1@alpha.com',
            'phone'    => '9111111111',
            'password' => Hash::make('password'),
            'role'     => 'admin',
            'firm_id'  => $this->firm1->id,
            'status'   => 'active',
        ]);

        $this->admin2 = User::create([
            'name'     => 'Admin Beta',
            'email'    => 'admin2@beta.com',
            'phone'    => '9222222222',
            'password' => Hash::make('password'),
            'role'     => 'admin',
            'firm_id'  => $this->firm2->id,
            'status'   => 'active',
        ]);

        $this->project1 = Project::create([
            'firm_id'      => $this->firm1->id,
            'project_name' => 'Metro Line 3 Expansion',
            'po_number'    => 'PO-1001',
            'po_date'      => '2026-09-01',
            'po_amount'    => 500000.00,
        ]);
    }

    public function test_project_name_must_be_unique_within_same_firm_on_create()
    {
        // Try creating duplicate project in Firm 1
        $response = $this->actingAs($this->admin1)->post(route('projects.store'), [
            'project_name' => 'Metro Line 3 Expansion',
            'po_number'    => 'PO-1002',
            'po_date'      => '2026-09-02',
            'po_amount'    => 200000.00,
        ]);
        $response->assertSessionHasErrors(['project_name']);

        // Firm 2 can create project with the same name
        $response = $this->actingAs($this->admin2)->post(route('projects.store'), [
            'project_name' => 'Metro Line 3 Expansion',
            'po_number'    => 'PO-2001',
            'po_date'      => '2026-09-02',
            'po_amount'    => 300000.00,
        ]);
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('projects', [
            'firm_id'      => $this->firm2->id,
            'project_name' => 'Metro Line 3 Expansion',
        ]);
    }

    public function test_project_name_must_be_unique_within_same_firm_on_update()
    {
        $project2 = Project::create([
            'firm_id'      => $this->firm1->id,
            'project_name' => 'City Hospital Refurbishment',
            'po_number'    => 'PO-1003',
            'po_date'      => '2026-09-05',
            'po_amount'    => 150000.00,
        ]);

        // Attempting to rename project2 to project1's name should fail validation
        $response = $this->actingAs($this->admin1)->put(route('projects.update', $project2), [
            'project_name' => 'Metro Line 3 Expansion',
            'po_number'    => 'PO-1003',
            'po_date'      => '2026-09-05',
            'po_amount'    => 150000.00,
        ]);
        $response->assertSessionHasErrors(['project_name']);

        // Updating project1 without changing its name should succeed
        $response = $this->actingAs($this->admin1)->put(route('projects.update', $this->project1), [
            'project_name' => 'Metro Line 3 Expansion',
            'po_number'    => 'PO-1001-REV1',
            'po_date'      => '2026-09-01',
            'po_amount'    => 550000.00,
        ]);
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('projects', [
            'id'        => $this->project1->id,
            'po_number' => 'PO-1001-REV1',
            'po_amount' => 550000.00,
        ]);
    }

    public function test_all_fields_are_mandatory_on_project_create()
    {
        $response = $this->actingAs($this->admin1)->post(route('projects.store'), []);

        $response->assertSessionHasErrors([
            'project_name',
            'po_number',
            'po_date',
            'po_amount',
        ]);
    }
}
