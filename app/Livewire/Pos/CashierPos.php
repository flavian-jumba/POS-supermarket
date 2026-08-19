<?php

namespace App\Livewire\Pos;

use App\Filament\Support\PosResourceUi;
use App\Models\Category;
use App\Models\MpesaIntegration;
use App\Models\MpesaTransaction;
use App\Models\Product;
use App\Mpesa\MpesaPaymentService;
use App\Pos\CheckoutException;
use App\Pos\CheckoutService;
use App\Pos\PosContext;
use App\Support\Currency;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::pos')]
class CashierPos extends Component
{
    public string $search = '';

    public string $activeCategory = 'all';

    /** @var array<int, array{id:int,sku:string,name:string,price:float,qty:int,illustration:string,trackInventory:bool,quantityOnHand:float}> */
    public array $cart = [];

    public ?string $paymentMethod = null;

    public string $customerPhone = '';

    public string $mpesaStatus = 'idle';

    public ?string $mpesaReference = null;

    public ?int $mpesaTransactionId = null;

    public bool $mpesaAvailable = false;

    public ?string $notice = null;

    public string $noticeType = 'info';

    public string $cashierName = '—';

    public string $registerName = '—';

    public string $registerStatus = '—';

    public float $shiftBalance = 0.0;

    public string $branchName = '—';

    public string $organizationName = 'StanleyMat Supermarket';

    protected float $taxRate = 0.0;

    public ?int $organizationId = null;

    public ?int $branchId = null;

    public ?int $registerId = null;

    public ?int $registerSessionId = null;

    public function mount(PosContext $context): void
    {
        $this->hydrateContext($context);
    }

    /**
     * @return array<int, array{key:string,label:string,icon:string}>
     */
    #[Computed]
    public function categories(): array
    {
        $categories = [
            ['key' => 'all', 'label' => 'All Items', 'icon' => 'squares-2x2'],
        ];

        if (! $this->organizationId) {
            return $categories;
        }

        return [
            ...$categories,
            ...Category::query()
                ->where('organization_id', $this->organizationId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
                ->map(fn ($category): array => [
                    'key' => (string) $category->id,
                    'label' => $category->name,
                    'icon' => $this->categoryIcon($category->slug),
                ])
                ->all(),
        ];
    }

    /**
     * @return array<int, array{id:int,name:string,price:float,priceFrom:bool,category:string,illustration:string}>
     */
    #[Computed]
    public function products(): array
    {
        if (! $this->organizationId) {
            return [];
        }

        $search = trim($this->search);

        return Product::query()
            ->with([
                'category',
                'unit',
                'barcodes',
                'inventoryLevels' => fn ($query) => $query->when($this->branchId, fn ($query) => $query->where('branch_id', $this->branchId)),
            ])
            ->where('organization_id', $this->organizationId)
            ->where('is_active', true)
            ->when($this->activeCategory !== 'all', fn ($query) => $query->where('category_id', (int) $this->activeCategory))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhereHas('barcodes', fn ($query) => $query->where('barcode', $search));
                });
            })
            ->orderBy('name')
            ->limit(12)
            ->get()
            ->map(fn (Product $product): array => $this->productCardData($product))
            ->all();
    }

    #[Computed]
    public function cartCount(): int
    {
        return array_sum(array_column($this->cart, 'qty'));
    }

    #[Computed]
    public function subtotal(): float
    {
        return array_sum(array_map(
            static fn (array $item) => $item['price'] * $item['qty'],
            $this->cart
        ));
    }

    #[Computed]
    public function discountTotal(): float
    {
        return 0.0;
    }

    #[Computed]
    public function taxTotal(): float
    {
        return round($this->subtotal * $this->taxRate, 2);
    }

    #[Computed]
    public function total(): float
    {
        return $this->subtotal - $this->discountTotal + $this->taxTotal;
    }

    public function selectCategory(string $key): void
    {
        $this->activeCategory = $key;
    }

    public function addToCart(int $productId): void
    {
        $product = $this->findProductForCart($productId);

        if (! $product) {
            return;
        }

        if (! $product['isAvailable']) {
            $this->showNotice($product['name'].' is out of stock at this branch.', 'error');

            return;
        }

        if (isset($this->cart[$productId])) {
            if ($product['trackInventory'] && $this->cart[$productId]['qty'] >= $product['quantityOnHand']) {
                $this->showNotice('No more stock available for '.$product['name'].'.', 'error');

                return;
            }

            $this->cart[$productId]['qty']++;
            $this->showNotice($product['name'].' added to sale.', 'success');
            $this->dispatch('pos-refocus-scanner');

            return;
        }

        $this->cart[$productId] = [
            'id' => $product['id'],
            'sku' => $product['sku'],
            'name' => $product['name'],
            'price' => $product['price'],
            'qty' => 1,
            'illustration' => $product['illustration'],
            'trackInventory' => $product['trackInventory'],
            'quantityOnHand' => $product['quantityOnHand'],
        ];

        $this->showNotice($product['name'].' added to sale.', 'success');
        $this->dispatch('pos-refocus-scanner');
    }

    public function incrementItem(int $itemId): void
    {
        if (! isset($this->cart[$itemId])) {
            return;
        }

        $item = $this->cart[$itemId];

        if ($item['trackInventory'] && $item['qty'] >= $item['quantityOnHand']) {
            $this->showNotice('No more stock available for '.$item['name'].'.', 'error');

            return;
        }

        $this->cart[$itemId]['qty']++;
    }

    public function decrementItem(int $itemId): void
    {
        if (! isset($this->cart[$itemId])) {
            return;
        }

        $this->cart[$itemId]['qty']--;

        if ($this->cart[$itemId]['qty'] <= 0) {
            unset($this->cart[$itemId]);
        }
    }

    public function removeItem(int $itemId): void
    {
        unset($this->cart[$itemId]);
    }

    public function clearCart(): void
    {
        $this->cart = [];
        $this->paymentMethod = null;
    }

    public function selectPaymentMethod(string $method): void
    {
        if ($method === 'mpesa' && ! $this->mpesaAvailable) {
            $this->showNotice('M-Pesa is not configured for this supermarket.', 'error');

            return;
        }

        $this->paymentMethod = $method;
        $this->notice = null;
    }

    public function sendStkPush(MpesaPaymentService $mpesa): void
    {
        if (empty($this->cart)) {
            $this->showNotice('Add at least one item to the sale first.', 'error');

            return;
        }

        if (! $this->mpesaAvailable) {
            $this->showNotice('M-Pesa is not configured for this supermarket.', 'error');

            return;
        }

        if (! preg_match('/^(?:\+254|0)(7\d{8}|1\d{8})$/', $this->customerPhone)) {
            $this->showNotice('Enter a valid Kenyan phone number.', 'error');

            return;
        }

        $this->paymentMethod = 'mpesa';
        $this->mpesaStatus = 'sending';
        $this->mpesaReference = null;
        $this->mpesaTransactionId = null;

        try {
            $transaction = $mpesa->sendStkPush(
                context: [
                    'organizationId' => $this->organizationId,
                    'branchId' => $this->branchId,
                    'registerId' => $this->registerId,
                    'registerSessionId' => $this->registerSessionId,
                    'cashierId' => Auth::id(),
                ],
                cartItems: array_map(fn (array $item): array => ['id' => $item['id'], 'qty' => $item['qty']], $this->cart),
                phone: $this->customerPhone,
            );
        } catch (CheckoutException $exception) {
            $this->mpesaStatus = 'failed';
            $this->showNotice($exception->getMessage(), 'error');

            return;
        }

        $this->mpesaTransactionId = $transaction->id;
        $this->mpesaReference = $transaction->checkout_request_id;
        $this->mpesaStatus = 'waiting';
        $this->showNotice('STK push sent. Waiting for customer to enter M-Pesa PIN.', 'info');
    }

    public function checkMpesaStatus(): void
    {
        if (! $this->mpesaTransactionId) {
            return;
        }

        $transaction = MpesaTransaction::query()
            ->whereKey($this->mpesaTransactionId)
            ->where('organization_id', $this->organizationId)
            ->first();

        if (! $transaction) {
            return;
        }

        $this->mpesaStatus = $transaction->status;
        $this->mpesaReference = $transaction->mpesa_receipt_number ?? $transaction->checkout_request_id;

        if ($transaction->status === 'successful') {
            $this->showNotice('M-Pesa payment received.', 'success');
            $this->cart = [];
            unset($this->products);
        }

        if (in_array($transaction->status, ['failed', 'cancelled', 'timeout'], true)) {
            $this->showNotice('M-Pesa payment was not completed.', 'error');
        }
    }

    public function completeSale(): void
    {
        if (empty($this->cart)) {
            $this->showNotice('Add at least one item to the sale first.', 'error');

            return;
        }

        if ($this->paymentMethod === null) {
            $this->showNotice('Select a payment method first.', 'error');

            return;
        }

        if ($this->paymentMethod === 'mpesa') {
            $this->checkMpesaStatus();

            if ($this->mpesaStatus === 'successful') {
                $this->cart = [];
                $this->paymentMethod = null;
                $this->customerPhone = '';
                $this->mpesaStatus = 'idle';
                $this->mpesaReference = null;
                $this->mpesaTransactionId = null;
                $this->showNotice('Sale completed.', 'success');

                return;
            }

            $this->showNotice('Waiting for M-Pesa payment confirmation.', 'error');

            return;
        }

        try {
            app(CheckoutService::class)->complete(
                context: [
                    'organizationId' => $this->organizationId,
                    'branchId' => $this->branchId,
                    'registerId' => $this->registerId,
                    'registerSessionId' => $this->registerSessionId,
                    'cashierId' => Auth::id(),
                ],
                cartItems: array_map(fn (array $item): array => ['id' => $item['id'], 'qty' => $item['qty']], $this->cart),
                paymentMethod: $this->paymentMethod,
                paymentReference: $this->paymentMethod === 'mpesa' ? $this->mpesaReference : null,
            );
        } catch (CheckoutException $exception) {
            $this->showNotice($exception->getMessage(), 'error');

            return;
        }

        $this->cart = [];
        $this->paymentMethod = null;
        $this->customerPhone = '';
        $this->mpesaStatus = 'idle';
        $this->mpesaReference = null;
        $this->mpesaTransactionId = null;
        unset($this->products);

        $this->showNotice('Sale completed.', 'success');
        $this->dispatch('pos-refocus-scanner');
    }

    public function placeholderAction(string $label): void
    {
        $this->showNotice("{$label} — coming next.", 'info');
    }

    public function formatCurrency(float|int|string $amount): string
    {
        return Currency::format($amount);
    }

    protected function showNotice(string $message, string $type = 'info'): void
    {
        $this->notice = $message;
        $this->noticeType = $type;
    }

    protected function hydrateContext(PosContext $context): void
    {
        $resolved = $context->resolve();

        $this->organizationId = $resolved['organization']?->id;
        $this->branchId = $resolved['branch']?->id;
        $this->registerId = $resolved['register']?->id;
        $this->registerSessionId = $resolved['session']?->id;

        $this->cashierName = $resolved['cashier']?->name ?? 'No cashier';
        $this->organizationName = $resolved['organization']?->name ?? 'No organization';
        $this->branchName = $resolved['branch']?->name ?? 'No branch';
        $this->registerName = $resolved['register']?->name ?? 'No register';
        $this->registerStatus = $resolved['session'] ? 'Register Open' : 'No Open Session';
        $this->shiftBalance = (float) ($resolved['session']?->expected_cash ?? $resolved['session']?->opening_float ?? 0);
        $this->mpesaAvailable = $this->organizationId
            ? MpesaIntegration::query()
                ->where('organization_id', $this->organizationId)
                ->where('is_active', true)
                ->where('connection_status', 'verified')
                ->exists()
            : false;

        if (! $this->organizationId || ! $this->branchId || ! $this->registerId || ! $this->registerSessionId) {
            $this->redirectRoute('pos.session', navigate: false);
        }
    }

    /**
     * @return array{id:int,sku:string,name:string,price:float,priceFrom:bool,category:string,illustration:string,quantityOnHand:float,trackInventory:bool,isAvailable:bool,stockLabel:string,stockState:string}
     */
    protected function productCardData(Product $product): array
    {
        $quantityOnHand = (float) $product->inventoryLevels->sum('quantity_on_hand');
        $trackInventory = (bool) $product->track_inventory;

        $stockStatus = PosResourceUi::stockStatus($product);
        $stockState = match ($stockStatus) {
            'Out of Stock' => 'out_of_stock',
            'Low Stock' => 'low_stock',
            default => 'in_stock',
        };

        return [
            'id' => $product->id,
            'sku' => $product->sku,
            'name' => $product->name,
            'price' => (float) $product->selling_price,
            'priceFrom' => false,
            'category' => (string) $product->category_id,
            'illustration' => $this->productIllustration($product),
            'quantityOnHand' => $quantityOnHand,
            'trackInventory' => $trackInventory,
            'isAvailable' => $stockState !== 'out_of_stock',
            'stockLabel' => $trackInventory ? number_format($quantityOnHand, 0).' in stock' : 'Available',
            'stockState' => $trackInventory ? $stockState : 'in_stock',
        ];
    }

    /**
     * @return array{id:int,sku:string,name:string,price:float,priceFrom:bool,category:string,illustration:string,quantityOnHand:float,trackInventory:bool,isAvailable:bool,stockLabel:string,stockState:string}|null
     */
    protected function findProductForCart(int $productId): ?array
    {
        if (! $this->organizationId) {
            return null;
        }

        $product = Product::query()
            ->with(['category', 'inventoryLevels' => fn ($query) => $query->when($this->branchId, fn ($query) => $query->where('branch_id', $this->branchId))])
            ->where('organization_id', $this->organizationId)
            ->where('is_active', true)
            ->find($productId);

        return $product ? $this->productCardData($product) : null;
    }

    protected function productIllustration(Product $product): string
    {
        $name = Str::lower($product->name);

        return match (true) {
            Str::contains($name, 'milk') => 'milk',
            Str::contains($name, 'bread') => 'bread',
            Str::contains($name, ['coca', 'juice', 'water']) => 'soda',
            Str::contains($name, 'crisp') => 'chips',
            Str::contains($name, ['cleaner', 'oil']) => 'spray',
            default => $this->categoryIllustration($product->category?->slug),
        };
    }

    protected function categoryIllustration(?string $slug): string
    {
        return match ($slug) {
            'fresh-produce' => 'veggies',
            'bakery' => 'bread',
            'beverages' => 'soda',
            'dairy' => 'milk',
            'snacks' => 'chips',
            'home-care' => 'spray',
            default => 'milk',
        };
    }

    protected function categoryIcon(?string $slug): string
    {
        return match ($slug) {
            'fresh-produce' => 'sun',
            'bakery' => 'cake',
            'beverages' => 'beaker',
            'dairy' => 'shopping-bag',
            'snacks' => 'fire',
            'home-care' => 'sparkles',
            default => 'squares-2x2',
        };
    }

    public function render()
    {
        return view('livewire.pos.cashier-pos');
    }
}
