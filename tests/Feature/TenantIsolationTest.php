<?php

use App\Livewire\Pos\CashierPos;
use App\Models\Branch;
use App\Models\BranchMembership;
use App\Models\Category;
use App\Models\OrganizationMembership;
use App\Models\Product;
use App\Models\Register;
use App\Models\RegisterSession;
use App\Models\User;
use App\Support\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('organization a cannot access organization b pos data', function () {
    $cashier = User::factory()->create();
    $organizationA = createOrganization('tenant-a', now()->toDateTimeString());
    $organizationB = createOrganization('tenant-b', now()->toDateTimeString());
    OrganizationMembership::query()->create(['organization_id' => $organizationA->id, 'user_id' => $cashier->id, 'role' => 'cashier', 'status' => 'active']);

    $branchA = Branch::query()->create(['organization_id' => $organizationA->id, 'name' => 'A Branch', 'code' => 'A']);
    BranchMembership::query()->create(['branch_id' => $branchA->id, 'user_id' => $cashier->id, 'role' => 'cashier', 'status' => 'active']);
    $registerA = Register::query()->create(['branch_id' => $branchA->id, 'name' => 'Register A', 'code' => 'REG-A']);
    $sessionA = RegisterSession::query()->create(['register_id' => $registerA->id, 'user_id' => $cashier->id, 'opening_float' => 0, 'expected_cash' => 0, 'opened_at' => now(), 'status' => 'open']);

    $categoryB = Category::query()->create(['organization_id' => $organizationB->id, 'name' => 'B Category', 'slug' => 'b-category']);
    $productB = Product::query()->create([
        'organization_id' => $organizationB->id,
        'category_id' => $categoryB->id,
        'name' => 'Other Tenant Milk',
        'sku' => 'B-MILK',
        'selling_price' => 100,
    ]);

    $this->actingAs($cashier)
        ->withSession([
            Workspace::ORGANIZATION_SESSION_KEY => $organizationA->id,
            Workspace::BRANCH_SESSION_KEY => $branchA->id,
            Workspace::REGISTER_SESSION_KEY => $registerA->id,
            Workspace::REGISTER_SESSION_ID_KEY => $sessionA->id,
        ]);

    Livewire::test(CashierPos::class)
        ->call('addToCart', $productB->id)
        ->assertSet('cart', []);
});

test('cashier cannot start a shift on another organization register', function () {
    $cashier = User::factory()->create();
    $organizationA = createOrganization('shift-a', now()->toDateTimeString());
    $organizationB = createOrganization('shift-b', now()->toDateTimeString());
    OrganizationMembership::query()->create(['organization_id' => $organizationA->id, 'user_id' => $cashier->id, 'role' => 'cashier', 'status' => 'active']);

    $branchB = Branch::query()->create(['organization_id' => $organizationB->id, 'name' => 'B Branch', 'code' => 'B']);
    $registerB = Register::query()->create(['branch_id' => $branchB->id, 'name' => 'Register B', 'code' => 'REG-B']);

    $this->actingAs($cashier)
        ->withSession([Workspace::ORGANIZATION_SESSION_KEY => $organizationA->id])
        ->post(route('pos.session.store'), [
            'register_id' => $registerB->id,
            'opening_cash' => 100,
        ])
        ->assertNotFound();
});
