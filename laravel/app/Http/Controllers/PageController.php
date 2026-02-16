<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Page;
use App\Services\SiteSettingsService;
use Illuminate\View\View;

/**
 * PageController - handles static CMS pages.
 * Replaces: app/Modules/Platform/pages/index.php
 */
class PageController extends Controller
{
    public function __construct(
        private readonly SiteSettingsService $settingsService,
    ) {}

    /**
     * Show a CMS page by slug.
     * URL: /pages/{slug}
     */
    public function show(string $slug): View
    {
        $page = Page::where('page_slug', $slug)
            ->where('page_status', 'active')
            ->firstOrFail();

        return view('pages.show', [
            'page' => $page,
            'settings' => $this->settingsService->getGeneralSettings(),
        ]);
    }
}
