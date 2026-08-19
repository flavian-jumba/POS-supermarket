<?php

namespace App\Filament\Widgets;

use App\Models\Branch;
use App\Models\InventoryLevel;
use App\Models\Product;
use App\Models\Register;
use App\Models\Sale;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

class SetupChecklist extends Widget
{
    protected string $view = 'filament.widgets.setup-checklist';

    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<int, array{label:string,complete:bool}>
     */
    public function getChecklist(): array
    {
        $organization = Filament::getTenant();

        if (! $organization) {
            return [];
        }

        $branchIds = Branch::query()
            ->whereBelongsTo($organization)
            ->pluck('id');

        return [
            [
                'label' => 'Create supermarket',
                'complete' => $organization->exists,
            ],
            [
                'label' => 'Create first branch',
                'complete' => $branchIds->isNotEmpty(),
            ],
            [
                'label' => 'Create first register',
                'complete' => Register::query()->whereIn('branch_id', $branchIds)->exists(),
            ],
            [
                'label' => 'Add first product',
                'complete' => Product::query()->whereBelongsTo($organization)->exists(),
            ],
            [
                'label' => 'Add opening stock',
                'complete' => InventoryLevel::query()->whereIn('branch_id', $branchIds)->where('quantity_on_hand', '>', 0)->exists(),
            ],
            [
                'label' => 'Add cashier',
                'complete' => User::query()
                    ->whereHas('organizationMemberships', fn ($query) => $query->whereBelongsTo($organization)->where('status', 'active')->where('role', 'cashier'))
                    ->exists(),
            ],
            [
                'label' => 'Make first sale',
                'complete' => Sale::query()->whereBelongsTo($organization)->exists(),
            ],
        ];
    }
}
