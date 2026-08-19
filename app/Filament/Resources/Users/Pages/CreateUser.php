<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\BranchMembership;
use App\Models\OrganizationMembership;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected string $organizationRole = 'cashier';

    /** @var array<int> */
    protected array $branchIds = [];

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->organizationRole = $data['organization_role'] ?? 'cashier';
        $this->branchIds = $data['branch_ids'] ?? [];

        unset($data['organization_role'], $data['branch_ids']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $tenant = Filament::getTenant();

        OrganizationMembership::query()->updateOrCreate(
            [
                'organization_id' => $tenant->id,
                'user_id' => $this->record->id,
            ],
            [
                'role' => $this->organizationRole,
                'status' => 'active',
            ],
        );

        foreach ($this->branchIds as $branchId) {
            BranchMembership::query()->updateOrCreate(
                [
                    'branch_id' => $branchId,
                    'user_id' => $this->record->id,
                ],
                [
                    'role' => $this->organizationRole === 'cashier' ? 'cashier' : 'manager',
                    'status' => 'active',
                ],
            );
        }
    }
}
