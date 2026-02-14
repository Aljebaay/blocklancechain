<?php

namespace App\Http\Controllers\LegacyBridge;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class RequestsFetchSubcategoryController extends Controller
{
    public function __invoke(Request $request)
    {
        if (filter_var(env('FORCE_LARAVEL_REQUESTS_MODULE_FAIL', false), FILTER_VALIDATE_BOOLEAN)) {
            return response('', 500);
        }

        $forceFail = filter_var(env('FORCE_LARAVEL_FETCH_SUBCATEGORY_FAIL', false), FILTER_VALIDATE_BOOLEAN);
        if ($forceFail) {
            throw new \RuntimeException('FORCE_LARAVEL_FETCH_SUBCATEGORY_FAIL triggered');
        }

        $this->bootstrapLegacySession();

        // Mirror legacy session check by attempting to read the same PHP session if available.
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }

        $isSellerLoggedIn = isset($_SESSION['seller_user_name']) && $_SESSION['seller_user_name'] !== '';
        if (!$isSellerLoggedIn) {
            $body = "<script>window.open('../login','_self')</script>";
            return response($body, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
        }

        $categoryId = $request->input('category_id');
        if ($categoryId === null || $categoryId === '') {
            $categoryId = 0;
        }

        $siteLanguage = $_SESSION['siteLanguage'] ?? 1;
        $placeholder = $this->placeholderText();

        $options = [];
        $options[] = "<option value=''> {$placeholder} </option>";

        $children = DB::connection('legacy')
            ->table('categories_children')
            ->select('child_id')
            ->where('child_parent_id', $categoryId)
            ->get();

        if ($children->isNotEmpty()) {
            $childIds = $children->pluck('child_id')->all();
            $meta = DB::connection('legacy')
                ->table('child_cats_meta')
                ->select('child_id', 'child_title')
                ->whereIn('child_id', $childIds)
                ->where('language_id', $siteLanguage)
                ->get()
                ->keyBy('child_id');

            foreach ($childIds as $childId) {
                $title = $meta[$childId]->child_title ?? '';
                $options[] = "<option value='{$childId}'> {$title} </option>";
            }
        }

        $html = implode("\n", $options);

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    private function placeholderText(): string
    {
        if (isset($GLOBALS['lang']['placeholder']['select_sub_category'])) {
            return (string) $GLOBALS['lang']['placeholder']['select_sub_category'];
        }

        return 'Select Sub Category';
    }

    private function bootstrapLegacySession(): void
    {
        $legacyBase = realpath(base_path('..'));
        $bootstrap = $legacyBase !== false
            ? $legacyBase . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . 'Platform' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'session_bootstrap.php'
            : null;

        if ($bootstrap && is_file($bootstrap) && !function_exists('blc_bootstrap_session')) {
            require_once $bootstrap;
        }

        if (function_exists('blc_bootstrap_session')) {
            blc_bootstrap_session();
        }
    }
}
