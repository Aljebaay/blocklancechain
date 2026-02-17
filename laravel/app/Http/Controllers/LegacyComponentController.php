<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\LegacyDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

/**
 * LegacyComponentController - handles AJAX component endpoints.
 * Replaces:
 *   includes/comp/c-favorites.php
 *   includes/comp/c-messages-header.php
 *   includes/comp/c-messages-body.php
 *   includes/comp/c-notifications-header.php
 *   includes/comp/c-notifications-body.php
 *   includes/messagePopup.php
 *   includes/notificationsPopup.php
 *   includes/close_cookies_footer.php
 *
 * All endpoints are POST and called by customjs.js / knowledge-bank.js via $.ajax().
 */
class LegacyComponentController extends Controller
{
    private LegacyDataService $legacyData;

    public function __construct(LegacyDataService $legacyData)
    {
        $this->legacyData = $legacyData;
    }

    /**
     * POST /includes/comp/c-favorites
     * Returns plain text count of favorites for the seller, or empty string if 0.
     */
    public function cFavorites(Request $request): Response
    {
        $sellerId = (int) $request->input('seller_id', 0);
        if ($sellerId <= 0) {
            return response('');
        }

        $count = DB::table('favorites')
            ->where('seller_id', $sellerId)
            ->count();

        return response($count > 0 ? (string) $count : '');
    }

    /**
     * POST /includes/comp/c-messages-header
     * Returns plain text count of unread inbox messages, or empty string if 0.
     */
    public function cMessagesHeader(Request $request): Response
    {
        $sellerId = (int) $request->input('seller_id', 0);
        if ($sellerId <= 0) {
            return response('');
        }

        $count = DB::table('inbox_messages')
            ->where('message_receiver', $sellerId)
            ->where('message_status', 'unread')
            ->count();

        return response($count > 0 ? (string) $count : '');
    }

    /**
     * POST /includes/comp/c-messages-body
     * Returns JSON with messages array, lang strings, sender info, message previews.
     */
    public function cMessagesBody(Request $request): JsonResponse
    {
        $sellerId = (int) $request->input('seller_id', 0);
        if ($sellerId <= 0) {
            return response()->json([]);
        }

        // Count all non-empty inbox conversations
        $countAll = DB::table('inbox_sellers')
            ->where(function ($q) use ($sellerId) {
                $q->where('receiver_id', $sellerId)
                    ->orWhere('sender_id', $sellerId);
            })
            ->where('message_status', '!=', 'empty')
            ->count();

        $data = [
            'lang' => [
                'inbox' => $this->getLangString('popup', 'inbox', 'Inbox'),
                'view_inbox' => $this->getLangString('popup', 'view_inbox', 'View Inbox'),
                'no_inbox' => $this->getLangString('popup', 'no_inbox', 'No Messages'),
            ],
            'count_all_inbox_sellers' => $countAll,
            'see_all' => $this->getLangString(null, 'see_all', 'See All'),
            'messages' => [],
        ];

        // Get latest 4 conversations
        $inboxSellers = DB::table('inbox_sellers')
            ->where(function ($q) use ($sellerId) {
                $q->where('receiver_id', $sellerId)
                    ->orWhere('sender_id', $sellerId);
            })
            ->where('message_status', '!=', 'empty')
            ->orderByDesc('time')
            ->limit(4)
            ->get();

        $i = 0;
        foreach ($inboxSellers as $row) {
            $i++;

            // Determine the "other" party
            $otherSellerId = ($sellerId == $row->sender_id)
                ? $row->receiver_id
                : $row->sender_id;

            // Check if this sender is hidden
            $hidden = DB::table('hide_seller_messages')
                ->where('hider_id', $sellerId)
                ->where('hide_seller_id', $otherSellerId)
                ->exists();

            if ($hidden) {
                $data['count_all_inbox_sellers'] = max(0, $data['count_all_inbox_sellers'] - 1);

                continue;
            }

            $msg = [];
            $msg['message_group_id'] = $row->message_group_id;

            // Get sender info
            $sender = DB::table('sellers')
                ->where('seller_id', $otherSellerId)
                ->first();

            $msg['sender_user_name'] = $sender->seller_user_name ?? '';
            $senderImage = $sender->seller_image ?? '';
            $msg['sender_image'] = $this->legacyData->getImageUrl2('sellers', 'seller_image', $senderImage);
            if (empty($msg['sender_image']) || empty($senderImage)) {
                $msg['sender_image'] = 'empty-image.png';
            }

            // Get message details
            $inboxMessage = DB::table('inbox_messages')
                ->where('message_id', $row->message_id)
                ->first();

            $messageDesc = strip_tags($inboxMessage->message_desc ?? '');
            $msg['date'] = $inboxMessage->message_date ?? '';
            $msg['status'] = $inboxMessage->message_status ?? '';

            if ($messageDesc === '') {
                $messageDesc = 'Sent you an offer';
            }
            $msg['desc'] = $messageDesc;

            // CSS class for unread messages
            if ($msg['status'] === 'unread' && $sellerId == $row->receiver_id) {
                $msg['class'] = 'header-message-div-unread';
            } else {
                $msg['class'] = 'header-message-div';
            }

            $data['messages'][$i] = $msg;
        }

        return response()->json($data);
    }

    /**
     * POST /includes/comp/c-notifications-header
     * Returns plain text count of unread notifications, or empty string if 0.
     */
    public function cNotificationsHeader(Request $request): Response
    {
        $sellerId = (int) $request->input('seller_id', 0);
        if ($sellerId <= 0) {
            return response('');
        }

        $count = DB::table('notifications')
            ->where('receiver_id', $sellerId)
            ->where('status', 'unread')
            ->count();

        return response($count > 0 ? (string) $count : '');
    }

    /**
     * POST /includes/comp/c-notifications-body
     * Returns JSON with notifications array, sender info, reason messages.
     */
    public function cNotificationsBody(Request $request): JsonResponse
    {
        $sellerId = (int) $request->input('seller_id', 0);
        if ($sellerId <= 0) {
            return response()->json([]);
        }

        $siteUrl = rtrim(config('app.url', ''), '/');

        $countAll = DB::table('notifications')
            ->where('receiver_id', $sellerId)
            ->count();

        $data = [
            'lang' => [
                'notifications' => $this->getLangString('popup', 'notifications', 'Notifications'),
                'view_notifications' => $this->getLangString('popup', 'view_notifications', 'View Notifications'),
                'no_notifications' => $this->getLangString('popup', 'no_notifications', 'No Notifications'),
            ],
            'count_all_notifications' => $countAll,
            'see_all' => $this->getLangString(null, 'see_all', 'See All'),
            'notifications' => [],
        ];

        $notifications = DB::table('notifications')
            ->where('receiver_id', $sellerId)
            ->orderByDesc('notification_id')
            ->limit(4)
            ->get();

        $i = 0;
        foreach ($notifications as $row) {
            $i++;
            $n = [];
            $n['id'] = $row->notification_id;
            $n['order_id'] = $row->order_id;
            $n['date'] = $row->date;
            $reason = $row->reason;
            $senderIdRaw = $row->sender_id;

            // Check if sender is admin
            if (str_contains((string) $senderIdRaw, 'admin')) {
                $adminId = (int) str_replace('admin_', '', (string) $senderIdRaw);
                $n['sender_user_name'] = 'Admin';
                $admin = DB::table('admins')->where('admin_id', $adminId)->first();
                $adminImage = $admin->admin_image ?? '';
                if (empty($adminImage)) {
                    $n['sender_image'] = "$siteUrl/admin/admin_images/empty-image.png";
                } else {
                    $n['sender_image'] = $this->legacyData->getImageUrl('admins', $adminImage);
                }
            } else {
                $sender = DB::table('sellers')
                    ->where('seller_id', $senderIdRaw)
                    ->first();
                $senderUserName = (string) ($sender->seller_user_name ?? '');
                $senderImage = (string) ($sender->seller_image ?? '');
                $n['sender_user_name'] = ucfirst($senderUserName);
                if ($senderImage === '') {
                    $n['sender_image'] = "$siteUrl/user_images/empty-image.png";
                } else {
                    $n['sender_image'] = $this->legacyData->getImageUrl2('sellers', 'seller_image', $senderImage);
                }
            }

            $n['message'] = $this->getNotificationMessage($reason);
            $n['class'] = ($row->status === 'unread')
                ? 'header-message-div-unread'
                : 'header-message-div';

            $data['notifications'][$i] = $n;
        }

        return response()->json($data);
    }

    /**
     * POST /includes/messagePopup
     * Returns JSON with popup messages, updates inbox_sellers popup='0'.
     */
    public function messagePopup(Request $request): JsonResponse
    {
        $sellerId = (int) $request->input('seller_id', 1);
        if ($sellerId <= 0) {
            $sellerId = 1;
        }

        $data = [];
        $i = 0;

        $popupMessages = DB::table('inbox_sellers')
            ->where('receiver_id', $sellerId)
            ->where('popup', '1')
            ->orderByDesc('inbox_seller_id')
            ->limit(4)
            ->get();

        foreach ($popupMessages as $row) {
            $i++;
            $messageGroupId = $row->message_group_id;

            // Mark popup as seen
            DB::table('inbox_sellers')
                ->where('receiver_id', $sellerId)
                ->where('message_status', 'unread')
                ->where('message_group_id', $messageGroupId)
                ->update(['popup' => '0']);

            $msg = [];
            $msg['message_group_id'] = $messageGroupId;

            // Determine the "other" party
            $otherSellerId = ($sellerId == $row->sender_id)
                ? $row->receiver_id
                : $row->sender_id;

            $sender = DB::table('sellers')
                ->where('seller_id', $otherSellerId)
                ->first();

            $msg['sender_user_name'] = $sender->seller_user_name ?? '';
            $senderImage = $sender->seller_image ?? '';
            $msg['sender_image'] = $this->legacyData->getImageUrl2('sellers', 'seller_image', $senderImage);
            if (empty($msg['sender_image']) || empty($senderImage)) {
                $msg['sender_image'] = 'empty-image.png';
            }

            // Get message details
            $inboxMessage = DB::table('inbox_messages')
                ->where('message_id', $row->message_id)
                ->first();

            $msg['desc'] = substr(strip_tags($inboxMessage->message_desc ?? ''), 0, 250);
            $msg['date'] = $inboxMessage->message_date ?? '';
            $msg['offer_id'] = $inboxMessage->message_offer_id ?? '';
            $msg['message_status'] = $inboxMessage->message_status ?? '';

            if (empty($msg['desc']) && ! empty($msg['offer_id'])) {
                $msg['sender_user_name'] = 'Offer waiting!';
                $msg['desc'] = 'You have a new offer in your inbox.';
            }

            $data[$i] = $msg;
        }

        return response()->json($data);
    }

    /**
     * POST /includes/notificationsPopup
     * Returns JSON with popup notification data, updates notifications bell='over'.
     */
    public function notificationsPopup(Request $request): JsonResponse
    {
        $sellerId = (int) $request->input('seller_id', 0);
        if ($sellerId <= 0) {
            return response()->json([]);
        }

        $siteUrl = rtrim(config('app.url', ''), '/');

        $data = [];
        $i = 0;

        $activeNotifications = DB::table('notifications')
            ->where('receiver_id', $sellerId)
            ->where('bell', 'active')
            ->where('status', 'unread')
            ->orderByDesc('notification_id')
            ->limit(1)
            ->get();

        foreach ($activeNotifications as $row) {
            $i++;
            $notificationId = $row->notification_id;
            $reason = $row->reason;

            $n = [];
            $n['notification_id'] = $notificationId;
            $n['sender_id'] = $row->sender_id;
            $n['order_id'] = $row->order_id;
            $n['reason'] = $reason;
            $n['date'] = $row->date;
            $n['status'] = $row->status;

            // Get sender details
            $senderIdRaw = $row->sender_id;
            $senderUserName = '';
            $senderImage = '';

            if (str_contains((string) $senderIdRaw, 'admin')) {
                $adminId = (int) str_replace('admin_', '', (string) $senderIdRaw);
                $senderUserName = 'Admin';
                $admin = DB::table('admins')->where('admin_id', $adminId)->first();
                $adminImage = $admin->admin_image ?? '';
                if (empty($adminImage)) {
                    $senderImage = "$siteUrl/admin/admin_images/empty-image.png";
                } else {
                    $senderImage = $this->legacyData->getImageUrl('admins', $adminImage);
                }
            } else {
                $sender = DB::table('sellers')
                    ->where('seller_id', $senderIdRaw)
                    ->first();
                $senderUserName = $sender->seller_user_name ?? '';
                $imgVal = $sender->seller_image ?? '';
                if (empty($imgVal)) {
                    $senderImage = "$siteUrl/user_images/empty-image.png";
                } else {
                    $senderImage = $this->legacyData->getImageUrl2('sellers', 'seller_image', $imgVal);
                }
            }

            $n['sender_user_name'] = $senderUserName;
            $n['sender_image'] = $senderImage;
            $n['message'] = $this->getNotificationMessage($reason);

            // Check if there are more active unread notifications
            $moreCount = DB::table('notifications')
                ->where('receiver_id', $sellerId)
                ->where('bell', 'active')
                ->where('status', 'unread')
                ->where('notification_id', '!=', $notificationId)
                ->count();

            if ($moreCount > 0) {
                $n['more'] = "
    <div class='clearfix'></div>
      <a href='$siteUrl/notifications'><span class='badge badge-success float-right'>$moreCount More</span></a>
    <div class='clearfix'></div>
	";
            } else {
                $n['more'] = '';
            }

            $data[$i] = $n;
        }

        // Mark all active unread notifications as "over"
        DB::table('notifications')
            ->where('receiver_id', $sellerId)
            ->where('status', 'unread')
            ->where('bell', 'active')
            ->update(['bell' => 'over']);

        return response()->json($data);
    }

    /**
     * POST /includes/close_cookies_footer.php
     * Sets a cookie for cookie bar or announcement close.
     */
    public function closeCookiesFooter(Request $request): Response
    {
        $close = (string) $request->input('close', '');

        if ($close === 'close_cookies') {
            $cookieName = 'close_cookie';
            $cookieValue = 'Cookie Bar';
        } else {
            $cookieName = 'close_announcement';
            $cookieValue = (string) $request->input('time', '');
        }

        // 30 days = 43200 minutes
        $cookie = Cookie::make($cookieName, $cookieValue, 43200, '/');

        return response('')->withCookie($cookie);
    }

    /**
     * Map notification reason codes to human-readable messages.
     * Replaces: includes/comp/notification_reasons.php
     */
    private function getNotificationMessage(string $reason): string
    {
        $messages = [
            'referral_approved' => 'Has approved your user referral. you have got the commission.',
            'proposal_referral_approved' => 'Has approved your proposal referral. you have got the commission.',
            'modification' => 'Has sent modification to your proposal.',
            'declined' => 'Has Declined your proposal. Please submit a valid proposal.',
            'approved' => 'Has approved your proposal. Thanks for posting.',
            'unapproved_request' => 'Has unapproved your request. Please submit a valid request.',
            'approved_request' => 'Has approved your request. Thanks for posting.',
            'offer' => 'Has just sent you an offer on your request click here to view.',
            'order' => 'Has just sent you an order.',
            'order_tip' => 'Has has given you an tip.',
            'order_message' => 'Updated the order.',
            'order_revision' => 'Requested for a revision.',
            'order_completed' => 'Completed your order.',
            'order_delivered' => 'Delivered your order.',
            'cancellation_request' => 'Wants to cancel the order.',
            'decline_cancellation_request' => 'Declined your cancellation request.',
            'accept_cancellation_request' => 'Accepted cancellation request.',
            'cancelled_by_customer_support' => 'Order has been cancelled by admin.',
            'buyer_order_review' => 'Please review and rate your buyer.',
            'seller_order_review' => 'Please review and rate your seller.',
            'order_cancelled' => 'Your order has been cancelled.',
            'withdrawal_declined' => 'your withdrawal request has been declined. click here to view reason.',
            'withdrawal_approved' => 'your withdrawal request has been completed. click here to view.',
            'extendTimeDeclined' => 'Has Declined your extention.',
            'extendTimeAccepted' => 'Has accepted your extension. Time was increased successfully.',
            'buyerExtendTimeAccepted' => 'Time increased successfully.',
            'ticket_reply' => 'just responded to your ticket.',
        ];

        return $messages[$reason] ?? '';
    }

    /**
     * POST /search-knowledge
     * Returns JSON with knowledge bank articles.
     * Replaces: search-knowledge.php
     */
    public function searchKnowledge(Request $request): JsonResponse
    {
        $search = strip_tags((string) $request->input('q', ''));
        $cat = (string) $request->input('cat', '');
        $siteLanguage = (int) session('siteLanguage', 1);

        if (! empty($cat)) {
            $articles = DB::table('knowledge_bank as kb')
                ->join('article_cat as ac', 'ac.article_cat_id', '=', 'kb.cat_id')
                ->where('kb.cat_id', $cat)
                ->where('kb.language_id', $siteLanguage)
                ->orderByDesc('kb.article_id')
                ->get();
        } else {
            $articles = DB::table('knowledge_bank as kb')
                ->join('article_cat as ac', 'ac.article_cat_id', '=', 'kb.cat_id')
                ->where('kb.article_heading', 'like', "%{$search}%")
                ->where('kb.language_id', $siteLanguage)
                ->orderByDesc('kb.article_id')
                ->get();
        }

        $output = [];

        if ($articles->count() > 0) {
            $output['results'] = $articles->toArray();
            $output['count'] = $articles->count();
        } else {
            $output['count'] = 0;
            $output['message'] = "Sorry, we couldn't find any results for your search.";
        }

        return response()->json($output);
    }

    /**
     * Get a language string from session-stored lang array.
     * Falls back to default if not found.
     */
    private function getLangString(?string $group, string $key, string $default): string
    {
        $lang = session('lang', []);
        if ($group !== null) {
            return $lang[$group][$key] ?? $default;
        }

        return $lang[$key] ?? $default;
    }
}
