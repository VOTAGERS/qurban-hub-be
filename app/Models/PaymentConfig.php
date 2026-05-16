<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class PaymentConfig extends Model
{
    protected $fillable = [
        'provider',
        'public_key',
        'secret_key',
        'webhook_secret',
        'webhook_stripe_id',
        'webhook_url',
        'mode',
        'is_active',
        'status',
        'created_by',
        'updated_by'
    ];

    protected $hidden = ['secret_key', 'webhook_secret'];
    public function setSecretKeyAttribute(string $value): void
    {
        $this->attributes['secret_key'] = Crypt::encryptString($value);
    }

    public function getSecretKeyAttribute(?string $value): string
    {
        if (!$value) return '';
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return '';
        }
    }
    public function setWebhookSecretAttribute(?string $value): void
    {
        $this->attributes['webhook_secret'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getWebhookSecretAttribute(?string $value): ?string
    {
        if (!$value) return null;
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return null;
        }
    }
}
