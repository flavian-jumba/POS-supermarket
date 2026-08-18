<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class StockAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'created_by',
        'adjustment_number',
        'type',
        'reason',
        'notes',
        'status',
    ];

    protected $attributes = [
        'status' => 'draft',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function items(): HasMany
    {
        return $this->hasMany(
            StockAdjustmentItem::class
        );
    }

    public function stockMovements(): MorphMany
    {
        return $this->morphMany(
            StockMovement::class,
            'reference'
        );
    }
}
