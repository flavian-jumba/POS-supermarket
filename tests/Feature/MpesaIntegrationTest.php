<?php

use App\Livewire\Pos\CashierPos;
use App\Models\Branch;
use App\Models\BranchMembership;
use App\Models\InventoryLevel;
use App\Models\MpesaIntegration;
use App\Models\MpesaTransaction;
use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Register;
use App\Models\RegisterSession;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\User;
use App\Mpesa\DarajaClient;
use App\Support\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function mpesaOrganization(string $slug): Organization
{
    return Organization::query()->create([
        'name' => str($slug)->headline().' Supermarket',
        'slug' => $slug,
        'email' => "{$slug}@example.test",
        'phone' => '+254700000001',
        'status' => 'active',
        'onboarding_completed_at' => now(),
    ]);
}

function mpesaIntegration(Organization $organization, array $overrides = []): MpesaIntegration
{
    return MpesaIntegration::query()->create([
        'organization_id' => $organization->id,
        'environment' => $overrides['environment'] ?? 'sandbox',
        'consumer_key' => $overrides['consumer_key'] ?? 'key-'.$organization->slug,
        'consumer_secret' => $overrides['consumer_secret'] ?? 'secret-'.$organization->slug,
        'shortcode' => $overrides['shortcode'] ?? '174379',
        'passkey' => $overrides['passkey'] ?? 'passkey-'.$organization->slug,
        'transaction_type' => $overrides['transaction_type'] ?? 'CustomerPayBillOnline',
        'is_active' => $overrides['is_active'] ?? true,
        'connection_status' => $overrides['connection_status'] ?? 'verified',
    ]);
}

function mpesaPosFixture(Organization $organization, ?User $cashier = null): array
{
    $cashier ??= User::factory()->create();

    OrganizationMembership::query()->create([
        'organization_id' => $organization->id,
        'user_id' => $cashier->id,
        'role' => 'cashier',
        'status' => 'active',
    ]);

    $branch = Branch::query()->create([
        'organization_id' => $organization->id,
        'name' => 'Main Branch',
        'code' => 'MAIN',
    ]);

    BranchMembership::query()->create([
        'branch_id' => $branch->id,
        'user_id' => $cashier->id,
        'role' => 'cashier',
        'status' => 'active',
    ]);

    $register = Register::query()->create([
        'branch_id' => $branch->id,
        'name' => 'Register 01',
        'code' => 'REG-01',
    ]);

    $session = RegisterSession::query()->create([
        'register_id' => $register->id,
        'user_id' => $cashier->id,
        'opening_float' => 0,
        'expected_cash' => 0,
        'opened_at' => now(),
        'status' => 'open',
    ]);

    $product = Product::query()->create([
        'organization_id' => $organization->id,
        'name' => 'Milk 500ml',
        'sku' => 'MILK-'.$organization->slug,
        'selling_price' => 100,
        'cost_price' => 70,
        'track_inventory' => true,
    ]);

    InventoryLevel::query()->create([
        'branch_id' => $branch->id,
        'product_id' => $product->id,
        'quantity_on_hand' => 10,
        'reorder_level' => 1,
    ]);

    return compact('cashier', 'branch', 'register', 'session', 'product');
}

function fakeSuccessfulDaraja(string $checkoutRequestId = 'ws_CO_123'): void
{
    Http::preventStrayRequests();
    Http::fake([
        'https://sandbox.safaricom.co.ke/oauth/v1/generate*' => Http::response(['access_token' => 'token', 'expires_in' => '3599']),
        'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest' => Http::response([
            'MerchantRequestID' => 'mr_123',
            'CheckoutRequestID' => $checkoutRequestId,
            'ResponseCode' => '0',
            'ResponseDescription' => 'Success. Request accepted for processing',
            'CustomerMessage' => 'Success. Request accepted for processing',
        ]),
    ]);
}

test('organization has independent encrypted mpesa integration', function () {
    $organizationA = mpesaOrganization('mpesa-a');
    $organizationB = mpesaOrganization('mpesa-b');

    $integrationA = mpesaIntegration($organizationA, ['consumer_key' => 'key-a']);
    $integrationB = mpesaIntegration($organizationB, ['consumer_key' => 'key-b']);

    expect($organizationA->mpesaIntegration->is($integrationA))->toBeTrue()
        ->and($organizationB->mpesaIntegration->is($integrationB))->toBeTrue()
        ->and($integrationA->consumer_key)->toBe('key-a')
        ->and(DB::table('mpesa_integrations')->whereKey($integrationA->id)->value('consumer_key'))->not->toBe('key-a');
});

test('invalid credentials fail connection test and are not activated', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://sandbox.safaricom.co.ke/oauth/v1/generate*' => Http::response(['error' => 'invalid'], 401),
    ]);

    $integration = mpesaIntegration(mpesaOrganization('invalid'), [
        'is_active' => true,
        'connection_status' => 'verified',
    ]);

    $result = app(DarajaClient::class)->testConnection($integration);

    expect($result['ok'])->toBeFalse()
        ->and($integration->fresh()->is_active)->toBeFalse()
        ->and($integration->fresh()->connection_status)->toBe('failed');
});

test('tenant a cannot access tenant b integration page', function () {
    $user = User::factory()->create();
    $organizationA = mpesaOrganization('filament-a');
    $organizationB = mpesaOrganization('filament-b');
    OrganizationMembership::query()->create(['organization_id' => $organizationA->id, 'user_id' => $user->id, 'role' => 'owner', 'status' => 'active']);
    mpesaIntegration($organizationB);

    $this->actingAs($user)
        ->get('/admin/'.$organizationB->slug.'/payment-integrations')
        ->assertNotFound();
});

test('payment integrations page renders polished masked mpesa form', function () {
    $user = User::factory()->create();
    $organization = mpesaOrganization('filament-ui');
    OrganizationMembership::query()->create(['organization_id' => $organization->id, 'user_id' => $user->id, 'role' => 'owner', 'status' => 'active']);
    mpesaIntegration($organization, [
        'consumer_key' => 'visible-key-never-rendered',
        'consumer_secret' => 'visible-secret-never-rendered',
        'passkey' => 'visible-passkey-never-rendered',
        'shortcode' => '001456',
    ]);

    $this->actingAs($user)
        ->get('/admin/'.$organization->slug.'/payment-integrations')
        ->assertSuccessful()
        ->assertSee('M-PESA Integration')
        ->assertSee('M-Pesa Credentials')
        ->assertSee('Business Shortcode')
        ->assertSee('Leave unchanged to keep the current secret')
        ->assertSee('Paybill •••456')
        ->assertDontSee('visible-key-never-rendered')
        ->assertDontSee('visible-secret-never-rendered')
        ->assertDontSee('visible-passkey-never-rendered');
});

test('pos resolves correct organization integration and stk creates pending records', function () {
    fakeSuccessfulDaraja('checkout-a');

    $organizationA = mpesaOrganization('pos-a');
    $organizationB = mpesaOrganization('pos-b');
    mpesaIntegration($organizationA, ['shortcode' => '111111']);
    mpesaIntegration($organizationB, ['shortcode' => '222222']);
    $fixture = mpesaPosFixture($organizationA);

    $this->actingAs($fixture['cashier'])
        ->withSession([
            Workspace::ORGANIZATION_SESSION_KEY => $organizationA->id,
            Workspace::BRANCH_SESSION_KEY => $fixture['branch']->id,
            Workspace::REGISTER_SESSION_KEY => $fixture['register']->id,
            Workspace::REGISTER_SESSION_ID_KEY => $fixture['session']->id,
        ]);

    Livewire::test(CashierPos::class)
        ->call('addToCart', $fixture['product']->id)
        ->set('customerPhone', '0712345678')
        ->call('sendStkPush')
        ->assertSet('mpesaStatus', 'waiting');

    $sale = Sale::query()->whereBelongsTo($organizationA)->firstOrFail();
    $payment = Payment::query()->where('sale_id', $sale->id)->firstOrFail();
    $transaction = MpesaTransaction::query()->whereBelongsTo($organizationA)->firstOrFail();

    expect($sale->payment_status)->toBe('unpaid')
        ->and($sale->status)->toBe('pending')
        ->and($payment->status)->toBe('pending')
        ->and($transaction->status)->toBe('processing')
        ->and($transaction->checkout_request_id)->toBe('checkout-a');

    Http::assertSent(fn ($request) => $request->url() === 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest'
        && $request['BusinessShortCode'] === '111111');
});

test('successful callback completes correct payment and is idempotent', function () {
    fakeSuccessfulDaraja('checkout-success');

    $organization = mpesaOrganization('callback-success');
    mpesaIntegration($organization);
    $fixture = mpesaPosFixture($organization);

    $this->actingAs($fixture['cashier'])
        ->withSession([
            Workspace::ORGANIZATION_SESSION_KEY => $organization->id,
            Workspace::BRANCH_SESSION_KEY => $fixture['branch']->id,
            Workspace::REGISTER_SESSION_KEY => $fixture['register']->id,
            Workspace::REGISTER_SESSION_ID_KEY => $fixture['session']->id,
        ]);

    Livewire::test(CashierPos::class)
        ->call('addToCart', $fixture['product']->id)
        ->set('customerPhone', '0712345678')
        ->call('sendStkPush');

    $payload = [
        'Body' => [
            'stkCallback' => [
                'MerchantRequestID' => 'mr_123',
                'CheckoutRequestID' => 'checkout-success',
                'ResultCode' => 0,
                'ResultDesc' => 'The service request is processed successfully.',
                'CallbackMetadata' => [
                    'Item' => [
                        ['Name' => 'Amount', 'Value' => 100],
                        ['Name' => 'MpesaReceiptNumber', 'Value' => 'RCP123'],
                    ],
                ],
            ],
        ],
    ];

    $this->postJson('/api/mpesa/callback', $payload)->assertSuccessful();
    $this->postJson('/api/mpesa/callback', $payload)->assertSuccessful();

    $sale = Sale::query()->firstOrFail();
    $payment = Payment::query()->firstOrFail();
    $transaction = MpesaTransaction::query()->firstOrFail();

    expect($sale->fresh()->payment_status)->toBe('paid')
        ->and($sale->fresh()->status)->toBe('completed')
        ->and($payment->fresh()->status)->toBe('successful')
        ->and($payment->fresh()->reference)->toBe('RCP123')
        ->and($transaction->fresh()->status)->toBe('successful')
        ->and(StockMovement::query()->where('reference_id', $sale->id)->count())->toBe(1);
});

test('failed callback does not complete sale', function () {
    fakeSuccessfulDaraja('checkout-failed');

    $organization = mpesaOrganization('callback-failed');
    mpesaIntegration($organization);
    $fixture = mpesaPosFixture($organization);

    $this->actingAs($fixture['cashier'])
        ->withSession([
            Workspace::ORGANIZATION_SESSION_KEY => $organization->id,
            Workspace::BRANCH_SESSION_KEY => $fixture['branch']->id,
            Workspace::REGISTER_SESSION_KEY => $fixture['register']->id,
            Workspace::REGISTER_SESSION_ID_KEY => $fixture['session']->id,
        ]);

    Livewire::test(CashierPos::class)
        ->call('addToCart', $fixture['product']->id)
        ->set('customerPhone', '0712345678')
        ->call('sendStkPush');

    $this->postJson('/api/mpesa/callback', [
        'Body' => [
            'stkCallback' => [
                'CheckoutRequestID' => 'checkout-failed',
                'ResultCode' => 1032,
                'ResultDesc' => 'Request cancelled by user.',
            ],
        ],
    ])->assertSuccessful();

    expect(Sale::query()->firstOrFail()->payment_status)->toBe('unpaid')
        ->and(Payment::query()->firstOrFail()->status)->toBe('cancelled')
        ->and(MpesaTransaction::query()->firstOrFail()->status)->toBe('cancelled')
        ->and(StockMovement::query()->count())->toBe(0);
});

test('different organizations use different credentials', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://sandbox.safaricom.co.ke/oauth/v1/generate*' => Http::response(['access_token' => 'token', 'expires_in' => '3599']),
    ]);

    $integrationA = mpesaIntegration(mpesaOrganization('credential-a'), ['consumer_key' => 'key-a', 'consumer_secret' => 'secret-a']);
    $integrationB = mpesaIntegration(mpesaOrganization('credential-b'), ['consumer_key' => 'key-b', 'consumer_secret' => 'secret-b']);

    app(DarajaClient::class)->accessToken($integrationA, forceRefresh: true);
    app(DarajaClient::class)->accessToken($integrationB, forceRefresh: true);

    Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Basic '.base64_encode('key-a:secret-a')));
    Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Basic '.base64_encode('key-b:secret-b')));
});

test('mpesa unavailable when integration inactive', function () {
    Http::preventStrayRequests();
    Http::fake();

    $organization = mpesaOrganization('inactive');
    mpesaIntegration($organization, ['is_active' => false]);
    $fixture = mpesaPosFixture($organization);

    $this->actingAs($fixture['cashier'])
        ->withSession([
            Workspace::ORGANIZATION_SESSION_KEY => $organization->id,
            Workspace::BRANCH_SESSION_KEY => $fixture['branch']->id,
            Workspace::REGISTER_SESSION_KEY => $fixture['register']->id,
            Workspace::REGISTER_SESSION_ID_KEY => $fixture['session']->id,
        ]);

    Livewire::test(CashierPos::class)
        ->call('addToCart', $fixture['product']->id)
        ->set('customerPhone', '0712345678')
        ->call('sendStkPush')
        ->assertSet('mpesaStatus', 'idle');

    expect(MpesaTransaction::query()->count())->toBe(0);
});
