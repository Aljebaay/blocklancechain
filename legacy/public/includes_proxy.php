<?php
declare(strict_types=1);

$relativePath = isset($_GET['path']) ? (string) $_GET['path'] : '';
$relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');

if (
    $relativePath === '' ||
    str_contains($relativePath, "\0") ||
    str_contains($relativePath, '..')
) {
    http_response_code(404);
    echo 'Not Found';
    exit;
}

if (!preg_match('/\.php$/i', $relativePath)) {
    $relativePath .= '.php';
}

$platformIncludesBase = realpath(
    __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . 'Platform' . DIRECTORY_SEPARATOR . 'includes'
);

if ($platformIncludesBase === false) {
    http_response_code(500);
    echo 'Platform includes path not found';
    exit;
}

$targetFile = realpath($platformIncludesBase . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath));
if (
    $targetFile === false ||
    !is_file($targetFile) ||
    strncmp($targetFile, $platformIncludesBase, strlen($platformIncludesBase)) !== 0
) {
    http_response_code(404);
    echo 'Not Found';
    exit;
}

$oldCwd = getcwd();
chdir(dirname($targetFile));
require $targetFile;
chdir($oldCwd ?: __DIR__);
