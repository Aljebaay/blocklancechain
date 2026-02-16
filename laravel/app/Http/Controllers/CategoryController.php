<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ChildCategory;
use App\Services\ProposalService;
use App\Services\SiteSettingsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * CategoryController - handles category pages.
 * Replaces: app/Modules/Platform/categories/category.php
 */
class CategoryController extends Controller
{
    public function __construct(
        private readonly ProposalService $proposalService,
        private readonly SiteSettingsService $settingsService,
    ) {}

    /**
     * Show all categories.
     * Replaces: app/Modules/Platform/categories/index.php
     */
    public function index(): View
    {
        $categories = Category::with('children')->orderBy('cat_order')->get();

        return view('categories.index', [
            'categories' => $categories,
            'settings' => $this->settingsService->getGeneralSettings(),
        ]);
    }

    /**
     * Show a category with its proposals.
     * Replaces: app/Modules/Platform/categories/category.php
     * URL: /categories/{cat_url}/{cat_child_url?}
     */
    public function show(Request $request, string $catUrl, ?string $catChildUrl = null): View
    {
        $category = Category::where('cat_url', $catUrl)->firstOrFail();

        $childCategory = null;
        if ($catChildUrl) {
            $childCategory = ChildCategory::where('cat_id', $category->id)
                ->where('child_cat_url', $catChildUrl)
                ->first();
        }

        $filters = $request->only([
            'min_price', 'max_price', 'delivery_time',
            'seller_level', 'seller_country', 'online_sellers',
        ]);
        $filters['category_id'] = $category->id;

        if ($childCategory) {
            $filters['sub_category_id'] = $childCategory->id;
        }

        $proposals = $this->proposalService->getProposals('category', $filters);

        return view('categories.show', [
            'category' => $category,
            'childCategory' => $childCategory,
            'childCategories' => $category->children,
            'proposals' => $proposals,
            'settings' => $this->settingsService->getGeneralSettings(),
        ]);
    }
}
