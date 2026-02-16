<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\SiteSettingsService;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * ArticleController - handles article pages.
 * Replaces: app/Modules/Platform/article/article.php
 */
class ArticleController extends Controller
{
    public function __construct(
        private readonly SiteSettingsService $settingsService,
    ) {}

    /**
     * Show an article by URL slug.
     * URL: /article/{article_url}
     */
    public function show(string $articleUrl): View
    {
        $article = DB::table('articles')
            ->where('article_url', $articleUrl)
            ->where('article_status', 'active')
            ->first();

        if (!$article) {
            abort(404);
        }

        return view('articles.show', [
            'article' => $article,
            'settings' => $this->settingsService->getGeneralSettings(),
        ]);
    }
}
