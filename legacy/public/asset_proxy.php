<?php
declare(strict_types=1);

$relativePath = isset($_GET['path']) ? (string) $_GET['path'] : '';
$relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');

if ($relativePath === '' || str_contains($relativePath, "\0") || str_contains($relativePath, '..')) {
    http_response_code(404);
    echo 'Not Found';
    exit;
}

$platformBase = realpath(
    __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . 'Platform'
);
if ($platformBase === false) {
    http_response_code(500);
    echo 'Platform path not found';
    exit;
}

$targetFile = realpath($platformBase . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath));
if ($targetFile === false || !is_file($targetFile) || strncmp($targetFile, $platformBase, strlen($platformBase)) !== 0) {
    http_response_code(404);
    echo 'Not Found';
    exit;
}

$extension = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
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
} else {
    $detectedMime = function_exists('mime_content_type') ? mime_content_type($targetFile) : false;
    if (is_string($detectedMime) && $detectedMime !== '') {
        header('Content-Type: ' . $detectedMime);
    } else {
        header('Content-Type: application/octet-stream');
    }
}

header('Content-Length: ' . (string) filesize($targetFile));
header('Cache-Control: public, max-age=2592000');
readfile($targetFile);
