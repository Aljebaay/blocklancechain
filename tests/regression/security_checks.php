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
