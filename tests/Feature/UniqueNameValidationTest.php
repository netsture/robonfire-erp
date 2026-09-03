<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Firm;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UniqueNameValidationTest extends TestCase
{
    use RefreshDatabase;

    private Firm $firm1;
    private Firm $firm2;
    private User $admin1;
    private User $admin2;
    private Category $category1;
    private Brand $brand1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->firm1 = Firm::create(['name' => 'Firm One', 'slug' => 'firm-one', 'status' => 'active']);
        $this->firm2 = Firm::create(['name' => 'Firm Two', 'slug' => 'firm-two', 'status' => 'active']);

        $this->admin1 = User::create([
            'name'     => 'Admin One',
            'email'    => 'admin1@firmone.com',
            'phone'    => '9111111111',
            'password' => Hash::make('password'),
            'role'     => 'admin',
            'firm_id'  => $this->firm1->id,
            'status'   => 'active',
        ]);

        $this->admin2 = User::create([
            'name'     => 'Admin Two',
            'email'    => 'admin2@firmtwo.com',
            'phone'    => '9222222222',
            'password' => Hash::make('password'),
            'role'     => 'admin',
            'firm_id'  => $this->firm2->id,
            'status'   => 'active',
        ]);

        $this->category1 = Category::create([
            'firm_id' => $this->firm1->id,
            'name'    => 'Safety Gear',
            'slug'    => 'safety-gear',
        ]);

        $this->brand1 = Brand::create([
            'firm_id' => $this->firm1->id,
            'name'    => 'Minimax',
            'slug'    => 'minimax',
        ]);
    }

    public function test_category_name_must_be_unique_within_same_firm()
    {
        // Try creating duplicate category in Firm 1
        $response = $this->actingAs($this->admin1)->post(route('categories.store'), [
            'name' => 'Safety Gear',
        ]);
        $response->assertSessionHasErrors(['name']);

        // Firm 2 can create category with the same name
        $response = $this->actingAs($this->admin2)->post(route('categories.store'), [
            'name' => 'Safety Gear',
        ]);
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('categories', [
            'firm_id' => $this->firm2->id,
            'name'    => 'Safety Gear',
        ]);
    }

    public function test_brand_name_must_be_unique_within_same_firm()
    {
        // Try creating duplicate brand in Firm 1
        $response = $this->actingAs($this->admin1)->post(route('brands.store'), [
            'name' => 'Minimax',
        ]);
        $response->assertSessionHasErrors(['name']);

        // Firm 2 can create brand with the same name
        $response = $this->actingAs($this->admin2)->post(route('brands.store'), [
            'name' => 'Minimax',
        ]);
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('brands', [
            'firm_id' => $this->firm2->id,
            'name'    => 'Minimax',
        ]);
    }

    public function test_product_title_category_brand_combination_must_be_unique_within_same_firm()
    {
        $categoryAlt = Category::create([
            'firm_id' => $this->firm1->id,
            'name'    => 'Alternative Gear',
            'slug'    => 'alt-gear',
        ]);

        Product::create([
            'firm_id'        => $this->firm1->id,
            'name'           => 'Fire Extinguisher 5kg',
            'hsn_code'       => '8424',
            'category_id'    => $this->category1->id,
            'brand_id'       => $this->brand1->id,
            'unit'           => 'Pcs',
            'tax_percent'    => 18,
            'alert_quantity' => 5,
            'status'         => 'active',
        ]);

        // Same title + same category + same brand in Firm 1 => FAILS
        $response = $this->actingAs($this->admin1)->post(route('products.store'), [
            'name'           => 'Fire Extinguisher 5kg',
            'hsn_code'       => '8424',
            'category_id'    => $this->category1->id,
            'brand_id'       => $this->brand1->id,
            'unit'           => 'Pcs',
            'tax_percent'    => 18,
            'alert_quantity' => 5,
            'status'         => 'active',
        ]);
        $response->assertSessionHasErrors(['name']);

        // Same title + different category in Firm 1 => PASSES
        $response = $this->actingAs($this->admin1)->post(route('products.store'), [
            'name'           => 'Fire Extinguisher 5kg',
            'hsn_code'       => '8424',
            'category_id'    => $categoryAlt->id,
            'brand_id'       => $this->brand1->id,
            'unit'           => 'Pcs',
            'tax_percent'    => 18,
            'alert_quantity' => 5,
            'status'         => 'active',
        ]);
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('products', [
            'firm_id'     => $this->firm1->id,
            'name'        => 'Fire Extinguisher 5kg',
            'category_id' => $categoryAlt->id,
        ]);
    }
}
