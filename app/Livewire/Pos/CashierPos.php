<?php

namespace App\Livewire\Pos;

use App\Models\Category;
use App\Models\Product;
use App\Pos\PosContext;
use App\Support\Currency;
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

    public ?string $notice = null;

    public string $noticeType = 'info';

    public string $cashierName = 'Faith W.';

    public string $registerName = 'Register 04';

    public string $registerStatus = 'Register Open';

    public float $shiftBalance = 5450.00;

    public string $branchName = 'Main Branch';

    public string $organizationName = 'StanleyMat Supermarket';

    protected float $taxRate = 0.0;

    public ?int $organizationId = null;

    public ?int $branchId = null;

    public ?int $registerId = null;

    public ?int $registerSessionId = null;

    public function mount(PosContext $context): void
    {
        $this->hydrateContext($context);
        $this->seedDemoCartFromDatabase();
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
            ->orderByRaw("FIELD(sku, 'BRK-MILK-500', 'SUP-BREAD-400', 'COCA-2L') DESC")
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
        $this->paymentMethod = $method;
        $this->notice = null;
    }

    public function sendStkPush(): void
    {
        if (! preg_match('/^(?:\+254|0)(7\d{8}|1\d{8})$/', $this->customerPhone)) {
            $this->showNotice('Enter a valid Kenyan phone number.', 'error');

            return;
        }

        // M-Pesa Daraja integration is not wired up yet — this is a UI-only
        // acknowledgement so the flow can be demoed without a live payment.
        $this->showNotice('STK request ready — waiting to connect M-Pesa Daraja.', 'success');
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

        // Sale posting, stock deduction and receipt generation are not wired
        // up yet — this phase only demonstrates the UI/UX flow.
        $this->showNotice('Demo only — sale posting connects in the next phase.', 'success');
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
    }

    protected function seedDemoCartFromDatabase(): void
    {
        if (! empty($this->cart) || ! $this->organizationId) {
            return;
        }

        foreach ([
            'BRK-MILK-500' => 2,
            'SUP-BREAD-400' => 1,
            'COCA-2L' => 1,
        ] as $sku => $quantity) {
            $product = Product::query()
                ->with(['category', 'inventoryLevels' => fn ($query) => $query->when($this->branchId, fn ($query) => $query->where('branch_id', $this->branchId))])
                ->where('organization_id', $this->organizationId)
                ->where('sku', $sku)
                ->first();

            if (! $product) {
                continue;
            }

            $item = $this->productCardData($product);
            $this->cart[$item['id']] = [
                'id' => $item['id'],
                'sku' => $item['sku'],
                'name' => $item['name'],
                'price' => $item['price'],
                'qty' => $quantity,
                'illustration' => $item['illustration'],
                'trackInventory' => $item['trackInventory'],
                'quantityOnHand' => $item['quantityOnHand'],
            ];
        }
    }

    /**
     * @return array{id:int,sku:string,name:string,price:float,priceFrom:bool,category:string,illustration:string,quantityOnHand:float,trackInventory:bool,isAvailable:bool,stockLabel:string}
     */
    protected function productCardData(Product $product): array
    {
        $quantityOnHand = (float) $product->inventoryLevels->sum('quantity_on_hand');
        $trackInventory = (bool) $product->track_inventory;
        $isAvailable = ! $trackInventory || $quantityOnHand > 0;

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
            'isAvailable' => $isAvailable,
            'stockLabel' => $trackInventory ? number_format($quantityOnHand, 0).' in stock' : 'Available',
        ];
    }

    /**
     * @return array{id:int,sku:string,name:string,price:float,priceFrom:bool,category:string,illustration:string,quantityOnHand:float,trackInventory:bool,isAvailable:bool,stockLabel:string}|null
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
