<?php

use App\Models\Branch;
use App\Models\BranchMembership;
use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\Register;
use App\Models\RegisterSession;
use App\Models\User;
use App\Support\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createMembership(User $user, Organization $organization, string $role = 'owner'): OrganizationMembership
{
    return OrganizationMembership::query()->create([
        'organization_id' => $organization->id,
        'user_id' => $user->id,
        'role' => $role,
        'status' => 'active',
    ]);
}

function createOrganization(string $slug = 'acme', ?string $onboardedAt = null): Organization
{
    return Organization::query()->create([
        'name' => str($slug)->headline().' Supermarket',
        'slug' => $slug,
        'email' => "{$slug}@example.test",
        'phone' => '+254700000001',
        'status' => 'active',
        'onboarding_completed_at' => $onboardedAt,
    ]);
}

test('supermarket registration creates user organization and owner membership', function () {
    $this->post(route('register.store'), [
        'supermarket_name' => 'Fresh Foods',
        'name' => 'Amina Owner',
        'email' => 'amina@example.test',
        'phone' => '+254700000010',
        'password' => 'Password1',
        'password_confirmation' => 'Password1',
    ])->assertRedirect(route('onboarding.index'));

    $user = User::query()->where('email', 'amina@example.test')->firstOrFail();
    $organization = Organization::query()->where('slug', 'fresh-foods')->firstOrFail();

    expect($organization->onboarding_completed_at)->toBeNull()
        ->and($user->organizationMemberships()->whereBelongsTo($organization)->where('role', 'owner')->where('status', 'active')->exists())->toBeTrue();
});

test('new organization redirects to onboarding and creates branch and register', function () {
    $user = User::factory()->create();
    $organization = createOrganization('newmart');
    createMembership($user, $organization);

    $this->actingAs($user)
        ->withSession([Workspace::ORGANIZATION_SESSION_KEY => $organization->id])
        ->get('/admin/'.$organization->slug)
        ->assertRedirect(route('onboarding.index'));

    $this->post(route('onboarding.branch'), [
        'name' => 'Main Branch',
        'code' => 'MAIN',
        'address' => 'Nairobi',
        'phone' => '+254700000011',
    ])->assertRedirect(route('onboarding.index'));

    $branch = Branch::query()->whereBelongsTo($organization)->where('code', 'MAIN')->firstOrFail();
    expect(BranchMembership::query()->whereBelongsTo($branch)->whereBelongsTo($user)->exists())->toBeTrue();

    $this->post(route('onboarding.register'), [
        'name' => 'Register 01',
        'code' => 'REG-01',
    ])->assertRedirect(route('onboarding.index'));

    expect(Register::query()->whereBelongsTo($branch)->where('code', 'REG-01')->exists())->toBeTrue();
});

test('onboarding completion sends owner to tenant admin dashboard', function () {
    $user = User::factory()->create();
    $organization = createOrganization('owner-mart');
    createMembership($user, $organization);
    $branch = Branch::query()->create(['organization_id' => $organization->id, 'name' => 'Main Branch', 'code' => 'MAIN']);
    Register::query()->create(['branch_id' => $branch->id, 'name' => 'Register 01', 'code' => 'REG-01']);

    $this->actingAs($user)
        ->withSession([Workspace::ORGANIZATION_SESSION_KEY => $organization->id])
        ->post(route('onboarding.complete'))
        ->assertRedirect('/admin/'.$organization->slug);

    expect($organization->fresh()->onboarding_completed_at)->not->toBeNull();
});

test('cashier redirects to register flow and resumes active session', function () {
    $cashier = User::factory()->create(['email' => 'cashier@example.test']);
    $organization = createOrganization('cashmart', now()->toDateTimeString());
    createMembership($cashier, $organization, 'cashier');
    $branch = Branch::query()->create(['organization_id' => $organization->id, 'name' => 'Main Branch', 'code' => 'MAIN']);
    BranchMembership::query()->create(['branch_id' => $branch->id, 'user_id' => $cashier->id, 'role' => 'cashier', 'status' => 'active']);
    $register = Register::query()->create(['branch_id' => $branch->id, 'name' => 'Register 01', 'code' => 'REG-01']);
    RegisterSession::query()->create([
        'register_id' => $register->id,
        'user_id' => $cashier->id,
        'opening_float' => 100,
        'expected_cash' => 100,
        'opened_at' => now(),
        'status' => 'open',
    ]);

    $this->post(route('login.store'), [
        'email' => 'cashier@example.test',
        'password' => 'password',
    ])->assertRedirect(route('pos.session'));

    $this->get(route('pos.session'))
        ->assertRedirect(route('pos'))
        ->assertSessionHas(Workspace::REGISTER_SESSION_KEY, $register->id);
});

test('Get Started button links to registration page', function () {
    $this->get('/')
        ->assertSee('Get Started')
        ->assertSee(route('register'));
});

test('multiple organizations show workspace selector and single organization skips it', function () {
    $singleUser = User::factory()->create(['email' => 'single@example.test']);
    $singleOrganization = createOrganization('single', now()->toDateTimeString());
    createMembership($singleUser, $singleOrganization);

    $this->post(route('login.store'), [
        'email' => 'single@example.test',
        'password' => 'password',
    ])->assertRedirect('/admin/'.$singleOrganization->slug);

    auth()->logout();

    $multiUser = User::factory()->create(['email' => 'multi@example.test']);
    createMembership($multiUser, createOrganization('first', now()->toDateTimeString()));
    createMembership($multiUser, createOrganization('second', now()->toDateTimeString()));

    $this->post(route('login.store'), [
        'email' => 'multi@example.test',
        'password' => 'password',
    ])->assertRedirect(route('workspace.index'));

    $this->get(route('workspace.index'))->assertSee('Choose Workspace')->assertSee('First Supermarket')->assertSee('Second Supermarket');
});
