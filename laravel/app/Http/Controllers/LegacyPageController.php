<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\LegacyDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Serves Blade pages that match legacy HTML output exactly.
 */
class LegacyPageController extends Controller
{
    public function __construct(
        private readonly LegacyDataService $legacyData,
    ) {}

    /**
     * Home page — guest or authenticated user.
     * Legacy: index.php → home.php (guest) or user_home.php (auth)
     */
    public function home(Request $request)
    {
        // Legacy: redirect /index to /
        if (str_contains($request->getRequestUri(), 'index')) {
            return redirect('/');
        }

        $globals = $this->legacyData->loadGlobals();
        $globals = $this->legacyData->loadHeaderData($globals);
        $globals = $this->legacyData->loadFooterData($globals);
        $globals = $this->legacyData->loadHomeData($globals);
        $globals['legacyData'] = $this->legacyData;

        $isLoggedIn = session()->has('seller_user_name');

        if ($isLoggedIn) {
            $globals = $this->legacyData->loadAuthHomeData($globals);

            return view('legacy.home-auth', $globals);
        }

        return view('legacy.home-guest', $globals);
    }

    /**
     * Login page.
     * Legacy: login.php
     */
    public function showLogin()
    {
        if (session()->has('seller_user_name')) {
            return redirect('/');
        }

        $globals = $this->legacyData->loadGlobals();
        $globals = $this->legacyData->loadHeaderData($globals);
        $globals = $this->legacyData->loadFooterData($globals);
        $globals['legacyData'] = $this->legacyData;
        $globals['hide_knowledge_bank'] = true;

        return view('legacy.login', $globals);
    }

    /**
     * Register page.
     * Legacy: register.php
     */
    public function showRegister()
    {
        if (session()->has('seller_user_name')) {
            return redirect('/');
        }

        // Legacy has no standalone register page — it returns a JS redirect
        return response("<script>window.open('index?not_available','_self');</script>");
    }

    /**
     * Categories index page.
     * Legacy: categories/index.php
     */
    public function categoriesIndex()
    {
        // Legacy /categories (no subpath) redirects to home page
        return redirect('/');
    }

    /**
     * Search page.
     * Legacy: search.php
     */
    public function search(Request $request)
    {
        // Handle POST search (legacy submits search via POST)
        if ($request->isMethod('post') && $request->has('search_query')) {
            session(['search_query' => $request->input('search_query', '')]);
        }

        $globals = $this->legacyData->loadGlobals();
        $globals = $this->legacyData->loadHeaderData($globals);
        $globals = $this->legacyData->loadFooterData($globals);
        $globals['legacyData'] = $this->legacyData;
        $globals['hide_knowledge_bank'] = true;

        return view('legacy.search', $globals);
    }

    /**
     * Blog index page.
     * Legacy: blog/index.php
     */
    public function blogIndex()
    {
        $globals = $this->legacyData->loadGlobals();
        $globals = $this->legacyData->loadHeaderData($globals);
        $globals = $this->legacyData->loadFooterData($globals);
        $globals['legacyData'] = $this->legacyData;
        $globals['hide_knowledge_bank'] = true;

        return view('legacy.blog-index', $globals);
    }

    /**
     * Category show page.
     * Legacy: categories/category.php?cat_url=...
     */
    public function categoriesShow(string $catUrl, ?string $childUrl = null)
    {
        $globals = $this->legacyData->loadGlobals();
        $globals = $this->legacyData->loadHeaderData($globals);
        $globals = $this->legacyData->loadFooterData($globals);
        $globals['legacyData'] = $this->legacyData;
        $globals['hide_knowledge_bank'] = true;

        $sLang = $globals['siteLanguage'] ?? 1;

        $cat = DB::table('categories')->where('cat_url', urlencode($catUrl))->first();
        if (! $cat) {
            return response("<script>window.open('".($globals['site_url'] ?? '')."/index?not_available','_self');</script>");
        }

        $catMeta = DB::table('cats_meta')->where('cat_id', $cat->cat_id)->where('language_id', $sLang)->first();
        $globals['page_cat_title'] = $catMeta->cat_title ?? '';
        $globals['page_cat_desc'] = $catMeta->cat_desc ?? '';
        $globals['active_cat_id'] = $cat->cat_id;
        $globals['active_child_id'] = null;
        $globals['cat_url'] = $catUrl;
        $globals['cat_child_url'] = $childUrl;

        if ($childUrl) {
            $child = DB::table('categories_children')
                ->where('child_parent_id', $cat->cat_id)
                ->where('child_url', urlencode($childUrl))
                ->first();
            if (! $child) {
                return response("<script>window.open('".($globals['site_url'] ?? '')."/index?not_available','_self');</script>");
            }
            $childMeta = DB::table('child_cats_meta')->where('child_id', $child->child_id)->where('language_id', $sLang)->first();
            $globals['page_child_title'] = $childMeta->child_title ?? '';
            $globals['page_child_desc'] = $childMeta->child_desc ?? '';
            $globals['page_cat_title'] = '';
            $globals['page_cat_desc'] = '';
            $globals['active_cat_id'] = null;
            $globals['active_child_id'] = $child->child_id;
        }

        return view('legacy.categories-show', $globals);
    }

    /**
     * Proposal show page.
     * Legacy: proposals/proposal.php?username=...&proposal_url=...
     */
    public function proposalShow(string $username, string $slug)
    {
        $globals = $this->legacyData->loadGlobals();
        $globals = $this->legacyData->loadHeaderData($globals);
        $globals = $this->legacyData->loadFooterData($globals);
        $globals['legacyData'] = $this->legacyData;
        $globals['hide_knowledge_bank'] = true;

        $sLang = $globals['siteLanguage'] ?? 1;

        $seller = DB::table('sellers')->where('seller_user_name', $username)->first();
        if (! $seller) {
            return response("<script>window.open('".($globals['site_url'] ?? '')."/index?not_available','_self');</script>");
        }

        $proposalUrl = urlencode($slug);
        $proposal = DB::table('proposals')
            ->where('proposal_url', $proposalUrl)
            ->where('proposal_seller_id', $seller->seller_id)
            ->whereNotIn('proposal_status', ['draft', 'admin_pause', 'pause', 'pending', 'trash', 'declined', 'modification', 'deleted'])
            ->first();

        if (! $proposal) {
            return response("<script>window.open('".($globals['site_url'] ?? '')."/index?not_available','_self');</script>");
        }

        $globals['proposal_id'] = $proposal->proposal_id;
        $globals['proposal_title'] = $proposal->proposal_title;
        $globals['proposal_desc'] = $proposal->proposal_desc;
        $globals['proposal_short_desc'] = strip_tags(substr($proposal->proposal_desc ?? '', 0, 160));
        $globals['proposal_tags'] = $proposal->proposal_tags ?? '';
        $globals['proposal_price'] = $proposal->proposal_price ?? 0;
        $globals['proposal_img1'] = $proposal->proposal_img1 ?? '';
        $globals['proposal_img2'] = $proposal->proposal_img2 ?? '';
        $globals['proposal_img3'] = $proposal->proposal_img3 ?? '';
        $globals['proposal_img4'] = $proposal->proposal_img4 ?? '';
        $globals['proposal_seller_id'] = $proposal->proposal_seller_id;
        $globals['proposal_cat_id'] = $proposal->proposal_cat_id ?? 0;

        $globals['proposal_seller_user_name'] = $seller->seller_user_name;
        $globals['proposal_seller_image'] = $seller->seller_image ?? '';
        $globals['proposal_seller_country'] = $seller->seller_country ?? '';
        $globals['proposal_seller_about'] = $seller->seller_about ?? '';
        $globals['proposal_seller_vacation'] = $seller->seller_vacation ?? 'off';

        $cat = DB::table('categories')->where('cat_id', $proposal->proposal_cat_id)->first();
        $globals['proposal_cat_url'] = $cat->cat_url ?? '';
        $catMeta = DB::table('cats_meta')->where('cat_id', $proposal->proposal_cat_id)->where('language_id', $sLang)->first();
        $globals['proposal_cat_title'] = $catMeta->cat_title ?? '';

        $child = DB::table('categories_children')->where('child_id', $proposal->proposal_child_id ?? 0)->first();
        $globals['proposal_child_url'] = $child->child_url ?? '';
        $childMeta = DB::table('child_cats_meta')->where('child_id', $proposal->proposal_child_id ?? 0)->where('language_id', $sLang)->first();
        $globals['proposal_child_title'] = $childMeta->child_title ?? '';

        $deliveryTime = DB::table('delivery_times')->where('delivery_id', $proposal->delivery_id ?? 0)->first();
        $globals['delivery_proposal_title'] = $deliveryTime->delivery_proposal_title ?? '';

        $orders = DB::table('orders')->where('proposal_id', $proposal->proposal_id)->where('order_active', 'yes')->count();
        $globals['proposal_order_queue'] = $orders;

        $reviews = DB::table('buyer_reviews')->where('proposal_id', $proposal->proposal_id)->get();
        $ratings = [];
        foreach ($reviews as $r) {
            $ratings[] = $r->buyer_rating;
        }
        $total = array_sum($ratings);
        $avg = count($ratings) > 0 ? $total / count($ratings) : 0;
        $globals['proposal_rating'] = (int) substr((string) $avg, 0, 1);
        $globals['count_reviews'] = count($ratings);

        $level = DB::table('seller_levels_meta')->where('level_id', $seller->seller_level ?? 0)->where('language_id', $sLang)->first();
        $globals['level_title'] = $level->title ?? '';

        $globals['count_extras'] = DB::table('proposals_extras')->where('proposal_id', $proposal->proposal_id)->count();
        $globals['count_faq'] = DB::table('proposals_faq')->where('proposal_id', $proposal->proposal_id)->count();

        return view('legacy.proposal', $globals);
    }

    /**
     * Blog post page.
     * Legacy: blog/post.php?id=...
     */
    public function blogPost(string $id)
    {
        $globals = $this->legacyData->loadGlobals();
        $globals = $this->legacyData->loadHeaderData($globals);
        $globals = $this->legacyData->loadFooterData($globals);
        $globals['legacyData'] = $this->legacyData;
        $globals['hide_knowledge_bank'] = true;
        $globals['post_id'] = $id;

        return view('legacy.blog-post', $globals);
    }

    /**
     * Tags page.
     * Legacy: tags/tag.php?tag=...
     */
    public function tagsShow(string $tag)
    {
        $globals = $this->legacyData->loadGlobals();
        $globals = $this->legacyData->loadHeaderData($globals);
        $globals = $this->legacyData->loadFooterData($globals);
        $globals['legacyData'] = $this->legacyData;
        $globals['hide_knowledge_bank'] = true;
        $globals['tag'] = str_replace('-', ' ', $tag);

        return view('legacy.tags', $globals);
    }

    /**
     * CMS page.
     * Legacy: pages/index.php?slug=...
     */
    public function pageShow(string $slug)
    {
        $globals = $this->legacyData->loadGlobals();
        $globals = $this->legacyData->loadHeaderData($globals);
        $globals = $this->legacyData->loadFooterData($globals);
        $globals['legacyData'] = $this->legacyData;
        $globals['hide_knowledge_bank'] = true;

        $sLang = $globals['siteLanguage'] ?? 1;
        $page = DB::table('pages')->where('url', urlencode($slug))->first();
        if (! $page) {
            return response("<script>window.open('".($globals['site_url'] ?? '')."/index?not_available','_self');</script>");
        }

        $meta = DB::table('pages_meta')->where('page_id', $page->id)->where('language_id', $sLang)->first();
        $globals['page_title'] = $meta->title ?? '';
        $globals['page_content'] = $meta->content ?? '';

        return view('legacy.page', $globals);
    }

    /**
     * User profile page.
     * Legacy: handler.php → user.php (when username matches a seller)
     */
    public function userProfile(string $username)
    {
        $globals = $this->legacyData->loadGlobals();
        $globals = $this->legacyData->loadHeaderData($globals);
        $globals = $this->legacyData->loadFooterData($globals);
        $globals['legacyData'] = $this->legacyData;
        $globals['hide_knowledge_bank'] = true;
        $globals['profile_username'] = $username;

        $seller = DB::table('sellers')
            ->where('seller_user_name', $username)
            ->whereNotIn('seller_status', ['deactivated', 'block-ban'])
            ->first();

        if (! $seller) {
            return response("<script>window.open('".($globals['site_url'] ?? '')."/index?not_available','_self');</script>");
        }

        return view('legacy.user-profile', $globals);
    }
}
