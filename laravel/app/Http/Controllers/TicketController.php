<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Services\SiteSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * TicketController - handles support ticket functionality.
 * Replaces: app/Modules/Platform/ticket_support/*.php
 */
class TicketController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly SiteSettingsService $settingsService,
    ) {}

    /**
     * List support tickets for the current user.
     */
    public function index(): JsonResponse
    {
        $seller = $this->authService->currentSeller();

        if (! $seller) {
            return response()->json(['success' => false, 'error' => 'unauthorized'], 403);
        }

        $tickets = DB::table('support_requests')
            ->where('seller_id', $seller->seller_id)
            ->orderByDesc('id')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $tickets->items(),
            'pagination' => [
                'current_page' => $tickets->currentPage(),
                'last_page' => $tickets->lastPage(),
                'total' => $tickets->total(),
            ],
        ]);
    }

    /**
     * Show a single ticket conversation.
     */
    public function show(int $ticketId): JsonResponse
    {
        $seller = $this->authService->currentSeller();

        if (! $seller) {
            return response()->json(['success' => false, 'error' => 'unauthorized'], 403);
        }

        $ticket = DB::table('support_requests')
            ->where('id', $ticketId)
            ->where('seller_id', $seller->seller_id)
            ->first();

        if (! $ticket) {
            return response()->json(['success' => false, 'error' => 'not_found'], 404);
        }

        $messages = DB::table('support_messages')
            ->where('ticket_id', $ticketId)
            ->orderBy('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'ticket' => $ticket,
                'messages' => $messages,
            ],
        ]);
    }

    /**
     * Create a new support ticket.
     */
    public function store(Request $request): JsonResponse
    {
        $seller = $this->authService->currentSeller();

        if (! $seller) {
            return response()->json(['success' => false, 'error' => 'unauthorized'], 403);
        }

        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $ticketId = DB::table('support_requests')->insertGetId([
            'seller_id' => $seller->seller_id,
            'subject' => $validated['subject'],
            'status' => 'open',
            'date' => now()->format('Y-m-d H:i:s'),
        ]);

        DB::table('support_messages')->insert([
            'ticket_id' => $ticketId,
            'sender_id' => $seller->seller_id,
            'sender_type' => 'user',
            'message' => $validated['message'],
            'date' => now()->format('Y-m-d H:i:s'),
        ]);

        return response()->json([
            'success' => true,
            'data' => ['ticket_id' => $ticketId],
        ]);
    }
}
