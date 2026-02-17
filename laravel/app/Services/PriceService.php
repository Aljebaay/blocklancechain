<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Currency;
use App\Models\SiteCurrency;

/**
 * PriceService - replaces legacy showPrice() function from commonFunctions.php.
 * Handles currency formatting and conversion.
 */
class PriceService
{
    public function __construct(
        private readonly SiteSettingsService $settingsService,
    ) {}

    /**
     * Format a price with currency symbol, matching legacy showPrice() behavior exactly.
     */
    public function showPrice(
        float|int|string|null $price,
        string $class = '',
        string $showSymbol = 'yes',
    ): string {
        $price = $price === null || $price === '' ? 0 : (float) $price;

        $settings = $this->settingsService->getGeneralSettings();
        $currencyPosition = $settings->currency_position;
        $currencyFormat = $settings->currency_format;
        $siteCurrencySymbol = $this->settingsService->getSiteCurrency()->symbol ?? '$';

        // Check for session-based currency conversion
        $sessionCurrencyId = session('siteCurrency');
        if ($sessionCurrencyId !== null) {
            $siteCurrencyRecord = SiteCurrency::find($sessionCurrencyId);
            if ($siteCurrencyRecord) {
                $currencyPosition = $siteCurrencyRecord->position;
                $currencyFormat = $siteCurrencyRecord->format;
                $rate = session('conversionRate', 1);
                $price *= $rate;

                $currencyRecord = Currency::find($siteCurrencyRecord->currency_id);
                if ($currencyRecord) {
                    $siteCurrencySymbol = $currencyRecord->symbol;
                }
            }
        }

        // Format based on currency format (european vs default)
        $decPoint = '.';
        $thousandsSep = ',';
        if ($currencyFormat === 'european') {
            $decPoint = ',';
            $thousandsSep = '.';
        }

        $formattedPrice = number_format($price, 2, $decPoint, $thousandsSep);

        if (! empty($class)) {
            $formattedPrice = "<span class='{$class}'>{$formattedPrice}</span>";
        }

        if ($showSymbol === 'yes') {
            if ($currencyPosition === 'left') {
                return $siteCurrencySymbol.$formattedPrice;
            }

            return $formattedPrice.$siteCurrencySymbol;
        }

        return $formattedPrice;
    }

    /**
     * Calculate processing fee for a given amount.
     * Replaces legacy processing_fee() function.
     */
    public function processingFee(float $amount): float
    {
        $paymentSettings = $this->settingsService->getPaymentSettings();
        $feeType = $paymentSettings->processing_feeType;
        $feeAmount = (float) $paymentSettings->processing_fee;

        if ($feeType === 'fixed') {
            return $feeAmount;
        }

        if ($feeType === 'percentage') {
            return ($feeAmount / 100) * $amount;
        }

        return 0.0;
    }

    /**
     * Get percentage amount.
     * Replaces legacy get_percentage_amount() function.
     */
    public function getPercentageAmount(float $amount, float $percentage): float
    {
        return ($percentage / 100) * $amount;
    }
}
