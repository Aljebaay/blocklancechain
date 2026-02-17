<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * Loads all the global variables that legacy db.php sets up.
 * This is the single source of truth for shared template data.
 */
class LegacyDataService
{
    /**
     * Load all site-wide data that legacy db.php populates as globals.
     * Returns an associative array matching legacy variable names.
     */
    public function loadGlobals(): array
    {
        $data = [];

        // Site language (default to first language with default_lang=1)
        $siteLanguage = session('siteLanguage');
        if (!$siteLanguage) {
            $defaultLang = DB::table('languages')->where('default_lang', 1)->first();
            $siteLanguage = $defaultLang ? $defaultLang->id : 1;
            session(['siteLanguage' => $siteLanguage]);
        }
        $data['siteLanguage'] = $siteLanguage;

        // General settings
        $gs = DB::table('general_settings')->first();
        if (!$gs) {
            return $data;
        }

        $siteUrl = rtrim(config('app.url', ''), '/');
        $data['site_url'] = $siteUrl;
        $data['site_email_address'] = $gs->site_email_address ?? '';
        $data['site_name'] = $gs->site_name ?? '';
        $data['site_desc'] = $gs->site_desc ?? '';
        $data['site_keywords'] = $gs->site_keywords ?? '';
        $data['site_author'] = $gs->site_author ?? '';
        $data['site_title'] = $gs->site_title ?? ($gs->site_name ?? '');
        $data['enable_mobile_logo'] = $gs->enable_mobile_logo ?? 0;
        $data['site_logo_type'] = $gs->site_logo_type ?? 'text';
        $data['site_logo_text'] = $gs->site_logo_text ?? 'GigZone';
        $data['site_timezone'] = $gs->site_timezone ?? 'UTC';
        $data['site_currency'] = $gs->site_currency ?? 1;
        $data['currency_position'] = $gs->currency_position ?? 'left';
        $data['currency_format'] = $gs->currency_format ?? '';
        $data['enable_maintenance_mode'] = $gs->enable_maintenance_mode ?? 'no';
        $data['enable_referrals'] = $gs->enable_referrals ?? 0;
        $data['language_switcher'] = $gs->language_switcher ?? 0;
        $data['enable_google_translate'] = $gs->enable_google_translate ?? 0;
        $data['google_analytics'] = $gs->google_analytics ?? '';
        $data['site_watermark'] = $gs->site_watermark ?? '';
        $data['enable_social_login'] = $gs->enable_social_login ?? 0;
        $data['enable_websocket'] = $gs->enable_websocket ?? 0;
        $data['websocket_address'] = $gs->websocket_address ?? '';
        $data['knowledge_bank'] = $gs->knowledge_bank ?? 'no';
        $data['google_app_link'] = $gs->google_app_link ?? '';
        $data['apple_app_link'] = $gs->apple_app_link ?? '';
        $data['site_color'] = $gs->site_color ?? '#1DBF73';
        $data['site_hover_color'] = $gs->site_hover_color ?? '';
        $data['site_border_color'] = $gs->site_border_color ?? '';
        $data['site_copyright'] = $gs->site_copyright ?? '';
        $data['edited_proposals'] = $gs->edited_proposals ?? '';
        $data['make_phone_number_required'] = $gs->make_phone_number_required ?? 0;

        // Image URLs for site assets
        $data['site_favicon'] = $this->getImageUrl2('general_settings', 'site_favicon', $gs->site_favicon ?? '');
        $data['site_logo_image'] = $this->getImageUrl2('general_settings', 'site_logo_image', $gs->site_logo_image ?? '');
        $data['site_logo'] = $this->getImageUrl2('general_settings', 'site_logo', $gs->site_logo ?? '');
        $data['site_mobile_logo'] = $this->getImageUrl2('general_settings', 'site_mobile_logo', $gs->site_mobile_logo ?? '');

        // Currency
        $currency = DB::table('currencies')->where('id', $data['site_currency'])->first();
        $data['s_currency_name'] = $currency->name ?? '';
        $data['s_currency'] = $currency->symbol ?? '$';

        // Announcement bar
        $bar = DB::table('announcement_bar')->where('language_id', $siteLanguage)->first();
        $data['enable_bar'] = $bar->enable_bar ?? '0';
        $data['bg_color'] = $bar->bg_color ?? '';
        $data['text_color'] = $bar->text_color ?? '';
        $data['bar_text'] = $bar->bar_text ?? '';
        $data['bar_last_updated'] = $bar->last_updated ?? '';

        // Currency converter
        $converter = DB::table('currency_converter_settings')->first();
        $data['enable_converter'] = $converter->enable ?? 0;

        // Language info
        $langRow = DB::table('languages')->where('id', $siteLanguage)->first();
        $data['lang_dir'] = $langRow->direction ?? 'left';
        $data['template_folder'] = $langRow->template_folder ?? '';

        // Load language strings (pass $site_name as it's used in language files via PHP short echo tags)
        $data['lang'] = $this->loadLanguageStrings($langRow->title ?? 'english', $data['site_name']);

        // RTL helpers
        if (($data['lang_dir'] ?? 'left') === 'right') {
            $data['floatRight'] = 'float-right';
            $data['textRight'] = 'text-right';
        } else {
            $data['floatRight'] = 'float-left';
            $data['textRight'] = 'text-left';
        }

        // Device detection (simplified - assume desktop for SSR)
        $data['deviceType'] = 'computer';
        $data['proposals_stylesheet'] = '<link href="' . $siteUrl . '/styles/desktop_proposals.css" rel="stylesheet">';

        // Row general settings object (needed by some templates)
        $data['row_general_settings'] = $gs;

        return $data;
    }

    /**
     * Load language strings from legacy language files.
     * The legacy language files use PHP short echo tags for $site_name and other variables.
     */
    private function loadLanguageStrings(string $langTitle, string $siteName = ''): array
    {
        $langTitle = strtolower(trim($langTitle));
        if (!preg_match('/^[a-z0-9 _().-]+$/', $langTitle)) {
            $langTitle = 'english';
        }

        $legacyLangDir = base_path('../app/Modules/Platform/languages');
        $langFile = $legacyLangDir . '/' . $langTitle . '.php';

        if (!file_exists($langFile)) {
            $langFile = $legacyLangDir . '/english.php';
        }

        if (file_exists($langFile)) {
            $lang = [];
            // These variables are used inside legacy language files via short echo tags
            $site_name = $siteName;
            $site_url = rtrim(config('app.url', ''), '/');

            // Capture output buffer since language files use short PHP echo tags
            ob_start();
            include $langFile;
            ob_end_clean();

            return $lang;
        }

        return [];
    }

    /**
     * Replicate legacy getImageUrl2 function.
     * For non-S3 images, builds URL like: {site_url}/{main_folder}/{folder}/{filename}
     */
    public function getImageUrl2(string $table, string $field, string $key): string
    {
        $siteUrl = rtrim(config('app.url', ''), '/');

        if (empty($key)) {
            $key = 'empty-image.png';
        }

        $folder = $this->getFolderName($table);
        if ($field === 'seller_cover_image') {
            $folder = 'cover_images';
        }

        $mainFolder = $this->getMainFolderName($folder, $table);
        $key = rawurlencode($key);

        return $this->buildMediaUrl($siteUrl, $mainFolder, $folder, $key);
    }

    /**
     * Replicate legacy getImageUrl function.
     */
    public function getImageUrl(string $table, string $key): string
    {
        $siteUrl = rtrim(config('app.url', ''), '/');

        if (empty($key)) {
            $key = 'empty-image.png';
        }

        $folder = $this->getFolderName($table);
        $mainFolder = $this->getMainFolderName($folder, $table);
        $key = rawurlencode($key);

        return $this->buildMediaUrl($siteUrl, $mainFolder, $folder, $key);
    }

    private function getFolderName(string $table): string
    {
        $map = [
            'admins' => 'admin/admin_images',
            'general_settings' => 'images',
            'sellers' => 'user_images',
            'categories' => 'cat_images',
            'post_categories' => 'blog_cat_images',
            'proposals' => 'proposal_files',
            'order_conversations' => 'order_files',
            'instant_deliveries' => 'order_files',
            'inbox_messages' => 'conversations_files',
            'buyer_requests' => 'request_files',
            'languages' => 'images',
            'section_boxes' => 'box_images',
            'home_cards' => 'card_images',
            'home_section_slider' => 'home_slider_images',
            'slider' => 'slides_images',
            'support_tickets' => 'ticket_files',
            'support_conversations' => 'ticket_files',
            'knowledge_bank' => 'article_images',
            'posts' => 'post_images',
        ];

        return $map[$table] ?? '';
    }

    private function getMainFolderName(string $folder, string $table): string
    {
        $map = [
            'proposal_files' => 'proposals',
            'request_files' => 'requests',
            'conversations_files' => 'conversations',
            'article_images' => 'article',
            'admin_images' => 'admin',
        ];

        if ($folder === 'images' && $table === 'languages') {
            return 'languages';
        }

        return $map[$folder] ?? '';
    }

    private function buildMediaUrl(string $siteUrl, string $mainFolder, string $folder, string $key): string
    {
        $parts = array_filter([$mainFolder, $folder, $key], fn($p) => $p !== null && $p !== '');
        return $siteUrl . '/' . implode('/', $parts);
    }

    /**
     * Load header-specific data (logged-in user, cart, etc.)
     */
    public function loadHeaderData(array $globals): array
    {
        $data = $globals;

        if (session()->has('seller_user_name')) {
            $sellerUserName = session('seller_user_name');
            $seller = DB::table('sellers')
                ->where('seller_user_name', $sellerUserName)
                ->first();

            if (!$seller) {
                session()->forget('seller_user_name');
                return $data;
            }

            $data['seller_id'] = $seller->seller_id;
            $data['seller_email'] = $seller->seller_email;
            $data['seller_verification'] = $seller->seller_verification;
            $data['seller_image'] = $this->getImageUrl2('sellers', 'seller_image', $seller->seller_image ?? '');
            $data['count_cart'] = DB::table('cart')->where('seller_id', $seller->seller_id)->count();

            $account = DB::table('seller_accounts')
                ->where('seller_id', $seller->seller_id)
                ->first();
            if (!$account) {
                DB::table('seller_accounts')->insert(['seller_id' => $seller->seller_id]);
                $account = DB::table('seller_accounts')
                    ->where('seller_id', $seller->seller_id)
                    ->first();
            }
            $data['current_balance'] = $account->current_balance ?? 0;
            $data['count_active_proposals'] = DB::table('proposals')
                ->where('proposal_seller_id', $seller->seller_id)
                ->where('proposal_status', 'active')
                ->count();
        }

        return $data;
    }

    /**
     * Load footer-specific data (links, pages, languages, currencies).
     */
    public function loadFooterData(array $globals): array
    {
        $data = $globals;
        $siteLanguage = $globals['siteLanguage'];

        // Footer links by section
        $data['footer_categories'] = DB::table('footer_links')
            ->where('link_section', 'categories')
            ->where('language_id', $siteLanguage)
            ->get();

        $data['footer_about'] = DB::table('footer_links')
            ->where('link_section', 'about')
            ->where('language_id', $siteLanguage)
            ->get();

        $data['footer_follow'] = DB::table('footer_links')
            ->where('link_section', 'follow')
            ->where('language_id', $siteLanguage)
            ->get();

        // CMS pages for footer
        $data['footer_pages'] = DB::table('pages')
            ->leftJoin('pages_meta', function ($join) use ($siteLanguage) {
                $join->on('pages_meta.page_id', '=', 'pages.id')
                    ->where('pages_meta.language_id', '=', $siteLanguage);
            })
            ->select('pages.id', 'pages.url', 'pages_meta.title')
            ->get()
            ->filter(fn($page) => !empty($page->title));

        // Languages for switcher
        $data['all_languages'] = DB::table('languages')->get();

        // Site currencies for converter
        if (($globals['enable_converter'] ?? 0) == 1) {
            $data['site_currencies'] = DB::table('site_currencies')
                ->join('currencies', 'currencies.id', '=', 'site_currencies.currency_id')
                ->select('site_currencies.id', 'site_currencies.currency_id', 'site_currencies.position', 'currencies.name', 'currencies.symbol')
                ->get();
        }

        return $data;
    }

    /**
     * Load home page specific data.
     */
    public function loadHomeData(array $globals): array
    {
        $data = $globals;
        $siteLanguage = $globals['siteLanguage'];
        $langDir = $globals['lang_dir'] ?? 'left';

        // Home section heading
        $section = DB::table('home_section')
            ->where('language_id', $siteLanguage)
            ->first();
        $data['section_heading'] = $section->section_heading ?? '';
        $data['section_short_heading'] = $section->section_short_heading ?? '';

        // Home slider
        $data['slides'] = DB::table('home_section_slider')->get();

        // Home cards
        $data['home_cards'] = DB::table('home_cards')
            ->where('language_id', $siteLanguage)
            ->get();

        // Categories for home page
        if ($langDir === 'right') {
            $data['categories_row1'] = DB::table('categories')
                ->where('cat_featured', 'yes')
                ->orderByDesc(DB::raw('1'))
                ->offset(4)->limit(4)->get();
            $data['categories_row2'] = DB::table('categories')
                ->where('cat_featured', 'yes')
                ->orderByDesc(DB::raw('1'))
                ->offset(0)->limit(4)->get();
        } else {
            $data['categories_row1'] = DB::table('categories')
                ->where('cat_featured', 'yes')
                ->offset(0)->limit(4)->get();
            $data['categories_row2'] = DB::table('categories')
                ->where('cat_featured', 'yes')
                ->offset(4)->limit(4)->get();
        }

        // Section boxes
        $data['section_boxes_first'] = DB::table('section_boxes')
            ->where('language_id', $siteLanguage)
            ->offset(0)->limit(1)->get();
        $data['section_boxes_rest'] = DB::table('section_boxes')
            ->where('language_id', $siteLanguage)
            ->offset(1)->limit(100)->get();

        // Featured proposals
        $data['featured_proposals_count'] = DB::table('proposals')
            ->where('proposal_featured', 'yes')
            ->where('proposal_status', 'active')
            ->count();
        $data['featured_proposals'] = DB::table('proposals')
            ->where('proposal_featured', 'yes')
            ->where('proposal_status', 'active')
            ->limit(10)
            ->get();

        return $data;
    }

    /**
     * Load authenticated user home page data.
     * Legacy: user_home.php + includes/user_home_sidebar.php
     */
    public function loadAuthHomeData(array $globals): array
    {
        $data = $globals;
        $siteLanguage = $globals['siteLanguage'];
        $langDir = $globals['lang_dir'] ?? 'left';
        $loginSellerId = $globals['seller_id'] ?? 0;

        // Auth slider (different from guest - uses 'slider' table)
        $data['auth_slides'] = DB::table('slider')
            ->where('language_id', $siteLanguage)
            ->get();

        // Featured proposals for auth home (limit 8)
        $data['auth_featured_proposals'] = DB::table('proposals')
            ->where('proposal_featured', 'yes')
            ->where('proposal_status', 'active')
            ->limit(8)
            ->get();

        // Top proposals
        $topIds = DB::table('top_proposals')->pluck('proposal_id')->toArray();
        if (empty($topIds)) {
            $data['auth_top_proposals'] = DB::table('proposals')
                ->where('level_id', 4)
                ->where('proposal_status', 'active')
                ->limit(8)
                ->get();
        } else {
            $data['auth_top_proposals'] = DB::table('proposals')
                ->where(function ($q) use ($topIds) {
                    $q->whereIn('proposal_id', $topIds)
                      ->orWhere(function ($q2) {
                          $q2->where('level_id', 4)->where('proposal_status', 'active');
                      });
                })
                ->limit(8)
                ->get();
        }

        // Random proposals
        $totalActive = DB::table('proposals')->where('proposal_status', 'active')->count();
        if ($totalActive > 0) {
            $limit = min(8, $totalActive);
            $randomOffset = $totalActive > $limit ? mt_rand(0, $totalActive - $limit) : 0;
            $data['auth_random_proposals'] = DB::table('proposals')
                ->where('proposal_status', 'active')
                ->orderByDesc('proposal_id')
                ->offset($randomOffset)
                ->limit($limit)
                ->get();
        } else {
            $data['auth_random_proposals'] = collect();
        }

        // Buyer requests relevant to this seller
        $childIds = DB::table('proposals')
            ->where('proposal_seller_id', $loginSellerId)
            ->where('proposal_status', 'active')
            ->distinct()
            ->pluck('proposal_child_id')
            ->toArray();

        $gs = DB::table('general_settings')->first();
        $relevantRequests = $gs->relevant_requests ?? 'no';

        $requestsQuery = DB::table('buyer_requests')
            ->where('request_status', 'active')
            ->where('seller_id', '!=', $loginSellerId)
            ->orderByDesc('request_id')
            ->limit(5);

        if ($relevantRequests !== 'no' && !empty($childIds)) {
            $requestsQuery->whereIn('child_id', $childIds);
        }

        $data['auth_buyer_requests'] = $requestsQuery->get();

        // Sidebar data: buy it again (completed orders with active proposals)
        $data['sidebar_buy_again'] = DB::table('orders')
            ->where('buyer_id', $loginSellerId)
            ->where('order_status', 'completed')
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('proposals')
                  ->whereColumn('proposals.proposal_id', 'orders.proposal_id')
                  ->where('proposals.proposal_status', 'active');
            })
            ->distinct()
            ->pluck('proposal_id')
            ->toArray();

        // Recently viewed proposals
        $data['sidebar_recently_viewed'] = DB::table('recent_proposals')
            ->where('seller_id', $loginSellerId)
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('proposals')
                  ->whereColumn('proposals.proposal_id', 'recent_proposals.proposal_id')
                  ->where('proposals.proposal_status', 'active');
            })
            ->orderByDesc('recent_proposals.recent_id')
            ->limit(4)
            ->pluck('proposal_id')
            ->toArray();

        // Seller offers quota
        $loginSeller = DB::table('sellers')->where('seller_id', $loginSellerId)->first();
        $data['login_seller_name'] = $loginSeller->seller_name ?? '';
        $data['login_user_name'] = $loginSeller->seller_user_name ?? '';
        $data['login_seller_offers'] = $loginSeller->seller_offers ?? '0';

        return $data;
    }

    /**
     * Replicate legacy showPrice() from commonFunctions.php.
     * Handles currency symbol, position, conversion, and formatting.
     */
    public function showPrice(float|int|string|null $price, string $class = '', string $showSymbol = 'yes'): string
    {
        $price = ($price === null || $price === '') ? 0 : (float) $price;

        $gs = DB::table('general_settings')->first();
        $currencyPosition = $gs->currency_position ?? 'left';
        $currencyFormat = $gs->currency_format ?? '';
        $siteCurrencySymbol = $gs->s_currency ?? '$';

        // Session-based currency conversion
        $sessionCurrencyId = session('siteCurrency');
        if ($sessionCurrencyId !== null) {
            $scRow = DB::table('site_currencies')->where('id', $sessionCurrencyId)->first();
            if ($scRow) {
                $currencyPosition = $scRow->position;
                $currencyFormat = $scRow->format;
                $rate = session('conversionRate', 1);
                $price *= $rate;

                $cRow = DB::table('currencies')->where('id', $scRow->currency_id)->first();
                if ($cRow) {
                    $siteCurrencySymbol = $cRow->symbol;
                }
            }
        }

        $decPoint = '.';
        $thousandsSep = ',';
        if ($currencyFormat === 'european') {
            $decPoint = ',';
            $thousandsSep = '.';
        }

        $formattedPrice = number_format($price, 2, $decPoint, $thousandsSep);

        if (!empty($class)) {
            $formattedPrice = "<span class='{$class}'>{$formattedPrice}</span>";
        }

        if ($showSymbol === 'yes') {
            return ($currencyPosition === 'left')
                ? $siteCurrencySymbol . $formattedPrice
                : $formattedPrice . $siteCurrencySymbol;
        }

        return $formattedPrice;
    }

    /**
     * Replicate legacy dynamicUrl function.
     */
    public function dynamicUrl(string $url, bool $prepend = true): string
    {
        $siteUrl = rtrim(config('app.url', ''), '/');

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        if ($prepend) {
            return $siteUrl . '/' . ltrim($url, '/');
        }

        return $url;
    }
}
