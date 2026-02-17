<?php

declare(strict_types=1);

use App\Http\Controllers\SearchController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| JSON API endpoints for AJAX calls and Vue 3 frontend communication.
|
*/

Route::prefix('v1')->group(function () {
    // Search
    Route::get('/search', [SearchController::class, 'load'])->name('api.search');

    // Proposals
    Route::get('/proposals', function (Request $request) {
        $service = app(\App\Services\ProposalService::class);
        $filters = $request->only([
            'search', 'min_price', 'max_price', 'delivery_time',
            'seller_level', 'seller_country', 'online_sellers',
            'category_id', 'sub_category_id', 'tag',
        ]);
        $type = $request->input('type', 'search');
        $proposals = $service->getProposals($type, $filters, (int) $request->input('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => $proposals->items(),
            'pagination' => [
                'current_page' => $proposals->currentPage(),
                'last_page' => $proposals->lastPage(),
                'total' => $proposals->total(),
                'per_page' => $proposals->perPage(),
            ],
        ]);
    })->name('api.proposals.index');

    Route::get('/proposals/by-slug/{username}/{proposalUrl}', function (string $username, string $proposalUrl) {
        $service = app(\App\Services\ProposalService::class);
        $proposal = $service->getProposalBySlug($username, $proposalUrl);

        if (! $proposal) {
            return response()->json(['success' => false, 'error' => 'not_found'], 404);
        }

        $proposal->increment('proposal_views');

        return response()->json([
            'success' => true,
            'data' => $proposal->load(['seller', 'buyerReviews.buyer', 'extras', 'faqs', 'packages', 'gallery']),
        ]);
    })->name('api.proposals.show-by-slug');

    Route::get('/proposals/{id}', function (int $id) {
        $service = app(\App\Services\ProposalService::class);
        $proposal = $service->getProposalById($id);

        if (! $proposal) {
            return response()->json(['success' => false, 'error' => 'not_found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $proposal,
        ]);
    })->name('api.proposals.show');

    // Categories
    Route::get('/categories', function () {
        $categories = \App\Models\Category::with('children')
            ->where('cat_featured', 'yes')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    })->name('api.categories.index');

    // Site Settings (public subset)
    Route::get('/settings', function () {
        $service = app(\App\Services\SiteSettingsService::class);
        $settings = $service->getGeneralSettings();

        return response()->json([
            'success' => true,
            'data' => [
                'site_name' => $settings->site_name,
                'site_url' => $service->getSiteUrl(),
                'site_logo_type' => $settings->site_logo_type,
                'site_logo_text' => $settings->site_logo_text,
                'currency_position' => $settings->currency_position,
                'currency_format' => $settings->currency_format,
                'enable_social_login' => $settings->enable_social_login,
                'enable_referrals' => $settings->enable_referrals,
                'language_switcher' => $settings->language_switcher,
            ],
        ]);
    })->name('api.settings');

    // Auth status
    Route::get('/auth/status', function () {
        $authService = app(\App\Services\AuthService::class);
        $seller = $authService->currentSeller();

        return response()->json([
            'success' => true,
            'authenticated' => $authService->isLoggedIn(),
            'user' => $seller ? [
                'id' => $seller->seller_id,
                'username' => $seller->seller_user_name,
                'name' => $seller->seller_name,
                'email' => $seller->seller_email,
                'image' => $seller->seller_image,
                'level' => $seller->seller_level,
            ] : null,
        ]);
    })->name('api.auth.status');

    // User profile (public)
    Route::get('/users/{username}', function (string $username) {
        $seller = \App\Models\Seller::where('seller_user_name', $username)
            ->with(['proposals' => fn ($q) => $q->where('proposal_status', 'active')->with(['seller', 'buyerReviews'])])
            ->first();

        if (! $seller) {
            return response()->json(['success' => false, 'error' => 'not_found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $seller,
        ]);
    })->name('api.users.profile');

    // Blog
    Route::get('/blog', function (Request $request) {
        $posts = \App\Models\Blog::where('blog_status', 'active')
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $posts->items(),
            'pagination' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'total' => $posts->total(),
                'per_page' => $posts->perPage(),
            ],
        ]);
    })->name('api.blog.index');

    Route::get('/blog/{id}', function (int $id) {
        $post = \App\Models\Blog::find($id);

        if (! $post) {
            return response()->json(['success' => false, 'error' => 'not_found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $post,
        ]);
    })->name('api.blog.show');

    // CMS Pages
    Route::get('/pages/{slug}', function (string $slug) {
        $page = \App\Models\Page::where('page_slug', $slug)
            ->where('page_status', 'active')
            ->first();

        if (! $page) {
            return response()->json(['success' => false, 'error' => 'not_found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $page,
        ]);
    })->name('api.pages.show');

    // Buyer requests (public listing)
    Route::get('/buyer-requests', function (Request $request) {
        $requests = \App\Models\BuyerRequest::with(['buyer', 'category'])
            ->where('request_status', 'active')
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $requests->items(),
            'pagination' => [
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
                'total' => $requests->total(),
                'per_page' => $requests->perPage(),
            ],
        ]);
    })->name('api.buyer-requests.index');
});

// Authenticated API routes
Route::prefix('v1')->middleware('seller.auth')->group(function () {
    // My proposals
    Route::get('/proposals/my', function () {
        $authService = app(\App\Services\AuthService::class);
        $seller = $authService->currentSeller();
        if (! $seller) {
            return response()->json(['success' => false, 'error' => 'unauthorized'], 403);
        }

        $proposals = \App\Models\Proposal::where('proposal_seller_id', $seller->seller_id)
            ->with(['seller', 'buyerReviews'])
            ->orderByDesc('proposal_id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $proposals,
        ]);
    })->name('api.proposals.my');

    // Orders
    Route::get('/orders/buying/{status?}', function (string $status = 'active') {
        $authService = app(\App\Services\AuthService::class);
        $seller = $authService->currentSeller();
        if (! $seller) {
            return response()->json(['success' => false, 'error' => 'unauthorized'], 403);
        }

        $validStatuses = ['active', 'completed', 'cancelled', 'delivered', 'all'];
        if (! in_array($status, $validStatuses, true)) {
            return response()->json(['success' => false, 'error' => 'invalid_status'], 400);
        }

        $query = \App\Models\Order::with(['proposal', 'seller'])
            ->where('buyer_id', $seller->seller_id);
        if ($status !== 'all') {
            $query->where('order_status', $status);
        }
        $orders = $query->orderByDesc('order_id')->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'total' => $orders->total(),
                'per_page' => $orders->perPage(),
            ],
        ]);
    })->name('api.orders.buying');

    Route::get('/orders/selling/{status?}', function (string $status = 'active') {
        $authService = app(\App\Services\AuthService::class);
        $seller = $authService->currentSeller();
        if (! $seller) {
            return response()->json(['success' => false, 'error' => 'unauthorized'], 403);
        }

        $validStatuses = ['active', 'completed', 'cancelled', 'delivered', 'all'];
        if (! in_array($status, $validStatuses, true)) {
            return response()->json(['success' => false, 'error' => 'invalid_status'], 400);
        }

        $query = \App\Models\Order::with(['proposal', 'buyer'])
            ->where('seller_id', $seller->seller_id);
        if ($status !== 'all') {
            $query->where('order_status', $status);
        }
        $orders = $query->orderByDesc('order_id')->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'total' => $orders->total(),
                'per_page' => $orders->perPage(),
            ],
        ]);
    })->name('api.orders.selling');

    Route::get('/orders/{orderId}', function (int $orderId) {
        $authService = app(\App\Services\AuthService::class);
        $seller = $authService->currentSeller();
        if (! $seller) {
            return response()->json(['success' => false, 'error' => 'unauthorized'], 403);
        }

        $order = \App\Models\Order::with([
            'proposal', 'buyer', 'seller',
            'buyerReview', 'sellerReview', 'messages.sender',
        ])->find($orderId);

        if (! $order || ($order->buyer_id !== $seller->seller_id && $order->seller_id !== $seller->seller_id)) {
            return response()->json(['success' => false, 'error' => 'not_found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    })->name('api.orders.show');

    // Conversations / Inbox
    Route::get('/conversations', function (Request $request) {
        $authService = app(\App\Services\AuthService::class);
        $seller = $authService->currentSeller();
        if (! $seller) {
            return response()->json(['success' => false, 'error' => 'unauthorized'], 403);
        }

        $conversations = \App\Models\Conversation::with(['sender', 'receiver'])
            ->where(function ($q) use ($seller) {
                $q->where('sender_id', $seller->seller_id)
                    ->orWhere('receiver_id', $seller->seller_id);
            })
            ->orderByDesc('last_activity')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $conversations->items(),
            'pagination' => [
                'current_page' => $conversations->currentPage(),
                'last_page' => $conversations->lastPage(),
                'total' => $conversations->total(),
                'per_page' => $conversations->perPage(),
            ],
        ]);
    })->name('api.conversations.index');

    Route::get('/conversations/{conversationId}', function (int $conversationId) {
        $authService = app(\App\Services\AuthService::class);
        $seller = $authService->currentSeller();
        if (! $seller) {
            return response()->json(['success' => false, 'error' => 'unauthorized'], 403);
        }

        $conversation = \App\Models\Conversation::with(['messages.sender', 'sender', 'receiver'])
            ->find($conversationId);

        if (! $conversation || ($conversation->sender_id !== $seller->seller_id && $conversation->receiver_id !== $seller->seller_id)) {
            return response()->json(['success' => false, 'error' => 'not_found'], 404);
        }

        \App\Models\InboxMessage::where('inbox_id', $conversationId)
            ->where('sender_id', '!=', $seller->seller_id)
            ->where('read_status', 0)
            ->update(['read_status' => 1]);

        return response()->json([
            'success' => true,
            'data' => $conversation,
        ]);
    })->name('api.conversations.show');

    Route::post('/conversations/{conversationId}/message', function (Request $request, int $conversationId) {
        $authService = app(\App\Services\AuthService::class);
        $seller = $authService->currentSeller();
        if (! $seller) {
            return response()->json(['success' => false, 'error' => 'unauthorized'], 403);
        }

        $request->validate(['message' => ['required', 'string', 'max:5000']]);

        $conversation = \App\Models\Conversation::find($conversationId);
        if (! $conversation || ($conversation->sender_id !== $seller->seller_id && $conversation->receiver_id !== $seller->seller_id)) {
            return response()->json(['success' => false, 'error' => 'forbidden'], 403);
        }

        $message = \App\Models\InboxMessage::create([
            'inbox_id' => $conversationId,
            'sender_id' => $seller->seller_id,
            'message' => $request->input('message'),
            'date' => now()->format('Y-m-d H:i:s'),
            'read_status' => 0,
        ]);

        $conversation->update(['last_activity' => now()->format('Y-m-d H:i:s')]);

        return response()->json([
            'success' => true,
            'data' => $message->load('sender'),
        ]);
    })->name('api.conversations.message');

    // User settings
    Route::get('/settings/user', function () {
        $authService = app(\App\Services\AuthService::class);
        $seller = $authService->currentSeller();
        if (! $seller) {
            return response()->json(['success' => false, 'error' => 'unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $seller,
        ]);
    })->name('api.settings.user');

    Route::post('/settings/user', function (Request $request) {
        $authService = app(\App\Services\AuthService::class);
        $seller = $authService->currentSeller();
        if (! $seller) {
            return response()->json(['success' => false, 'error' => 'unauthorized'], 403);
        }

        $validated = $request->validate([
            'seller_name' => ['nullable', 'string', 'max:255'],
            'seller_headline' => ['nullable', 'string', 'max:500'],
            'seller_about' => ['nullable', 'string', 'max:5000'],
            'seller_country' => ['nullable', 'string', 'max:100'],
            'seller_phone' => ['nullable', 'string', 'max:20'],
            'seller_language' => ['nullable', 'string', 'max:100'],
        ]);

        $seller->update($validated);

        return response()->json(['success' => true]);
    })->name('api.settings.update');

    // Support tickets
    Route::get('/tickets', [\App\Http\Controllers\TicketController::class, 'index'])->name('api.tickets.index');
    Route::get('/tickets/{ticketId}', [\App\Http\Controllers\TicketController::class, 'show'])->name('api.tickets.show');
    Route::post('/tickets', [\App\Http\Controllers\TicketController::class, 'store'])->name('api.tickets.store');
});

// =====================================================================
// Admin API Routes (requires admin authentication)
// =====================================================================
Route::prefix('v1/admin')->middleware('admin.auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])
        ->name('api.admin.dashboard');

    // User management
    Route::get('/users', [\App\Http\Controllers\Admin\UserManagementController::class, 'index'])
        ->name('api.admin.users.index');
    Route::get('/users/{sellerId}', [\App\Http\Controllers\Admin\UserManagementController::class, 'show'])
        ->name('api.admin.users.show');
    Route::post('/users/{sellerId}/status', [\App\Http\Controllers\Admin\UserManagementController::class, 'updateStatus'])
        ->name('api.admin.users.status');
    Route::post('/users/{sellerId}/balance', [\App\Http\Controllers\Admin\UserManagementController::class, 'updateBalance'])
        ->name('api.admin.users.balance');

    // Proposal management
    Route::get('/proposals', [\App\Http\Controllers\Admin\ProposalManagementController::class, 'index'])
        ->name('api.admin.proposals.index');
    Route::post('/proposals/{proposalId}/status', [\App\Http\Controllers\Admin\ProposalManagementController::class, 'updateStatus'])
        ->name('api.admin.proposals.status');
    Route::post('/proposals/{proposalId}/featured', [\App\Http\Controllers\Admin\ProposalManagementController::class, 'toggleFeatured'])
        ->name('api.admin.proposals.featured');
    Route::post('/proposals/{proposalId}/top-rated', [\App\Http\Controllers\Admin\ProposalManagementController::class, 'toggleTopRated'])
        ->name('api.admin.proposals.top-rated');

    // General settings
    Route::get('/settings', function () {
        $service = app(\App\Services\SiteSettingsService::class);

        return response()->json([
            'success' => true,
            'data' => $service->getGeneralSettings(),
        ]);
    })->name('api.admin.settings');

    Route::post('/settings', function (Request $request) {
        $settings = \App\Models\GeneralSetting::first();
        if ($settings) {
            $settings->update($request->only($settings->getFillable()));
            app(\App\Services\SiteSettingsService::class)->clearCache();
        }

        return response()->json(['success' => true]);
    })->name('api.admin.settings.update');

    // Admin auth check
    Route::get('/auth/status', function () {
        return response()->json([
            'success' => true,
            'authenticated' => true,
            'admin' => [
                'email' => session('admin_email'),
                'name' => session('admin_name'),
            ],
        ]);
    })->name('api.admin.auth.status');
});
