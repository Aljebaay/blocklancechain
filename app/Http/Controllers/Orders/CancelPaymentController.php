<?php

namespace App\Http\Controllers\Orders;

use App\Http\Controllers\Controller;
use App\Support\LegacyWriteConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CancelPaymentController extends Controller
{
    public function __invoke(Request $request)
    {
        if (!filter_var(env('MIGRATE_ORDERS', false), FILTER_VALIDATE_BOOLEAN)) {
            return response('', 404);
        }

        if (filter_var(env('FORCE_LARAVEL_ORDERS_FAIL', false), FILTER_VALIDATE_BOOLEAN)) {
            return response('', 500);
        }

        $this->bootstrapLegacySession($request);

        $sellerUserName = $_SESSION['seller_user_name'] ?? null;
        if (!is_string($sellerUserName) || $sellerUserName === '') {
            return $this->indexRedirect();
        }

        try {
            $seller = DB::connection('legacy')
                ->table('sellers')
                ->select('seller_id')
                ->where('seller_user_name', $sellerUserName)
                ->first();
        } catch (\Throwable $exception) {
            return response('', 500);
        }

        if (!$seller || !isset($seller->seller_id)) {
            return $this->indexRedirect();
        }

        $buyerId = (int) $seller->seller_id;
        [$lookupField, $lookupValue] = $this->resolveLookup($request);

        if ($lookupField === null || $lookupValue === null) {
            return $this->indexRedirect();
        }

        try {
            $order = DB::connection('legacy')
                ->table('temp_orders')
                ->where($lookupField, $lookupValue)
                ->where('buyer_id', $buyerId)
                ->first();
        } catch (\Throwable $exception) {
            return response('', 500);
        }

        if (!$order) {
            return $this->indexRedirect();
        }

        try {
            $write = LegacyWriteConnection::connection();

            $write->transaction(function () use ($write, $order): void {
                if ((string) ($order->type ?? '') === 'cart') {
                    $write->delete(
                        'delete from temp_orders where reference_no = ? and type = ?',
                        [(string) ($order->content_id ?? ''), 'cart_item']
                    );
                    $write->delete(
                        'delete from temp_extras where reference_no = ?',
                        [(string) ($order->content_id ?? '')]
                    );
                }

                $write->delete(
                    'delete from temp_orders where reference_no = ?',
                    [(string) ($order->reference_no ?? '')]
                );
            });
        } catch (\Throwable $exception) {
            return response('', 500);
        }

        if ((string) ($order->type ?? '') === 'orderExtendTime') {
            return $this->html("<script>window.close();</script>");
        }

        $targetUrl = $this->resolveTargetUrl($order);
        $targetUrl = str_replace("'", "\\'", $targetUrl);

        return $this->html("<script>window.open('{$targetUrl}','_self');</script>");
    }

    /**
     * @return array{0: string|null, 1: string|null}
     */
    private function resolveLookup(Request $request): array
    {
        $referenceNo = $request->query('reference_no');
        if (is_scalar($referenceNo) && (string) $referenceNo !== '') {
            return ['reference_no', (string) $referenceNo];
        }

        $token = $request->query('token');
        if (is_scalar($token) && (string) $token !== '') {
            return ['reference_no', (string) $token];
        }

        $id = $request->query('id');
        if (is_scalar($id) && (string) $id !== '') {
            return ['id', (string) $id];
        }

        return [null, null];
    }

    private function resolveTargetUrl(object $order): string
    {
        $type = (string) ($order->type ?? '');
        $contentId = (string) ($order->content_id ?? '');

        switch ($type) {
            case 'proposal':
                $proposal = DB::connection('legacy')
                    ->table('proposals')
                    ->select('proposal_url', 'proposal_seller_id')
                    ->where('proposal_id', $contentId)
                    ->first();

                if (!$proposal || !isset($proposal->proposal_url, $proposal->proposal_seller_id)) {
                    return 'index';
                }

                $seller = DB::connection('legacy')
                    ->table('sellers')
                    ->select('seller_user_name')
                    ->where('seller_id', $proposal->proposal_seller_id)
                    ->first();

                if (!$seller || !isset($seller->seller_user_name)) {
                    return 'index';
                }

                return 'proposals/' . rawurlencode((string) $seller->seller_user_name) . '/' . rawurlencode((string) $proposal->proposal_url);

            case 'featured_listing':
                return 'proposals/view_proposals';

            case 'cart':
                return 'cart_payment_options';

            case 'message_offer':
                return 'conversations/inbox';

            case 'request_offer':
                $offer = DB::connection('legacy')
                    ->table('send_offers')
                    ->select('request_id')
                    ->where('offer_id', $contentId)
                    ->first();

                if (!$offer || !isset($offer->request_id)) {
                    return 'index';
                }

                return 'requests/view_offers?request_id=' . rawurlencode((string) $offer->request_id);

            case 'orderTip':
                return 'order_details?order_id=' . rawurlencode($contentId);

            default:
                return 'index';
        }
    }

    private function html(string $body)
    {
        return response($body, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    private function indexRedirect()
    {
        return $this->html("<script> window.open('index','_self'); </script>");
    }

    private function bootstrapLegacySession(Request $request): void
    {
        if (session_status() === PHP_SESSION_ACTIVE && session_name() === 'PHPSESSID') {
            return;
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $_SESSION = [];

        @ini_set('session.use_strict_mode', '1');
        @ini_set('session.use_only_cookies', '1');

        session_name('PHPSESSID');
        $legacyCookieId = $request->cookies->get('PHPSESSID');
        if (is_string($legacyCookieId) && $legacyCookieId !== '') {
            @session_id($legacyCookieId);
        } else {
            unset($_COOKIE['PHPSESSID']);
            session_id(session_create_id());
        }

        $bootstrapped = false;
        $legacyBootstrap = base_path('legacy' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . 'Platform' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'session_bootstrap.php');
        if (is_file($legacyBootstrap)) {
            require_once $legacyBootstrap;
            if (function_exists('blc_bootstrap_session')) {
                blc_bootstrap_session();
                $bootstrapped = true;
            }
        }

        if (!$bootstrapped) {
            $projectBase = base_path('legacy');
            $platformBase = $projectBase . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . 'Platform';

            $candidatePaths = [
                $projectBase . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'sessions',
                $platformBase . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'sessions',
                $platformBase . DIRECTORY_SEPARATOR . '.sessions',
                rtrim(sys_get_temp_dir(), "\\/") . DIRECTORY_SEPARATOR . 'gig-zone_sessions',
            ];

            foreach ($candidatePaths as $path) {
                if (!is_dir($path)) {
                    @mkdir($path, 0777, true);
                }
                if (is_dir($path) && is_writable($path)) {
                    session_save_path($path);
                    break;
                }
            }

            if (session_status() !== PHP_SESSION_ACTIVE) {
                @session_start();
            }
        }

        if (!is_string($legacyCookieId) || $legacyCookieId === '') {
            $_SESSION = [];
        }
    }
}

