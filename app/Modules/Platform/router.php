<?php
// Router for PHP built-in server to support extensionless URLs and Apache-like rewrites.
$uriPath = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$uriPath = $uriPath ?: "/";
$docRoot = __DIR__;
$fullPath = $docRoot . $uriPath;

if ($uriPath !== "/" && is_file($fullPath)) {
    return false;
}

// For static assets, do not fall back to index.php when missing.
if (preg_match('/\.(?:js|css|map|png|jpe?g|gif|svg|ico|woff2?|ttf|eot|webp|mp4|webm|pdf|json)$/i', $uriPath)) {
    http_response_code(404);
    echo "Not Found";
    return true;
}

if (is_dir($fullPath)) {
    $indexFile = rtrim($fullPath, "/\\") . DIRECTORY_SEPARATOR . "index.php";
    if (is_file($indexFile)) {
        $oldCwd = getcwd();
        chdir(dirname($indexFile));
        require $indexFile;
        chdir($oldCwd);
        return true;
    }
}

$phpPath = $docRoot . rtrim($uriPath, "/") . ".php";
if (is_file($phpPath)) {
    $oldCwd = getcwd();
    chdir(dirname($phpPath));
    require $phpPath;
    chdir($oldCwd);
    return true;
}

$trimmed = trim($uriPath, "/");
$segments = $trimmed === "" ? array() : explode("/", $trimmed);

// Mimic categories/.htaccess: /categories/{cat}/{child?}
if (!empty($segments) && $segments[0] === "categories" && isset($segments[1])) {
    $_GET["cat_url"] = urldecode($segments[1]);
    if (isset($segments[2])) {
        $_GET["cat_child_url"] = urldecode($segments[2]);
    }
    $_REQUEST = array_merge($_REQUEST, $_GET);
    $categoryPath = $docRoot . DIRECTORY_SEPARATOR . "categories" . DIRECTORY_SEPARATOR . "category.php";
    $oldCwd = getcwd();
    chdir(dirname($categoryPath));
    require $categoryPath;
    chdir($oldCwd);
    return true;
}

// Mimic root .htaccess single-segment slug handler.
if (count($segments) === 1 && preg_match('/^[0-9a-zA-Z_-]+$/', $segments[0])) {
    $_GET["slug"] = $segments[0];
    $_REQUEST = array_merge($_REQUEST, $_GET);
    $handlerPath = $docRoot . DIRECTORY_SEPARATOR . "handler.php";
    $oldCwd = getcwd();
    chdir(dirname($handlerPath));
    require $handlerPath;
    chdir($oldCwd);
    return true;
}

$fallbackPath = $docRoot . DIRECTORY_SEPARATOR . "index.php";
$oldCwd = getcwd();
chdir(dirname($fallbackPath));
require $fallbackPath;
chdir($oldCwd);
return true;
