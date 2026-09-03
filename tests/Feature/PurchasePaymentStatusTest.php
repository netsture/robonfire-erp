<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Firm;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PurchasePaymentStatusTest extends TestCase
{
    use RefreshDatabase;

    private Firm $firm;
    private User $admin;
    private Supplier $supplier;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->firm = Firm::create(['name' => 'Firm One', 'slug' => 'firm-one', 'status' => 'active']);

        $this->admin = User::create([
            'name'     => 'Admin One',
            'email'    => 'admin1@firmone.com',
            'phone'    => '9111111111',
            'password' => Hash::make('password'),
            'role'     => 'admin',
            'firm_id'  => $this->firm->id,
            'status'   => 'active',
        ]);

        $this->supplier = Supplier::create([
            'firm_id'      => $this->firm->id,
            'company_name' => 'Supplier One',
            'email'        => 'sup@one.com',
            'phone'        => '9888888888',
            'gst_number'   => '27AAAAA0000A1Z5',
            'status'       => 'active',
        ]);

        $category = Category::create(['firm_id' => $this->firm->id, 'name' => 'Cat 1', 'slug' => 'cat-1']);
        $brand = Brand::create(['firm_id' => $this->firm->id, 'name' => 'Brand 1', 'slug' => 'brand-1']);

        $this->product = Product::create([
            'firm_id'        => $this->firm->id,
            'name'           => 'Product One',
            'hsn_code'       => '8424',
            'category_id'    => $category->id,
            'brand_id'       => $brand->id,
            'unit'           => 'Pcs',
            'cost_price'     => 100,
            'selling_price'  => 150,
            'tax_percent'    => 10,
            'alert_quantity' => 5,
            'stock_quantity' => 10,
            'status'         => 'active',
        ]);
    }

    public function test_can_create_purchase_with_pending_payment_status()
    {
        $response = $this->actingAs($this->admin)->post(route('purchases.store'), [
            'supplier_id'    => $this->supplier->id,
            'project_name'   => 'Test Pending Project',
            'purchase_date'  => '2026-08-26',
            'payment_status' => 'pending',
            'paid_amount'    => '0.00',
            'products'       => [
                [
                    'id'          => $this->product->id,
                    'qty'         => 2,
                    'cost'        => 100,
                    'tax_percent' => 10,
                ],
            ],
        ]);

        $response->assertRedirect(route('purchases.index'));

        $this->assertDatabaseHas('purchases', [
            'project_name'   => 'Test Pending Project',
            'grand_total'    => 220.00,
            'paid_amount'    => 0.00,
            'payment_status' => 'pending',
        ]);
    }

    public function test_can_update_payment_status_from_pending_to_paid()
    {
        $purchase = Purchase::create([
            'firm_id'         => $this->firm->id,
            'supplier_id'     => $this->supplier->id,
            'user_id'         => $this->admin->id,
            'project_name'    => 'Project to Pay',
            'invoice_number'  => 'PINV-1001',
            'purchase_date'   => '2026-08-26',
            'subtotal'        => 200,
            'tax_amount'      => 20,
            'grand_total'     => 220,
            'paid_amount'     => 0,
            'payment_status'  => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->post(route('purchases.update-payment-status', $purchase), [
            'payment_status' => 'paid',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('purchases', [
            'id'             => $purchase->id,
            'payment_status' => 'paid',
            'paid_amount'    => 220.00,
        ]);
    }
}
