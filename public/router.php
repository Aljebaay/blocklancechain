<?php
declare(strict_types=1);

$uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uriPath = $uriPath ?: '/';
$docRoot = __DIR__;
$fullPath = $docRoot . $uriPath;
$platformBasePath = realpath(
    __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . 'Platform'
);
$laravelPublicPath = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'laravel' . DIRECTORY_SEPARATOR . 'public');
$bridgePrefix = '/_app';
$migrateFetchToggle = filter_var(getenv('MIGRATE_REQUESTS_FETCH_SUBCATEGORY') ?: 'false', FILTER_VALIDATE_BOOLEAN);
$migratePricingToggle = filter_var(getenv('MIGRATE_PROPOSAL_PRICING_CHECK') ?: 'false', FILTER_VALIDATE_BOOLEAN);
$migrateApisToggle = filter_var(getenv('MIGRATE_APIS_INDEX') ?: 'false', FILTER_VALIDATE_BOOLEAN);

function blc_require_laravel(string $laravelIndex, string $targetUri): array
{
    $oldUri = $_SERVER['REQUEST_URI'] ?? $targetUri;
    $oldScript = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $oldPhpSelf = $_SERVER['PHP_SELF'] ?? '/index.php';
    $oldCwd = getcwd();
    $buffer = '';
    $status = 500;

    ob_start();
    try {
        $_SERVER['REQUEST_URI'] = $targetUri;
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['PHP_SELF'] = '/index.php';
        chdir(dirname($laravelIndex));
        require $laravelIndex;
        $buffer = (string) ob_get_contents();
        $status = http_response_code();
        if ($status === false) {
            $status = 200;
        }
    } catch (Throwable $bridgeException) {
        $buffer = '';
        $status = 500;
    } finally {
        ob_end_clean();
        if ($oldCwd !== false) {
            chdir($oldCwd);
        }
        $_SERVER['REQUEST_URI'] = $oldUri;
        $_SERVER['SCRIPT_NAME'] = $oldScript;
        $_SERVER['PHP_SELF'] = $oldPhpSelf;
    }

    return ['status' => $status, 'body' => $buffer];
}

if ($uriPath !== '/' && is_file($fullPath)) {
    return false;
}

if (strncmp($uriPath, $bridgePrefix, strlen($bridgePrefix)) === 0) {
    $laravelIndex = $laravelPublicPath !== false
        ? $laravelPublicPath . DIRECTORY_SEPARATOR . 'index.php'
        : __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'laravel' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.php';

    $relative = substr($uriPath, strlen($bridgePrefix));
    $relativePath = $relative === '' ? '/' : $relative;
    $candidate = $laravelPublicPath !== false
        ? realpath($laravelPublicPath . str_replace('/', DIRECTORY_SEPARATOR, $relativePath))
        : false;

    if (
        $candidate !== false &&
        $laravelPublicPath !== false &&
        is_file($candidate) &&
        strncmp($candidate, $laravelPublicPath, strlen($laravelPublicPath)) === 0
    ) {
        $extension = strtolower(pathinfo($candidate, PATHINFO_EXTENSION));
        $mime = @mime_content_type($candidate) ?: '';
        if ($mime !== '') {
            header('Content-Type: ' . $mime);
        } elseif ($extension === 'json') {
            header('Content-Type: application/json; charset=UTF-8');
        }
        header('Content-Length: ' . (string) filesize($candidate));
        readfile($candidate);
        return true;
    }

    if (is_file($laravelIndex)) {
        $oldCwd = getcwd();
        chdir(dirname($laravelIndex));
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['PHP_SELF'] = '/index.php';
        require $laravelIndex;
        chdir($oldCwd ?: $docRoot);
        return true;
    }
}

if ($migrateFetchToggle && $uriPath === '/requests/fetch_subcategory') {
    $laravelIndex = $laravelPublicPath !== false
        ? $laravelPublicPath . DIRECTORY_SEPARATOR . 'index.php'
        : __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'laravel' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.php';
    if (is_file($laravelIndex)) {
        $targetUri = '/_app/migrate/requests/fetch_subcategory' . (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '' ? '?' . $_SERVER['QUERY_STRING'] : '');
        $result = blc_require_laravel($laravelIndex, $targetUri);
        if ($result['status'] === 200 && $result['body'] !== '') {
            echo $result['body'];
            return true;
        }
    }
}

if ($migratePricingToggle && $uriPath === '/proposals/ajax/check/pricing') {
    $laravelIndex = $laravelPublicPath !== false
        ? $laravelPublicPath . DIRECTORY_SEPARATOR . 'index.php'
        : __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'laravel' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.php';
    if (is_file($laravelIndex)) {
        $targetUri = '/_app/migrate/proposals/ajax/check/pricing' . (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '' ? '?' . $_SERVER['QUERY_STRING'] : '');
        $result = blc_require_laravel($laravelIndex, $targetUri);
        if ($result['status'] === 200 && $result['body'] !== '') {
            echo $result['body'];
            return true;
        }
    }
}

if ($migrateApisToggle && $uriPath === '/apis/index.php') {
    $laravelIndex = $laravelPublicPath !== false
        ? $laravelPublicPath . DIRECTORY_SEPARATOR . 'index.php'
        : __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'laravel' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.php';
    if (is_file($laravelIndex)) {
        $targetUri = '/_app/migrate/apis/index.php' . (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '' ? '?' . $_SERVER['QUERY_STRING'] : '');
        $result = blc_require_laravel($laravelIndex, $targetUri);
        if ($result['status'] === 200 && $result['body'] !== '') {
            echo $result['body'];
            return true;
        }
    }
}

if (preg_match('/\.(?:js|css|map|png|jpe?g|gif|svg|ico|woff2?|ttf|otf|eot|webp|mp4|webm|pdf|json|mp3|wav|ogg)$/i', $uriPath)) {
    $platformStaticPath = realpath(
        __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . 'Platform' . str_replace('/', DIRECTORY_SEPARATOR, $uriPath)
    );
    if (
        $platformStaticPath !== false &&
        $platformBasePath !== false &&
        is_file($platformStaticPath) &&
        strncmp($platformStaticPath, $platformBasePath, strlen($platformBasePath)) === 0
    ) {
        $extension = strtolower(pathinfo($platformStaticPath, PATHINFO_EXTENSION));
        $contentTypes = [
            'css' => 'text/css; charset=UTF-8',
            'js' => 'application/javascript; charset=UTF-8',
            'map' => 'application/json; charset=UTF-8',
            'json' => 'application/json; charset=UTF-8',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
            'webp' => 'image/webp',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'otf' => 'font/otf',
            'eot' => 'application/vnd.ms-fontobject',
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'pdf' => 'application/pdf',
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'ogg' => 'audio/ogg',
        ];
        if (isset($contentTypes[$extension])) {
            header('Content-Type: ' . $contentTypes[$extension]);
        }
        header('Content-Length: ' . (string) filesize($platformStaticPath));
        readfile($platformStaticPath);
        return true;
    }

    http_response_code(404);
    echo 'Not Found';
    return true;
}

if (strncmp($uriPath, '/includes/', strlen('/includes/')) === 0) {
    $legacyPath = ltrim($uriPath, '/');
    if (!preg_match('/\.php$/i', $legacyPath)) {
        $legacyPath .= '.php';
    }

    $platformIncludesBase = $platformBasePath !== false ? realpath($platformBasePath . DIRECTORY_SEPARATOR . 'includes') : false;
    $legacyIncludePath = $platformBasePath !== false
        ? realpath($platformBasePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $legacyPath))
        : false;

    if (
        $legacyIncludePath !== false &&
        $platformIncludesBase !== false &&
        is_file($legacyIncludePath) &&
        strncmp($legacyIncludePath, $platformIncludesBase, strlen($platformIncludesBase)) === 0
    ) {
        $oldCwd = getcwd();
        chdir(dirname($legacyIncludePath));
        require $legacyIncludePath;
        chdir($oldCwd ?: $docRoot);
        return true;
    }

    http_response_code(404);
    echo 'Not Found';
    return true;
}

if (is_dir($fullPath)) {
    $indexFile = rtrim($fullPath, "/\\") . DIRECTORY_SEPARATOR . 'index.php';
    if (is_file($indexFile)) {
        $oldCwd = getcwd();
        chdir(dirname($indexFile));
        require $indexFile;
        chdir($oldCwd ?: $docRoot);
        return true;
    }
}

$phpPath = $docRoot . rtrim($uriPath, '/') . '.php';
if (is_file($phpPath)) {
    $oldCwd = getcwd();
    chdir(dirname($phpPath));
    require $phpPath;
    chdir($oldCwd ?: $docRoot);
    return true;
}

$trimmed = trim($uriPath, '/');
$segments = $trimmed === '' ? [] : explode('/', $trimmed);

if (!empty($segments) && $segments[0] === 'categories' && isset($segments[1])) {
    $_GET['cat_url'] = urldecode($segments[1]);
    if (isset($segments[2])) {
        $_GET['cat_child_url'] = urldecode($segments[2]);
    }
    $_REQUEST = array_merge($_REQUEST, $_GET);
    $target = $docRoot . DIRECTORY_SEPARATOR . 'categories' . DIRECTORY_SEPARATOR . 'category.php';
    $oldCwd = getcwd();
    chdir(dirname($target));
    require $target;
    chdir($oldCwd ?: $docRoot);
    return true;
}

if (!empty($segments) && $segments[0] === 'proposals' && count($segments) >= 3) {
    $reserved = ['proposal_files', 'ajax', 'sections', 'coupons'];
    if (!in_array($segments[1], $reserved, true)) {
        $_GET['username'] = urldecode($segments[1]);
        $_GET['proposal_url'] = urldecode(implode('/', array_slice($segments, 2)));
        $_REQUEST = array_merge($_REQUEST, $_GET);
        $target = $docRoot . DIRECTORY_SEPARATOR . 'proposals' . DIRECTORY_SEPARATOR . 'proposal.php';
        $oldCwd = getcwd();
        chdir(dirname($target));
        require $target;
        chdir($oldCwd ?: $docRoot);
        return true;
    }
}

if (!empty($segments) && $segments[0] === 'blog' && isset($segments[1]) && ctype_digit($segments[1])) {
    $_GET['id'] = $segments[1];
    $_REQUEST = array_merge($_REQUEST, $_GET);
    $target = $docRoot . DIRECTORY_SEPARATOR . 'blog' . DIRECTORY_SEPARATOR . 'post.php';
    $oldCwd = getcwd();
    chdir(dirname($target));
    require $target;
    chdir($oldCwd ?: $docRoot);
    return true;
}

if (!empty($segments) && $segments[0] === 'article' && isset($segments[1])) {
    $_GET['article_url'] = urldecode(implode('/', array_slice($segments, 1)));
    $_REQUEST = array_merge($_REQUEST, $_GET);
    $target = $docRoot . DIRECTORY_SEPARATOR . 'article' . DIRECTORY_SEPARATOR . 'article.php';
    $oldCwd = getcwd();
    chdir(dirname($target));
    require $target;
    chdir($oldCwd ?: $docRoot);
    return true;
}

if (!empty($segments) && $segments[0] === 'tags' && isset($segments[1])) {
    $_GET['tag'] = urldecode(implode('/', array_slice($segments, 1)));
    $_REQUEST = array_merge($_REQUEST, $_GET);
    $target = $docRoot . DIRECTORY_SEPARATOR . 'tags' . DIRECTORY_SEPARATOR . 'tag.php';
    $oldCwd = getcwd();
    chdir(dirname($target));
    require $target;
    chdir($oldCwd ?: $docRoot);
    return true;
}

if (!empty($segments) && $segments[0] === 'pages' && isset($segments[1])) {
    $_GET['slug'] = urldecode(implode('/', array_slice($segments, 1)));
    $_REQUEST = array_merge($_REQUEST, $_GET);
    $target = $docRoot . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . 'index.php';
    $oldCwd = getcwd();
    chdir(dirname($target));
    require $target;
    chdir($oldCwd ?: $docRoot);
    return true;
}

if (count($segments) === 1 && preg_match('/^[0-9a-zA-Z_-]+$/', $segments[0])) {
    $_GET['slug'] = $segments[0];
    $_REQUEST = array_merge($_REQUEST, $_GET);
    $target = $docRoot . DIRECTORY_SEPARATOR . 'handler.php';
    $oldCwd = getcwd();
    chdir(dirname($target));
    require $target;
    chdir($oldCwd ?: $docRoot);
    return true;
}

$fallbackPath = $docRoot . DIRECTORY_SEPARATOR . 'index.php';
$oldCwd = getcwd();
chdir(dirname($fallbackPath));
require $fallbackPath;
chdir($oldCwd ?: $docRoot);
return true;
