<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'status',
        'onboarding_completed_at',
    ];

    protected $attributes = [
        'status' => 'active',
    ];

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(OrganizationMembership::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'organization_memberships'
        )
            ->withPivot([
                'role',
                'status',
            ])
            ->withTimestamps();
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function productBarcodes(): HasMany
    {
        return $this->hasMany(ProductBarcode::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function mpesaIntegration(): HasOne
    {
        return $this->hasOne(MpesaIntegration::class);
    }

    public function mpesaTransactions(): HasMany
    {
        return $this->hasMany(MpesaTransaction::class);
    }

    protected function casts(): array
    {
        return [
            'onboarding_completed_at' => 'datetime',
        ];
    }
}
