<?php
declare(strict_types=1);

$basePath = dirname(__DIR__);
$options = getopt('', ['manifest::', 'public::', 'force']);

$manifestPath = $options['manifest'] ?? ($basePath . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'endpoints.php');
$publicPath = $options['public'] ?? ($basePath . DIRECTORY_SEPARATOR . 'public');
$force = array_key_exists('force', $options);

if (!is_file($manifestPath)) {
    fwrite(STDERR, "Manifest file not found: {$manifestPath}\n");
    exit(1);
}

$manifest = require $manifestPath;
if (!is_array($manifest)) {
    fwrite(STDERR, "Manifest must return an array: {$manifestPath}\n");
    exit(1);
}

$created = 0;
$skipped = 0;

foreach ($manifest as $endpointId => $entry) {
    if (!is_array($entry) || !isset($entry['path']) || !is_string($entry['path'])) {
        continue;
    }

    $relativePath = str_replace('\\', '/', $entry['path']);
    if (!str_ends_with($relativePath, '.php')) {
        continue;
    }
    if ($relativePath === 'router.php') {
        continue;
    }

    $targetPath = $publicPath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    if (is_file($targetPath) && !$force) {
        $skipped++;
        continue;
    }

    $targetDir = dirname($targetPath);
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $dirPart = dirname($relativePath);
    $dirPart = str_replace('\\', '/', $dirPart);
    if ($dirPart === '.' || $dirPart === '/') {
        $segments = 0;
    } else {
        $segments = substr_count(trim($dirPart, '/'), '/') + 1;
    }
    $levelsToRoot = $segments + 1;

    $content = "<?php\n";
    $content .= '$__blcRoot = dirname(__DIR__, ' . $levelsToRoot . ");\n";
    $content .= '$__blcEndpointId = ' . var_export((string) $endpointId, true) . ";\n";
    $content .= "require_once \$__blcRoot . '/bootstrap/dispatch.php';\n";

    file_put_contents($targetPath, $content);
    $created++;
}

echo "Compat stubs generated.\n";
echo "Created: {$created}\n";
echo "Skipped: {$skipped}\n";
