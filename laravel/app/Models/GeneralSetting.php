<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * GeneralSetting model - maps to legacy `general_settings` table.
 * Contains site-wide configuration (site name, URL, logos, etc.).
 */
class GeneralSetting extends Model
{
    protected $table = 'general_settings';

    public $timestamps = false;

    protected $fillable = [
        'site_url',
        'site_email_address',
        'site_name',
        'site_title',
        'site_desc',
        'site_keywords',
        'site_author',
        'site_favicon',
        'site_logo_type',
        'site_logo_text',
        'site_logo_image',
        'site_logo',
        'site_mobile_logo',
        'enable_mobile_logo',
        'site_timezone',
        'tinymce_api_key',
        'google_app_link',
        'apple_app_link',
        'enable_social_login',
        'fb_app_id',
        'fb_app_secret',
        'g_client_id',
        'g_client_secret',
        'site_currency',
        'currency_position',
        'currency_format',
        'make_phone_number_required',
        'enable_maintenance_mode',
        'enable_referrals',
        'language_switcher',
        'enable_google_translate',
        'google_analytics',
        'site_watermark',
        'jwplayer_code',
        'edited_proposals',
        'enable_websocket',
        'websocket_address',
        'knowledge_bank',
    ];

    /**
     * Get cached site settings (singleton pattern).
     */
    public static function getCached(): self
    {
        return cache()->remember('general_settings', 3600, function () {
            return self::query()->first() ?? new self;
        });
    }
}
