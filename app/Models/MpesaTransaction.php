<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MpesaTransaction extends Model
{
    protected $fillable = [
        'organization_id',
        'sale_id',
        'payment_id',
        'phone',
        'amount',
        'merchant_request_id',
        'checkout_request_id',
        'mpesa_receipt_number',
        'result_code',
        'result_description',
        'status',
        'requested_at',
        'completed_at',
        'request_payload',
        'callback_payload',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, ['successful', 'failed', 'cancelled', 'timeout'], true);
    }

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'requested_at' => 'datetime',
            'completed_at' => 'datetime',
            'request_payload' => 'array',
            'callback_payload' => 'array',
        ];
    }
}
