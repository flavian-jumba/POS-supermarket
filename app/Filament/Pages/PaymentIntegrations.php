<?php

namespace App\Filament\Pages;

use App\Models\MpesaIntegration;
use App\Mpesa\DarajaClient;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PaymentIntegrations extends Page
{
    protected string $view = 'filament.pages.payment-integrations';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Payment Integrations';

    protected static ?string $title = 'Payment Integrations';

    /**
     * @var array<string, mixed>
     */
    public array $data = [
        'environment' => 'sandbox',
        'consumer_key' => null,
        'consumer_secret' => null,
        'account_type' => 'paybill',
        'shortcode' => null,
        'passkey' => null,
    ];

    public function mount(): void
    {
        $integration = $this->integration();

        $this->form->fill([
            'environment' => $integration?->environment ?? 'sandbox',
            'consumer_key' => null,
            'consumer_secret' => null,
            'account_type' => $integration?->transaction_type === 'CustomerBuyGoodsOnline' ? 'till' : 'paybill',
            'shortcode' => $integration?->shortcode,
            'passkey' => null,
        ]);
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        $hasIntegration = filled($this->integration());

        return $schema
            ->components([
                Section::make('M-Pesa Credentials')
                    ->description('Configure the Daraja credentials used by this supermarket for STK Push payments.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('environment')
                                    ->label('Environment')
                                    ->options([
                                        'sandbox' => 'Sandbox',
                                        'production' => 'Production',
                                    ])
                                    ->native(false)
                                    ->required()
                                    ->helperText('Use Sandbox while testing. Switch to Production only when using live Daraja credentials.'),
                                Select::make('account_type')
                                    ->label('Account Type')
                                    ->options([
                                        'paybill' => 'Paybill',
                                        'till' => 'Till Number',
                                    ])
                                    ->native(false)
                                    ->required()
                                    ->helperText('Select the M-Pesa account type linked to your Daraja application.'),
                                TextInput::make('consumer_key')
                                    ->label('Consumer Key')
                                    ->placeholder($hasIntegration ? '••••••••••••••' : 'Enter your Daraja consumer key')
                                    ->helperText($hasIntegration ? 'Leave unchanged to keep the current key.' : 'Found in your Safaricom Daraja application credentials.')
                                    ->maxLength(2000)
                                    ->required(! $hasIntegration),
                                TextInput::make('shortcode')
                                    ->label('Business Shortcode')
                                    ->placeholder('e.g. 174379')
                                    ->helperText('Enter the Paybill or Till shortcode used for M-Pesa transactions.')
                                    ->inputMode('numeric')
                                    ->maxLength(30)
                                    ->required(),
                                TextInput::make('consumer_secret')
                                    ->label('Consumer Secret')
                                    ->password()
                                    ->revealable()
                                    ->placeholder($hasIntegration ? '••••••••••••••' : 'Enter your Daraja consumer secret')
                                    ->helperText($hasIntegration ? 'Leave unchanged to keep the current secret.' : 'Found in your Safaricom Daraja application credentials.')
                                    ->maxLength(2000)
                                    ->required(! $hasIntegration),
                                TextInput::make('passkey')
                                    ->label('Passkey')
                                    ->password()
                                    ->revealable()
                                    ->placeholder($hasIntegration ? '••••••••••••••' : 'Enter your Lipa na M-Pesa passkey')
                                    ->helperText($hasIntegration ? 'Leave unchanged to keep the current passkey. Required for M-Pesa Express / STK Push.' : 'Required for M-Pesa Express / STK Push.')
                                    ->maxLength(2000)
                                    ->required(! $hasIntegration),
                            ]),
                    ]),
            ]);
    }

    public static function canAccess(): bool
    {
        $tenant = Filament::getTenant();
        $user = auth()->user();

        return $tenant && $user?->organizationMemberships()
            ->whereBelongsTo($tenant)
            ->where('status', 'active')
            ->whereIn('role', ['owner', 'admin'])
            ->exists();
    }

    public function testConnection(DarajaClient $daraja): void
    {
        $integration = $this->saveIntegration(activate: false);
        $result = $daraja->testConnection($integration);

        if ($result['ok']) {
            Notification::make()
                ->title('M-Pesa connection verified successfully')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Connection failed')
                ->body('Check your credentials and environment.')
                ->danger()
                ->send();
        }
    }

    public function saveAndActivate(DarajaClient $daraja): void
    {
        $integration = $this->saveIntegration(activate: false);
        $result = $daraja->testConnection($integration);

        if (! $result['ok']) {
            Notification::make()
                ->title('Connection failed')
                ->body('Check your credentials and environment.')
                ->danger()
                ->send();

            return;
        }

        $integration->update(['is_active' => true]);

        Notification::make()
            ->title('M-Pesa connection verified successfully')
            ->body('The integration is active for this supermarket.')
            ->success()
            ->send();
    }

    public function disableMpesa(): void
    {
        $integration = $this->integration();

        if (! $integration) {
            return;
        }

        $integration->update(['is_active' => false]);

        Notification::make()
            ->title('M-Pesa disabled')
            ->body('Cashiers can no longer use M-Pesa for this supermarket until it is activated again.')
            ->success()
            ->send();
    }

    public function integration(): ?MpesaIntegration
    {
        return Filament::getTenant()?->mpesaIntegration()->first();
    }

    public function maskedAccount(): string
    {
        $integration = $this->integration();

        if (! $integration?->shortcode) {
            return 'Not configured';
        }

        return ($integration->transaction_type === 'CustomerBuyGoodsOnline' ? 'Till' : 'Paybill')
            .' •••'.substr($integration->shortcode, -3);
    }

    public function statusLabel(): string
    {
        $integration = $this->integration();

        if (! $integration) {
            return 'Not Connected';
        }

        return $integration->is_active && $integration->connection_status === 'verified'
            ? 'Connected'
            : 'Not Connected';
    }

    public function statusColor(): string
    {
        return $this->statusLabel() === 'Connected' ? 'success' : 'gray';
    }

    public function environmentLabel(): string
    {
        $environment = $this->integration()?->environment ?? ($this->data['environment'] ?? 'sandbox');

        return str($environment)->headline()->toString();
    }

    public function environmentColor(): string
    {
        $environment = $this->integration()?->environment ?? ($this->data['environment'] ?? 'sandbox');

        return $environment === 'production' ? 'success' : 'warning';
    }

    private function saveIntegration(bool $activate): MpesaIntegration
    {
        $tenant = Filament::getTenant();
        abort_unless($tenant, 403);

        $existing = $this->integration();
        $state = $this->form->getState();

        Validator::make(['data' => $state], [
            'data.environment' => ['required', Rule::in(['sandbox', 'production'])],
            'data.consumer_key' => [$existing ? 'nullable' : 'required', 'string', 'max:2000'],
            'data.consumer_secret' => [$existing ? 'nullable' : 'required', 'string', 'max:2000'],
            'data.account_type' => ['required', Rule::in(['paybill', 'till'])],
            'data.shortcode' => ['required', 'string', 'max:30'],
            'data.passkey' => [$existing ? 'nullable' : 'required', 'string', 'max:2000'],
        ], [], [
            'data.environment' => 'environment',
            'data.consumer_key' => 'consumer key',
            'data.consumer_secret' => 'consumer secret',
            'data.account_type' => 'account type',
            'data.shortcode' => 'business shortcode',
            'data.passkey' => 'passkey',
        ])->validate();

        $data = [
            'organization_id' => $tenant->id,
            'environment' => $state['environment'],
            'shortcode' => $state['shortcode'],
            'transaction_type' => $state['account_type'] === 'till' ? 'CustomerBuyGoodsOnline' : 'CustomerPayBillOnline',
        ];

        if ($activate || ! $existing) {
            $data['is_active'] = $activate;
        }

        if (filled($state['consumer_key'] ?? null)) {
            $data['consumer_key'] = $state['consumer_key'];
        }

        if (filled($state['consumer_secret'] ?? null)) {
            $data['consumer_secret'] = $state['consumer_secret'];
        }

        if (filled($state['passkey'] ?? null)) {
            $data['passkey'] = $state['passkey'];
        }

        return MpesaIntegration::query()->updateOrCreate(
            ['organization_id' => $tenant->id],
            $data,
        );
    }
}
