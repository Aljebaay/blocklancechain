<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\LegacyDataService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/**
 * Handles AJAX endpoints that legacy JavaScript calls for filtered
 * proposal loading and pagination.
 *
 * Legacy endpoints:
 *   search_load.php   → dispatches get_search_proposals / get_search_pagination
 *   category_load.php → dispatches get_category_proposals / get_category_pagination
 *   tag_load.php      → dispatches get_tag_proposals / get_tag_pagination
 *   featured_load.php → dispatches get_featured_proposals / get_featured_pagination
 *
 * All endpoints accept POST with filter params (online_sellers[], seller_country[],
 * seller_level[], seller_language[], delivery_time[], cat_id[], instant_delivery[],
 * order[]) and zAction to choose proposals vs pagination response.
 */
class LegacyAjaxController extends Controller
{
    public function __construct(
        private readonly LegacyDataService $legacyData,
    ) {}

    /**
     * POST /search_load
     */
    public function searchLoad(Request $request): Response
    {
        $action = $request->input('zAction', 'get_search_proposals');

        if ($action === 'get_search_pagination') {
            return $this->renderPagination($request, 'search');
        }

        return $this->renderProposals($request, 'search');
    }

    /**
     * POST /category_load
     */
    public function categoryLoad(Request $request): Response
    {
        $action = $request->input('zAction', 'get_category_proposals');

        if ($action === 'get_category_pagination') {
            return $this->renderPagination($request, 'category');
        }

        return $this->renderProposals($request, 'category');
    }

    /**
     * POST /tag_load
     */
    public function tagLoad(Request $request): Response
    {
        $action = $request->input('zAction', 'get_tag_proposals');

        if ($action === 'get_tag_pagination') {
            return $this->renderPagination($request, 'tag');
        }

        return $this->renderProposals($request, 'tag');
    }

    /**
     * POST /featured_load
     */
    public function featuredLoad(Request $request): Response
    {
        $action = $request->input('zAction', 'get_featured_proposals');

        if ($action === 'get_featured_pagination') {
            return $this->renderPagination($request, 'featured');
        }

        return $this->renderProposals($request, 'featured');
    }

    // ─────────────────────────────────────────────────────────────────
    //  Core filter + render logic (matches legacy functions/filter.php)
    // ─────────────────────────────────────────────────────────────────

    /**
     * Build the base WHERE clause for the given filter type.
     * Returns [string $where, array $bindings].
     */
    private function baseWhere(string $filterType): array
    {
        $where = '';
        $bindings = [];

        switch ($filterType) {
            case 'search':
                $searchQuery = session('search_query', '');
                $bindings['proposal_title'] = "%{$searchQuery}%";
                $where = "proposal_title LIKE ? AND proposal_status='active'";
                $bindings = ["%{$searchQuery}%"];
                break;

            case 'category':
                if (session()->has('cat_child_id')) {
                    $where = "proposal_child_id = ? AND proposal_status='active'";
                    $bindings = [session('cat_child_id')];
                } elseif (session()->has('cat_id')) {
                    $where = "proposal_cat_id = ? AND proposal_status='active'";
                    $bindings = [session('cat_id')];
                } else {
                    $where = "proposal_status='active'";
                }
                break;

            case 'tag':
                $tag = session('tag', '');
                $where = "proposal_tags LIKE ? AND proposal_status='active'";
                $bindings = ["%{$tag}%"];
                break;

            case 'featured':
                $where = "proposal_featured='yes' AND proposal_status='active'";
                break;

            default:
                $where = "proposal_status='active'";
                break;
        }

        return [$where, $bindings];
    }

    /**
     * Collect online seller IDs (sellers active within last 10 seconds).
     * Matches legacy check_status() function.
     */
    private function getOnlineSellerIds(): array
    {
        $threshold = date('Y-m-d H:i:s', strtotime('-10 seconds'));

        return DB::table('sellers')
            ->where('seller_activity', '>', $threshold)
            ->pluck('seller_id')
            ->toArray();
    }

    /**
     * Apply sidebar filter conditions from POST parameters.
     * Matches legacy filter.php logic for building WHERE clauses.
     *
     * Returns [string $extraWhere, array $extraBindings, string $wherePath].
     */
    private function applyFilters(Request $request): array
    {
        $extraWhere = '';
        $extraBindings = [];
        $wherePath = '';

        // Online sellers filter
        $onlineSellers = $request->input('online_sellers', []);
        if (is_array($onlineSellers)) {
            foreach ($onlineSellers as $value) {
                if ($value != 0) {
                    $onlineIds = $this->getOnlineSellerIds();
                    if (! empty($onlineIds)) {
                        $placeholders = implode(',', array_fill(0, count($onlineIds), '?'));
                        $extraWhere .= " AND proposal_seller_id IN ({$placeholders})";
                        $extraBindings = array_merge($extraBindings, $onlineIds);
                    } else {
                        // No online sellers — force empty results
                        $extraWhere .= ' AND 1=0';
                    }
                    $wherePath .= "online_sellers[]={$value}&";
                    break; // Only process once
                }
            }
        }

        // Instant delivery filter
        $instantDelivery = 0;
        $instantDeliveryArr = $request->input('instant_delivery', []);
        if (is_array($instantDeliveryArr) && ! empty($instantDeliveryArr)) {
            $instantDelivery = (int) $instantDeliveryArr[0];
            $wherePath .= "instant_delivery[]={$instantDelivery}&";
        }

        // Order
        $orderArr = $request->input('order', []);
        $orderBy = 'DESC';
        if (is_array($orderArr) && ! empty($orderArr)) {
            $orderBy = strtoupper($orderArr[0]) === 'ASC' ? 'ASC' : 'DESC';
            $wherePath .= "order[]={$orderBy}&";
        }

        // Seller country filter
        $sellerCountry = $request->input('seller_country', []);
        if (is_array($sellerCountry)) {
            $countryValues = array_filter($sellerCountry, fn ($v) => $v !== 'undefined' && $v !== '');
            if (! empty($countryValues)) {
                $placeholders = implode(',', array_fill(0, count($countryValues), '?'));
                $extraWhere .= " AND sellers.seller_country IN ({$placeholders})";
                $extraBindings = array_merge($extraBindings, array_values($countryValues));
                foreach ($countryValues as $v) {
                    $wherePath .= "seller_country[]={$v}&";
                }
            }
        }

        // Seller city filter
        $sellerCity = $request->input('seller_city', []);
        if (is_array($sellerCity)) {
            $cityValues = array_filter($sellerCity, fn ($v) => $v !== 'undefined' && $v !== '');
            if (! empty($cityValues)) {
                $placeholders = implode(',', array_fill(0, count($cityValues), '?'));
                $extraWhere .= " AND sellers.seller_city IN ({$placeholders})";
                $extraBindings = array_merge($extraBindings, array_values($cityValues));
                foreach ($cityValues as $v) {
                    $wherePath .= "seller_city[]={$v}&";
                }
            }
        }

        // Category filter (for search page sidebar)
        $catId = $request->input('cat_id', []);
        if (is_array($catId)) {
            $catValues = array_filter($catId, fn ($v) => (int) $v !== 0);
            if (! empty($catValues)) {
                $placeholders = implode(',', array_fill(0, count($catValues), '?'));
                $extraWhere .= " AND proposal_cat_id IN ({$placeholders})";
                $extraBindings = array_merge($extraBindings, array_values($catValues));
                foreach ($catValues as $v) {
                    $wherePath .= "cat_id[]={$v}&";
                }
            }
        }

        // Delivery time filter
        $deliveryTime = $request->input('delivery_time', []);
        if (is_array($deliveryTime)) {
            $deliveryValues = array_filter($deliveryTime, fn ($v) => (int) $v !== 0);
            if (! empty($deliveryValues)) {
                $placeholders = implode(',', array_fill(0, count($deliveryValues), '?'));
                $extraWhere .= " AND delivery_id IN ({$placeholders})";
                $extraBindings = array_merge($extraBindings, array_values($deliveryValues));
                foreach ($deliveryValues as $v) {
                    $wherePath .= "delivery_time[]={$v}&";
                }
            }
        }

        // Seller level filter
        $sellerLevel = $request->input('seller_level', []);
        if (is_array($sellerLevel)) {
            $levelValues = array_filter($sellerLevel, fn ($v) => (int) $v !== 0);
            if (! empty($levelValues)) {
                $placeholders = implode(',', array_fill(0, count($levelValues), '?'));
                $extraWhere .= " AND level_id IN ({$placeholders})";
                $extraBindings = array_merge($extraBindings, array_values($levelValues));
                foreach ($levelValues as $v) {
                    $wherePath .= "seller_level[]={$v}&";
                }
            }
        }

        // Seller language filter
        $sellerLanguage = $request->input('seller_language', []);
        if (is_array($sellerLanguage)) {
            $langValues = array_filter($sellerLanguage, fn ($v) => (int) $v !== 0);
            if (! empty($langValues)) {
                $placeholders = implode(',', array_fill(0, count($langValues), '?'));
                $extraWhere .= " AND language_id IN ({$placeholders})";
                $extraBindings = array_merge($extraBindings, array_values($langValues));
                foreach ($langValues as $v) {
                    $wherePath .= "seller_language[]={$v}&";
                }
            }
        }

        // Instant delivery join condition
        if ($instantDelivery === 1) {
            $extraWhere .= ' AND instant_deliveries.enable=1';
        }

        return [$extraWhere, $extraBindings, $wherePath, $orderBy];
    }

    /**
     * Render filtered proposal cards as HTML.
     * Matches legacy get_proposals() in filter.php.
     */
    private function renderProposals(Request $request, string $filterType): Response
    {
        [$baseWhere, $baseBindings] = $this->baseWhere($filterType);
        [$extraWhere, $extraBindings, $wherePath, $orderBy] = $this->applyFilters($request);

        $perPage = 16;
        $page = max(1, (int) $request->input('page', 1));
        $offset = ($page - 1) * $perPage;

        $orderClause = $filterType === 'random'
            ? 'ORDER BY proposal_id DESC'
            : "ORDER BY proposals.proposal_id {$orderBy}";

        $fullWhere = $baseWhere.$extraWhere;
        $allBindings = array_merge($baseBindings, $extraBindings, [$perPage, $offset]);

        $sql = 'SELECT DISTINCT proposals.* FROM proposals '
             .'JOIN sellers ON proposals.proposal_seller_id = sellers.seller_id '
             .'JOIN instant_deliveries ON proposals.proposal_id = instant_deliveries.proposal_id '
             ."WHERE {$fullWhere} {$orderClause} LIMIT ? OFFSET ?";

        $proposals = DB::select($sql, $allBindings);

        if (empty($proposals)) {
            return $this->emptyResultsHtml($filterType);
        }

        $globals = $this->legacyData->loadGlobals();
        $siteLanguage = session('siteLanguage', 1);
        $html = '';

        foreach ($proposals as $proposal) {
            $cardHtml = view('legacy.partials.proposal-card', array_merge($globals, [
                'proposal' => $proposal,
                'legacyData' => $this->legacyData,
            ]))->render();

            $html .= '<div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-3">'.$cardHtml.'</div>';
        }

        return response($html)->header('Content-Type', 'text/html');
    }

    /**
     * Render pagination links as HTML.
     * Matches legacy get_pagination() in filter.php.
     */
    private function renderPagination(Request $request, string $filterType): Response
    {
        [$baseWhere, $baseBindings] = $this->baseWhere($filterType);
        [$extraWhere, $extraBindings, $wherePath] = $this->applyFilters($request);

        $fullWhere = $baseWhere.$extraWhere;
        $allBindings = array_merge($baseBindings, $extraBindings);

        $sql = 'SELECT COUNT(DISTINCT proposals.proposal_id) as cnt FROM proposals '
             .'JOIN sellers ON proposals.proposal_seller_id = sellers.seller_id '
             .'JOIN instant_deliveries ON proposals.proposal_id = instant_deliveries.proposal_id '
             ."WHERE {$fullWhere}";

        $countResult = DB::select($sql, $allBindings);
        $totalRecords = $countResult[0]->cnt ?? 0;

        if ($totalRecords === 0) {
            return response('')->header('Content-Type', 'text/html');
        }

        $perPage = 16;
        $totalPages = (int) ceil($totalRecords / $perPage);
        $page = max(1, (int) $request->input('page', 1));

        $globals = $this->legacyData->loadGlobals();
        $lang = $globals['lang'] ?? [];

        $firstLabel = $lang['pagination']['first_page'] ?? 'First';
        $lastLabel = $lang['pagination']['last_page'] ?? 'Last';

        $html = '';

        // First page link
        $html .= "<li class='page-item'><a class='page-link' href='?page=1&{$wherePath}'>{$firstLabel}</a></li>";

        // Page 1
        $activeClass = ($page === 1) ? ' active' : '';
        $html .= "<li class='page-item{$activeClass}'><a class='page-link' href='?page=1&{$wherePath}'>1</a></li>";

        // Pages around current
        $i = max(2, $page - 5);
        if ($i > 2) {
            $html .= "<li class='page-item'><a class='page-link'>...</a></li>";
        }

        for (; $i < min($page + 6, $totalPages); $i++) {
            $activeClass = ($i === $page) ? ' active' : '';
            $html .= "<li class='page-item{$activeClass}'><a href='?page={$i}&{$wherePath}' class='page-link'>{$i}</a></li>";
        }

        if ($i !== $totalPages && $totalPages > 1) {
            $html .= "<li class='page-item'><a class='page-link'>...</a></li>";
        }

        // Last numbered page
        if ($totalPages > 1) {
            $activeClass = ($totalPages === $page) ? ' active' : '';
            $html .= "<li class='page-item{$activeClass}'><a class='page-link' href='?page={$totalPages}&{$wherePath}'>{$totalPages}</a></li>";
        }

        // Last page link
        $html .= "<li class='page-item'><a class='page-link' href='?page={$totalPages}&{$wherePath}'>{$lastLabel}</a></li>";

        return response($html)->header('Content-Type', 'text/html');
    }

    /**
     * Return empty-results HTML matching legacy no-results messages.
     */
    private function emptyResultsHtml(string $filterType): Response
    {
        $globals = $this->legacyData->loadGlobals();
        $lang = $globals['lang'] ?? [];

        $message = match ($filterType) {
            'search' => $lang['search']['no_results'] ?? 'No proposals found.',
            'category' => session()->has('cat_child_id')
                ? ($lang['sub_category']['no_results'] ?? 'No proposals found in this subcategory.')
                : ($lang['category']['no_results'] ?? 'No proposals found in this category.'),
            'tag' => $lang['tag_proposals']['no_results'] ?? 'No proposals found with this tag.',
            default => $lang['search']['no_results'] ?? 'No proposals found.',
        };

        $html = "<div class='col-md-12'><h1 class='text-center mt-4'><i class='fa fa-meh-o'></i> {$message}</h1></div>";

        return response($html)->header('Content-Type', 'text/html');
    }
}
