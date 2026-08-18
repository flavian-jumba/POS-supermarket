<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchMembership extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'user_id',
        'role',
        'status',
    ];

    protected $attributes = [
        'role' => 'cashier',
        'status' => 'active',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
