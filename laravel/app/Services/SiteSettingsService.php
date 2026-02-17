<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AnnouncementBar;
use App\Models\ApiSetting;
use App\Models\Currency;
use App\Models\GeneralSetting;
use App\Models\Language;
use App\Models\PaymentSetting;
use App\Models\SmtpSetting;

/**
 * SiteSettingsService - replaces legacy global variables from includes/db.php.
 * Provides centralized access to site configuration loaded from the database.
 */
class SiteSettingsService
{
    private ?GeneralSetting $generalSettings = null;

    private ?PaymentSetting $paymentSettings = null;

    private ?SmtpSetting $smtpSettings = null;

    private ?ApiSetting $apiSettings = null;

    private ?Language $currentLanguage = null;

    private ?Currency $siteCurrency = null;

    private ?AnnouncementBar $announcementBar = null;

    public function getGeneralSettings(): GeneralSetting
    {
        if ($this->generalSettings === null) {
            $this->generalSettings = GeneralSetting::getCached();
        }

        return $this->generalSettings;
    }

    public function getPaymentSettings(): PaymentSetting
    {
        if ($this->paymentSettings === null) {
            $this->paymentSettings = PaymentSetting::getCached();
        }

        return $this->paymentSettings;
    }

    public function getSmtpSettings(): SmtpSetting
    {
        if ($this->smtpSettings === null) {
            $this->smtpSettings = cache()->remember('smtp_settings', 3600, function () {
                return SmtpSetting::query()->first() ?? new SmtpSetting;
            });
        }

        return $this->smtpSettings;
    }

    public function getApiSettings(): ApiSetting
    {
        if ($this->apiSettings === null) {
            $this->apiSettings = cache()->remember('api_settings', 3600, function () {
                return ApiSetting::query()->first() ?? new ApiSetting;
            });
        }

        return $this->apiSettings;
    }

    public function getSiteUrl(): string
    {
        $envUrl = config('app.url');
        if (! empty($envUrl)) {
            return rtrim($envUrl, '/');
        }

        $settings = $this->getGeneralSettings();

        return rtrim((string) $settings->site_url, '/');
    }

    public function getSiteName(): string
    {
        return (string) $this->getGeneralSettings()->site_name;
    }

    public function getCurrentLanguage(?int $languageId = null): Language
    {
        if ($this->currentLanguage === null || $languageId !== null) {
            $id = $languageId ?? session('siteLanguage');

            if ($id === null) {
                $defaultLang = Language::where('default_lang', 1)->first();
                if ($defaultLang) {
                    session(['siteLanguage' => $defaultLang->id]);
                    $this->currentLanguage = $defaultLang;
                } else {
                    $this->currentLanguage = new Language;
                }
            } else {
                $this->currentLanguage = Language::find($id) ?? new Language;
            }
        }

        return $this->currentLanguage;
    }

    public function getSiteCurrency(): Currency
    {
        if ($this->siteCurrency === null) {
            $settings = $this->getGeneralSettings();
            $this->siteCurrency = Currency::find($settings->site_currency) ?? new Currency;
        }

        return $this->siteCurrency;
    }

    public function getAnnouncementBar(?int $languageId = null): AnnouncementBar
    {
        $langId = $languageId ?? session('siteLanguage', 1);

        if ($this->announcementBar === null) {
            $this->announcementBar = AnnouncementBar::where('language_id', $langId)->first()
                ?? new AnnouncementBar;
        }

        return $this->announcementBar;
    }

    /**
     * Get the site timezone.
     */
    public function getTimezone(): string
    {
        return (string) ($this->getGeneralSettings()->site_timezone ?: 'UTC');
    }

    /**
     * Check if maintenance mode is enabled in the database.
     */
    public function isMaintenanceMode(): bool
    {
        return $this->getGeneralSettings()->enable_maintenance_mode === 'yes';
    }

    /**
     * Clear all cached settings.
     */
    public function clearCache(): void
    {
        cache()->forget('general_settings');
        cache()->forget('payment_settings');
        cache()->forget('smtp_settings');
        cache()->forget('api_settings');
        $this->generalSettings = null;
        $this->paymentSettings = null;
        $this->smtpSettings = null;
        $this->apiSettings = null;
        $this->currentLanguage = null;
        $this->siteCurrency = null;
        $this->announcementBar = null;
    }
}
