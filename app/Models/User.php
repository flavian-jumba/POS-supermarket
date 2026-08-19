<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable implements FilamentUser, HasTenants
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $attributes = [
        'status' => 'active',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->status === 'active'
            && $this->organizationMemberships()
                ->where('status', 'active')
                ->whereIn('role', ['owner', 'admin', 'manager'])
                ->exists();
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $tenant instanceof Organization
            && $this->organizationMemberships()
                ->whereBelongsTo($tenant)
                ->where('status', 'active')
                ->whereIn('role', ['owner', 'admin', 'manager'])
                ->exists();
    }

    /**
     * @return Collection<int, Organization>
     */
    public function getTenants(Panel $panel): Collection
    {
        return $this->organizations()
            ->wherePivot('status', 'active')
            ->wherePivotIn('role', ['owner', 'admin', 'manager'])
            ->where('organizations.status', 'active')
            ->orderBy('organizations.name')
            ->get();
    }

    public function organizationMemberships(): HasMany
    {
        return $this->hasMany(OrganizationMembership::class);
    }

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(
            Organization::class,
            'organization_memberships'
        )
            ->withPivot([
                'role',
                'status',
            ])
            ->withTimestamps();
    }

    public function branchMemberships(): HasMany
    {
        return $this->hasMany(BranchMembership::class);
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(
            Branch::class,
            'branch_memberships'
        )
            ->withPivot([
                'role',
                'status',
            ])
            ->withTimestamps();
    }

    public function registerSessions(): HasMany
    {
        return $this->hasMany(RegisterSession::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'cashier_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function stockAdjustments(): HasMany
    {
        return $this->hasMany(
            StockAdjustment::class,
            'created_by'
        );
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
