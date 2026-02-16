<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Services\SiteSettingsService;
use Illuminate\View\View;

/**
 * BlogController - handles blog listing and individual posts.
 * Replaces: app/Modules/Platform/blog/index.php, blog/post.php
 */
class BlogController extends Controller
{
    public function __construct(
        private readonly SiteSettingsService $settingsService,
    ) {}

    /**
     * Show blog listing.
     * Replaces: app/Modules/Platform/blog/index.php
     */
    public function index(): View
    {
        $posts = Blog::where('blog_status', 'active')
            ->orderByDesc('id')
            ->paginate(10);

        return view('blog.index', [
            'posts' => $posts,
            'settings' => $this->settingsService->getGeneralSettings(),
        ]);
    }

    /**
     * Show a single blog post.
     * Replaces: app/Modules/Platform/blog/post.php
     * URL: /blog/{id}/{slug?}
     */
    public function show(int $id): View
    {
        $post = Blog::findOrFail($id);

        return view('blog.show', [
            'post' => $post,
            'settings' => $this->settingsService->getGeneralSettings(),
        ]);
    }
}
