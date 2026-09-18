<?php

namespace Tests\Feature;

use App\Models\Firm;
use App\Models\Project;
use App\Models\ProjectExpense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectExpenseTest extends TestCase
{
    use RefreshDatabase;

    private Firm $firm1;
    private Firm $firm2;
    private User $admin1;
    private User $admin2;
    private User $superAdmin;
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

        $this->superAdmin = User::create([
            'name'     => 'Super Admin',
            'email'    => 'superadmin@erp.com',
            'phone'    => '9000000000',
            'password' => Hash::make('password'),
            'role'     => 'superadmin',
            'firm_id'  => null,
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

    public function test_admin_can_add_project_expense_with_document()
    {
        $file = UploadedFile::fake()->create('bill.pdf', 100, 'application/pdf');

        $response = $this->from(route('projects.show', ['project' => $this->project1, 'tab' => 'expenses']))
            ->actingAs($this->admin1)
            ->post(route('projects.expenses.store', $this->project1), [
                'item_name'    => 'Electrical Cables',
                'description'  => 'Heavy duty wiring for station section 2',
                'amount'       => 45000.50,
                'expense_date' => '2026-09-18',
                'document'     => $file,
            ]);

        $response->assertRedirect(route('projects.show', ['project' => $this->project1, 'tab' => 'expenses']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('project_expenses', [
            'project_id'   => $this->project1->id,
            'item_name'    => 'Electrical Cables',
            'description'  => 'Heavy duty wiring for station section 2',
            'amount'       => 45000.50,
            'expense_date' => '2026-09-18 00:00:00',
        ]);

        $expense = ProjectExpense::first();
        $this->assertNotNull($expense->document_path);
        $this->assertFileExists(public_path($expense->document_path));

        // Clean up test file
        if (file_exists(public_path($expense->document_path))) {
            @unlink(public_path($expense->document_path));
        }
    }

    public function test_admin_can_update_project_expense()
    {
        $expense = ProjectExpense::create([
            'project_id'   => $this->project1->id,
            'item_name'    => 'Safety Helmets',
            'description'  => '50 units',
            'amount'       => 15000.00,
            'expense_date' => '2026-09-10',
        ]);

        $response = $this->from(route('projects.show', ['project' => $this->project1, 'tab' => 'expenses']))
            ->actingAs($this->admin1)
            ->put(route('projects.expenses.update', [$this->project1, $expense]), [
                'item_name'    => 'Safety Helmets & Jackets',
                'description'  => '50 units helmets + 50 jackets',
                'amount'       => 25000.00,
                'expense_date' => '2026-09-10',
            ]);

        $response->assertRedirect(route('projects.show', ['project' => $this->project1, 'tab' => 'expenses']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('project_expenses', [
            'id'          => $expense->id,
            'item_name'   => 'Safety Helmets & Jackets',
            'amount'      => 25000.00,
        ]);
    }

    public function test_admin_can_delete_project_expense()
    {
        $expense = ProjectExpense::create([
            'project_id'   => $this->project1->id,
            'item_name'    => 'Site Scaffolding',
            'amount'       => 12000.00,
            'expense_date' => '2026-09-12',
        ]);

        $response = $this->from(route('projects.show', ['project' => $this->project1, 'tab' => 'expenses']))
            ->actingAs($this->admin1)
            ->delete(route('projects.expenses.destroy', [$this->project1, $expense]));

        $response->assertRedirect(route('projects.show', ['project' => $this->project1, 'tab' => 'expenses']));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('project_expenses', [
            'id' => $expense->id,
        ]);
    }

    public function test_admin_from_other_firm_cannot_access_or_modify_expenses()
    {
        $expense = ProjectExpense::create([
            'project_id'   => $this->project1->id,
            'item_name'    => 'Site Scaffolding',
            'amount'       => 12000.00,
            'expense_date' => '2026-09-12',
        ]);

        // Attempt store
        $response = $this->actingAs($this->admin2)->post(route('projects.expenses.store', $this->project1), [
            'item_name'    => 'Unauthorized Expense',
            'amount'       => 1000.00,
            'expense_date' => '2026-09-18',
        ]);
        $response->assertStatus(403);

        // Attempt update
        $response = $this->actingAs($this->admin2)->put(route('projects.expenses.update', [$this->project1, $expense]), [
            'item_name'    => 'Hacked Expense',
            'amount'       => 5000.00,
            'expense_date' => '2026-09-18',
        ]);
        $response->assertStatus(403);

        // Attempt delete
        $response = $this->actingAs($this->admin2)->delete(route('projects.expenses.destroy', [$this->project1, $expense]));
        $response->assertStatus(403);
    }

    public function test_superadmin_has_view_only_access_to_expenses()
    {
        $expense = ProjectExpense::create([
            'project_id'   => $this->project1->id,
            'item_name'    => 'Site Scaffolding',
            'amount'       => 12000.00,
            'expense_date' => '2026-09-12',
        ]);

        // Superadmin viewing project details succeeds
        $response = $this->actingAs($this->superAdmin)->get(route('projects.show', $this->project1));
        $response->assertStatus(200);
        $response->assertSee('Site Scaffolding');

        // Superadmin write actions should be forbidden (403)
        $response = $this->actingAs($this->superAdmin)->post(route('projects.expenses.store', $this->project1), [
            'item_name'    => 'SuperAdmin Expense',
            'amount'       => 1000.00,
            'expense_date' => '2026-09-18',
        ]);
        $response->assertStatus(403);
    }

    public function test_admin_can_view_project_expenses_index_page()
    {
        ProjectExpense::create([
            'project_id'   => $this->project1->id,
            'item_name'    => 'Site Scaffolding',
            'amount'       => 12000.00,
            'expense_date' => '2026-09-12',
        ]);

        $response = $this->actingAs($this->admin1)->get(route('projects.expenses.index'));
        $response->assertStatus(200);
        $response->assertSee('Project Expense Management');
        $response->assertSee('Site Scaffolding');
    }

    public function test_admin_can_store_general_expense()
    {
        $response = $this->actingAs($this->admin1)->post(route('projects.expenses.store-general'), [
            'project_id'   => $this->project1->id,
            'item_name'    => 'General Equipment Rental',
            'description'  => 'Generator for site operations',
            'amount'       => 18500.00,
            'expense_date' => '2026-09-18',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('project_expenses', [
            'project_id' => $this->project1->id,
            'item_name'  => 'General Equipment Rental',
            'amount'     => 18500.00,
        ]);
    }
}
