<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\InboxMessage;
use App\Models\Seller;
use App\Services\AuthService;
use App\Services\SiteSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * ConversationController - handles messaging/inbox.
 * Replaces: app/Modules/Platform/conversations/*.php
 */
class ConversationController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly SiteSettingsService $settingsService,
    ) {}

    /**
     * Show inbox listing.
     * Replaces: app/Modules/Platform/conversations/inbox.php
     */
    public function inbox(): View
    {
        $seller = $this->authService->currentSeller();

        if (!$seller) {
            abort(403);
        }

        $conversations = Conversation::with(['sender', 'receiver'])
            ->where('sender_id', $seller->seller_id)
            ->orWhere('receiver_id', $seller->seller_id)
            ->orderByDesc('last_activity')
            ->paginate(20);

        return view('conversations.inbox', [
            'conversations' => $conversations,
            'seller' => $seller,
            'settings' => $this->settingsService->getGeneralSettings(),
        ]);
    }

    /**
     * Show a single conversation.
     * Replaces: app/Modules/Platform/conversations/message.php
     */
    public function show(int $conversationId): View
    {
        $seller = $this->authService->currentSeller();

        if (!$seller) {
            abort(403);
        }

        $conversation = Conversation::with(['messages.sender', 'sender', 'receiver'])
            ->findOrFail($conversationId);

        // Verify the current user is a participant
        if ($conversation->sender_id !== $seller->seller_id &&
            $conversation->receiver_id !== $seller->seller_id) {
            abort(403);
        }

        // Mark messages as read
        InboxMessage::where('inbox_id', $conversationId)
            ->where('sender_id', '!=', $seller->seller_id)
            ->where('read_status', 0)
            ->update(['read_status' => 1]);

        return view('conversations.show', [
            'conversation' => $conversation,
            'messages' => $conversation->messages,
            'seller' => $seller,
            'settings' => $this->settingsService->getGeneralSettings(),
        ]);
    }

    /**
     * Send a message in a conversation.
     * Replaces: app/Modules/Platform/conversations/insert_inbox_message.php
     */
    public function sendMessage(Request $request, int $conversationId): JsonResponse
    {
        $seller = $this->authService->currentSeller();

        if (!$seller) {
            return response()->json(['success' => false, 'error' => 'unauthorized'], 403);
        }

        $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $conversation = Conversation::findOrFail($conversationId);

        // Verify the current user is a participant
        if ($conversation->sender_id !== $seller->seller_id &&
            $conversation->receiver_id !== $seller->seller_id) {
            return response()->json(['success' => false, 'error' => 'forbidden'], 403);
        }

        $message = InboxMessage::create([
            'inbox_id' => $conversationId,
            'sender_id' => $seller->seller_id,
            'message' => $request->input('message'),
            'date' => now()->format('Y-m-d H:i:s'),
            'read_status' => 0,
        ]);

        // Update conversation last activity
        $conversation->update(['last_activity' => now()->format('Y-m-d H:i:s')]);

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }
}
