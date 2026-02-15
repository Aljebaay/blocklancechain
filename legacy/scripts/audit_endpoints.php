<?php
declare(strict_types=1);

$basePath = dirname(__DIR__);
$outputPath = $basePath . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'endpoints.generated.php';
$platformRoot = $basePath . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . 'Platform';
$usePlatformTree = is_dir($platformRoot) && is_file($platformRoot . DIRECTORY_SEPARATOR . 'index.php');

if (!$usePlatformTree) {
    throw new RuntimeException('Endpoint source tree not found: app/Modules/Platform/');
}

$scanRoot = $platformRoot;
$handlerPrefix = 'app/Modules/Platform/';

$alwaysInclude = [
    'apis/index.php',
];

$allowedTopLevelDirs = [
    'admin',
    'article',
    'blog',
    'categories',
    'conversations',
    'feedback',
    'manage_orders',
    'orderIncludes',
    'pages',
    'paypal',
    'proposals',
    'requests',
    'tags',
    'ticket_support',
];

$excludedPathPrefixes = [
    '.git/',
    '.sessions/',
    '.vscode/',
    'app/',
    'bootstrap/',
    'config/',
    'docs/',
    'public/',
    'scripts/',
    'storage/',
    'tests/',
    'vendor/',
];

$entries = [];
$collisions = [];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($scanRoot, FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if (!$file instanceof SplFileInfo || !$file->isFile()) {
        continue;
    }

    $absolutePath = $file->getPathname();
    $relativePath = substr($absolutePath, strlen($scanRoot) + 1);
    $relativePath = str_replace('\\', '/', $relativePath);

    if (!str_ends_with($relativePath, '.php')) {
        continue;
    }

    $excluded = false;
    foreach ($excludedPathPrefixes as $excludedPrefix) {
        if (str_starts_with($relativePath, $excludedPrefix)) {
            $excluded = true;
            break;
        }
    }
    if ($excluded) {
        continue;
    }

    $isRootPhp = !str_contains($relativePath, '/');
    if (!$isRootPhp && !in_array($relativePath, $alwaysInclude, true)) {
        $topLevel = strtok($relativePath, '/');
        if ($topLevel === false || !in_array($topLevel, $allowedTopLevelDirs, true)) {
            continue;
        }
    }

    $endpointId = strtolower(str_replace('/', '.', substr($relativePath, 0, -4)));
    $endpointId = preg_replace('/[^a-z0-9_.-]/', '_', $endpointId);

    if ($endpointId === null || $endpointId === '') {
        continue;
    }

    if (isset($entries[$endpointId])) {
        $collisions[$endpointId][] = $relativePath;
        continue;
    }

    $entries[$endpointId] = [
        'path' => $relativePath,
        'handler' => $handlerPrefix . $relativePath,
    ];
}

ksort($entries);

$header = "<?php\n";
$header .= "declare(strict_types=1);\n\n";
$header .= "return ";
$content = $header . var_export($entries, true) . ";\n";

file_put_contents($outputPath, $content);

echo "Generated endpoint manifest: {$outputPath}\n";
echo "Scanned endpoint source root: app/Modules/Platform/\n";
echo "Total endpoints: " . count($entries) . "\n";

if ($collisions !== []) {
    echo "Collisions detected (kept first occurrence):\n";
    foreach ($collisions as $id => $paths) {
        echo " - {$id}: " . implode(', ', $paths) . "\n";
    }
}
