<?php

namespace App\Http\Controllers\LegacyBridge;

use App\Http\Controllers\Controller;
use App\Support\LegacyScriptRunner;
use Illuminate\Http\Request;

class OrdersBridgeController extends Controller
{
    /**
        * Bridge selected orders/payments endpoints through the isolated legacy runner.
        * Toggle: MIGRATE_ORDERS (default false).
        */
    private const WHITELIST = [
        // Cart + checkout
        'cart.php',
        'cart_charge.php',
        'cart_paystack_charge.php',
        'cart_dusupay_charge.php',
        'cart_mercadopago_charge.php',
        'cart_crypto_charge.php',
        'checkout.php',
        'checkout_charge.php',
        // Order views
        'order.php',
        'order_details.php',
        // Gateway front controllers
        'paypal_charge.php',
        'paypal_order.php',
        'paystack_order.php',
        'mercadopago_order.php',
        'dusupay_order.php',
        'crypto_order.php',
        'cancel_payment.php',
    ];

    public function __invoke(Request $request, string $file)
    {
        if (!filter_var(env('MIGRATE_ORDERS', false), FILTER_VALIDATE_BOOLEAN)) {
            return response('', 404);
        }

        $clean = ltrim($file, '/');
        // Normalize missing extension
        if (!str_ends_with($clean, '.php')) {
            $clean .= '.php';
        }

        if (!in_array($clean, self::WHITELIST, true)) {
            return response('', 404);
        }

        $script = base_path('..' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $clean);
        if (!is_file($script)) {
            return response('', 500);
        }

        $legacyUri = '/' . $clean;
        $result = LegacyScriptRunner::run($request, $script, $legacyUri);
        if (!$result || ($result['body'] ?? '') === '') {
            return response('', 500);
        }

        $status = (int) ($result['status'] ?? 200);
        return response($result['body'], $status > 0 ? $status : 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}
