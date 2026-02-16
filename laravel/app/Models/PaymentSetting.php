<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * PaymentSetting model - maps to legacy `payment_settings` table.
 */
class PaymentSetting extends Model
{
    protected $table = 'payment_settings';

    public $timestamps = false;

    protected $fillable = [
        'paypal_app_client_id',
        'paypal_app_secret',
        'paypal_currency_code',
        'paypal_mode',
        'stripe_secret_key',
        'stripe_publishable_key',
        'stripe_currency_code',
        'paystack_secret_key',
        'paystack_public_key',
        'paystack_currency_code',
        'mercadopago_access_token',
        'mercadopago_public_key',
        'coinpayments_merchant_id',
        'coinpayments_secret',
        'coinpayments_currency',
        'dusupay_secret_key',
        'dusupay_public_key',
        'dusupay_currency_code',
        'processing_feeType',
        'processing_fee',
        'enable_paypal',
        'enable_stripe',
        'enable_paystack',
        'enable_mercadopago',
        'enable_coinpayments',
        'enable_dusupay',
    ];

    /**
     * Get cached payment settings.
     */
    public static function getCached(): self
    {
        return cache()->remember('payment_settings', 3600, function () {
            return self::query()->first() ?? new self();
        });
    }
}
