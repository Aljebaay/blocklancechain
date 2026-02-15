<?php
declare(strict_types=1);

$basePath = dirname(__DIR__, 2);

$checks = [
    [
        'id' => 'register-geo-no-unserialize',
        'file' => 'app/Modules/Platform/includes/register_login_forgot.php',
        'contains' => [
            'https://www.geoplugin.net/json.gp',
        ],
        'not_contains' => [
            'unserialize(file_get_contents(',
        ],
    ],
    [
        'id' => 'delete-plugin-hardened',
        'file' => 'app/Modules/Platform/admin/delete_plugin.php',
        'contains' => [
            "admin_csrf_require('delete_plugin'",
            "preg_match('/^[A-Za-z0-9_-]+$/'",
            '$pluginsRoot = realpath(',
            '$pluginPath = realpath(',
        ],
    ],
    [
        'id' => 'delete-user-csrf',
        'file' => 'app/Modules/Platform/admin/delete_user.php',
        'contains' => [
            "admin_csrf_require('delete_user'",
        ],
    ],
    [
        'id' => 'approve-payout-csrf',
        'file' => 'app/Modules/Platform/admin/approve_payout.php',
        'contains' => [
            "admin_csrf_require('approve_payout'",
        ],
    ],
    [
        'id' => 'view-users-delete-token-link',
        'file' => 'app/Modules/Platform/admin/view_users.php',
        'contains' => [
            "admin_csrf_token('delete_user')",
            '&csrf_token=',
        ],
    ],
    [
        'id' => 'type-status-auth',
        'file' => 'app/Modules/Platform/conversations/typeStatus.php',
        'contains' => [
            "isset(\$_SESSION['seller_user_name'])",
            'http_response_code(401)',
        ],
    ],
    [
        'id' => 'upload-file-auth-and-upload-check',
        'file' => 'app/Modules/Platform/conversations/upload_file.php',
        'contains' => [
            "isset(\$_SESSION['seller_user_name'])",
            'is_uploaded_file(',
        ],
    ],
    [
        'id' => 'admin-logs-parameterized-filters',
        'file' => 'app/Modules/Platform/admin/admin_logs.php',
        'contains' => [
            '$whereSql = !empty($whereClauses)',
            ':filter_date',
            ':filter_admin_id',
        ],
        'not_contains' => [
            '$filter_query = "where date LIKE',
        ],
    ],
    [
        'id' => 'forgot-password-uses-expiring-token',
        'file' => 'app/Modules/Platform/includes/register_login_forgot.php',
        'contains' => [
            "includes/password_reset.php",
            'blc_password_reset_issue(',
            'change_password?selector=',
            '&token=',
        ],
        'not_contains' => [
            'change_password?username=',
            '&code=$seller_pass',
        ],
    ],
    [
        'id' => 'change-password-verifies-reset-token',
        'file' => 'app/Modules/Platform/change_password.php',
        'contains' => [
            'blc_password_reset_resolve(',
            'blc_password_reset_mark_used(',
        ],
        'not_contains' => [
            '"seller_pass" => $code',
        ],
    ],
    [
        'id' => 'session-bootstrap-cookie-hardening',
        'file' => 'app/Modules/Platform/includes/session_bootstrap.php',
        'contains' => [
            "session.use_strict_mode",
            "session_set_cookie_params",
            "'samesite' => 'Lax'",
            'blc_session_regenerate_id_safe',
            '__blc_regenerate_pending',
        ],
    ],
    [
        'id' => 'seller-login-regenerates-session',
        'file' => 'app/Modules/Platform/login.php',
        'contains' => [
            'blc_session_regenerate_id_safe(true);',
        ],
        'not_contains' => [
            'session_regenerate_id(true);',
        ],
    ],
    [
        'id' => 'admin-login-regenerates-session',
        'file' => 'app/Modules/Platform/admin/login.php',
        'contains' => [
            'blc_session_regenerate_id_safe(true);',
        ],
        'not_contains' => [
            'session_regenerate_id(true);',
        ],
    ],
    [
        'id' => 'register-login-regenerates-session-safely',
        'file' => 'app/Modules/Platform/includes/register_login_forgot.php',
        'contains' => [
            'blc_session_regenerate_id_safe(true);',
        ],
        'not_contains' => [
            'session_regenerate_id(true);',
        ],
    ],
    [
        'id' => 'admin-logs-csrf-links',
        'file' => 'app/Modules/Platform/admin/admin_logs.php',
        'contains' => [
            "admin_csrf_token('delete_log')",
            "admin_csrf_token('delete_all_logs')",
            'delete_all_logs&csrf_token=',
            'delete_log=<?= $id; ?>&csrf_token=',
        ],
    ],
    [
        'id' => 'delete-log-csrf-enforced',
        'file' => 'app/Modules/Platform/admin/delete_log.php',
        'contains' => [
            "admin_csrf_require('delete_log'",
        ],
    ],
    [
        'id' => 'delete-all-logs-csrf-enforced',
        'file' => 'app/Modules/Platform/admin/delete_all_logs.php',
        'contains' => [
            "admin_csrf_require('delete_all_logs'",
        ],
    ],
    [
        'id' => 'cancel-order-csrf',
        'file' => 'app/Modules/Platform/admin/cancel_order.php',
        'contains' => [
            "admin_csrf_require('cancel_order'",
            'seller_id=:seller_id',
        ],
    ],
    [
        'id' => 'view-orders-cancel-link-has-token',
        'file' => 'app/Modules/Platform/admin/view_orders.php',
        'contains' => [
            "admin_csrf_token('cancel_order')",
            'cancel_order=<?= $order_id; ?>&csrf_token=',
        ],
    ],
    [
        'id' => 'view-sellers-has-action-tokens',
        'file' => 'app/Modules/Platform/admin/view_sellers.php',
        'contains' => [
            "admin_csrf_token('verify_email')",
            "admin_csrf_token('ban_seller')",
            "admin_csrf_token('unblock_seller')",
            '&csrf_token=',
        ],
    ],
    [
        'id' => 'referrals-csrf-actions',
        'file' => 'app/Modules/Platform/admin/view_referrals.php',
        'contains' => [
            "admin_csrf_token('approve_referral')",
            "admin_csrf_token('decline_referral')",
            '&csrf_token=',
        ],
    ],
    [
        'id' => 'proposal-referrals-csrf-actions',
        'file' => 'app/Modules/Platform/admin/view_proposal_referrals.php',
        'contains' => [
            "admin_csrf_token('approve_proposal_referral')",
            "admin_csrf_token('decline_proposal_referral')",
            '&csrf_token=',
        ],
    ],
    [
        'id' => 'admin-dispatch-routes-through-index',
        'file' => 'bootstrap/dispatch.php',
        'contains' => [
            "strpos(\$__blcEndpointId, 'admin.') === 0",
            'directAdminAllowList',
            'require $adminIndex;',
        ],
    ],
    [
        'id' => 'download-endpoints-order-conversation-binding',
        'file' => 'app/Modules/Platform/orderIncludes/download.php',
        'contains' => [
            '"c_id" => $c_id,"order_id"=>$order_id',
            'order_id=:order_id',
        ],
        'not_contains' => [
            "where order_id='\$order_id'",
        ],
    ],
    [
        'id' => 'admin-download-parameterized-order-query',
        'file' => 'app/Modules/Platform/admin/includes/download.php',
        'contains' => [
            'order_id=:order_id',
            '"c_id" => $c_id,"order_id"=>$order_id',
        ],
        'not_contains' => [
            "where order_id='\$order_id'",
        ],
    ],
    [
        'id' => 'single-request-csrf-and-escaping',
        'file' => 'app/Modules/Platform/admin/single_request.php',
        'contains' => [
            'admin_csrf_require(',
            'admin_e(',
            'csrf_token_reply',
            'csrf_token_status',
            'admin_id = :admin_id',
        ],
    ],
];

$failed = 0;
$passed = 0;

foreach ($checks as $check) {
    $filePath = $basePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $check['file']);
    if (!is_file($filePath)) {
        echo "FAIL {$check['id']} missing file: {$check['file']}\n";
        $failed++;
        continue;
    }

    $content = file_get_contents($filePath);
    if (!is_string($content)) {
        echo "FAIL {$check['id']} cannot read file: {$check['file']}\n";
        $failed++;
        continue;
    }

    $ok = true;

    if (isset($check['contains']) && is_array($check['contains'])) {
        foreach ($check['contains'] as $needle) {
            if (!is_string($needle) || $needle === '') {
                continue;
            }
            if (strpos($content, $needle) === false) {
                echo "FAIL {$check['id']} missing expected text: {$needle}\n";
                $ok = false;
            }
        }
    }

    if (isset($check['not_contains']) && is_array($check['not_contains'])) {
        foreach ($check['not_contains'] as $needle) {
            if (!is_string($needle) || $needle === '') {
                continue;
            }
            if (strpos($content, $needle) !== false) {
                echo "FAIL {$check['id']} found forbidden text: {$needle}\n";
                $ok = false;
            }
        }
    }

    if ($ok) {
        echo "PASS {$check['id']}\n";
        $passed++;
    } else {
        $failed++;
    }
}

$sessionDir = $basePath . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . 'Platform' . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'sessions';
$sessionArtifacts = glob($sessionDir . DIRECTORY_SEPARATOR . 'sess_*');
if ($sessionArtifacts === false) {
    echo "FAIL sessions-artifacts cannot inspect session dir\n";
    $failed++;
} elseif (count($sessionArtifacts) > 0) {
    echo "FAIL sessions-artifacts runtime session files are tracked/present under app/Modules/Platform/storage/sessions\n";
    foreach ($sessionArtifacts as $artifact) {
        echo "  - " . str_replace($basePath . DIRECTORY_SEPARATOR, '', $artifact) . "\n";
    }
    $failed++;
} else {
    echo "PASS sessions-artifacts\n";
    $passed++;
}

echo "Security checks summary: passed={$passed} failed={$failed}\n";
exit($failed > 0 ? 1 : 0);
