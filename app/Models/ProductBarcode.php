<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductBarcode extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'product_id',
        'barcode',
        'is_primary',
    ];

    protected $attributes = [
        'is_primary' => false,
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected static function booted(): void
    {
        static::saving(function (ProductBarcode $barcode): void {
            if (! $barcode->is_primary) {
                return;
            }

            static::query()
                ->where('product_id', $barcode->product_id)
                ->when($barcode->exists, fn ($query) => $query->whereKeyNot($barcode->getKey()))
                ->update(['is_primary' => false]);
        });
    }
}
