<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MpesaIntegration extends Model
{
    protected $fillable = [
        'organization_id',
        'environment',
        'consumer_key',
        'consumer_secret',
        'shortcode',
        'passkey',
        'transaction_type',
        'is_active',
        'connection_status',
        'last_tested_at',
        'last_error',
    ];

    protected $hidden = [
        'consumer_key',
        'consumer_secret',
        'passkey',
    ];

    protected $attributes = [
        'environment' => 'sandbox',
        'transaction_type' => 'CustomerPayBillOnline',
        'is_active' => false,
        'connection_status' => 'untested',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function isUsable(): bool
    {
        return $this->is_active
            && $this->connection_status === 'verified'
            && filled($this->consumer_key)
            && filled($this->consumer_secret)
            && filled($this->shortcode)
            && filled($this->passkey);
    }

    protected function casts(): array
    {
        return [
            'consumer_key' => 'encrypted',
            'consumer_secret' => 'encrypted',
            'passkey' => 'encrypted',
            'is_active' => 'boolean',
            'last_tested_at' => 'datetime',
        ];
    }
}
