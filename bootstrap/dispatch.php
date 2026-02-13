<?php
declare(strict_types=1);

if (!isset($__blcRoot) || !is_string($__blcRoot) || $__blcRoot === '') {
    $__blcRoot = dirname(__DIR__);
}

if (!isset($__blcEndpointId) || !is_string($__blcEndpointId) || $__blcEndpointId === '') {
    throw new RuntimeException('Missing $__blcEndpointId for dispatcher.');
}

require_once $__blcRoot . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'app.php';

$manifestFile = $__blcRoot . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'endpoints.php';
if (!is_file($manifestFile)) {
    throw new RuntimeException('Endpoint manifest not found: ' . $manifestFile);
}

$manifest = require $manifestFile;
if (!is_array($manifest) || !isset($manifest[$__blcEndpointId]) || !is_array($manifest[$__blcEndpointId])) {
    throw new RuntimeException('Unknown endpoint id: ' . $__blcEndpointId);
}

$entry = $manifest[$__blcEndpointId];
$appConfigFile = $__blcRoot . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'app.php';
$appConfig = is_file($appConfigFile) ? (require $appConfigFile) : [];
$switchConfig = (is_array($appConfig) && isset($appConfig['endpoint_switch']) && is_array($appConfig['endpoint_switch']))
    ? $appConfig['endpoint_switch']
    : [];

$handler = (isset($entry['handler']) && is_string($entry['handler'])) ? $entry['handler'] : '';
$fallback = (isset($entry['fallback']) && is_string($entry['fallback'])) ? $entry['fallback'] : '';
$fallbackOnError = isset($switchConfig['fallback_on_error']) ? (bool) $switchConfig['fallback_on_error'] : true;
$handlerAttempted = false;
$handlerFailure = null;

$useNew = false;
if (array_key_exists('enabled', $entry)) {
    $useNew = (bool) $entry['enabled'];
} else {
    $overrides = (isset($switchConfig['overrides']) && is_array($switchConfig['overrides']))
        ? $switchConfig['overrides']
        : [];
    if (array_key_exists($__blcEndpointId, $overrides)) {
        $useNew = (bool) $overrides[$__blcEndpointId];
    } else {
        $useNew = isset($switchConfig['use_new_default']) ? (bool) $switchConfig['use_new_default'] : false;
    }
}

$looksLikeScriptTarget = static function (string $target): bool {
    return str_ends_with($target, '.php') || str_contains($target, '/') || str_contains($target, '\\');
};

$resolveScriptTarget = static function (string $target) use ($__blcRoot): string {
    $normalized = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $target);
    if (preg_match('/^(?:[A-Za-z]:[\\\\\\/]|[\\\\\\/])/', $normalized) === 1) {
        return $normalized;
    }
    return $__blcRoot . DIRECTORY_SEPARATOR . ltrim($normalized, '\\/');
};

$executeClassTarget = static function (string $target): void {
    if (!class_exists($target)) {
        throw new RuntimeException('Endpoint handler class not found: ' . $target);
    }

    $instance = new $target();
    if (is_callable([$instance, 'handle'])) {
        $instance->handle(\App\Runtime\RuntimeContext::fromGlobals());
        return;
    }
    if (is_callable($instance)) {
        $instance(\App\Runtime\RuntimeContext::fromGlobals());
        return;
    }

    throw new RuntimeException('Endpoint handler is not callable: ' . $target);
};

if ($useNew && $handler !== '') {
    $handlerAttempted = true;
    try {
        if ($looksLikeScriptTarget($handler)) {
            $resolvedHandler = $resolveScriptTarget($handler);
            if (!is_file($resolvedHandler)) {
                throw new RuntimeException('Endpoint script not found: ' . $resolvedHandler);
            }
            $oldWorkingDirectory = getcwd();
            chdir(dirname($resolvedHandler));
            try {
                require $resolvedHandler;
            } finally {
                if ($oldWorkingDirectory !== false) {
                    chdir($oldWorkingDirectory);
                }
            }
        } else {
            $executeClassTarget($handler);
        }
        return;
    } catch (Throwable $exception) {
        if (!$fallbackOnError || $fallback === '') {
            throw $exception;
        }
        $handlerFailure = $exception;
    }
}

if ($fallback !== '') {
    if ($looksLikeScriptTarget($fallback)) {
        $resolvedFallback = $resolveScriptTarget($fallback);
        if (!is_file($resolvedFallback)) {
            throw new RuntimeException('Endpoint script not found: ' . $resolvedFallback);
        }
        $oldWorkingDirectory = getcwd();
        chdir(dirname($resolvedFallback));
        try {
            require $resolvedFallback;
        } finally {
            if ($oldWorkingDirectory !== false) {
                chdir($oldWorkingDirectory);
            }
        }
        return;
    }

    $executeClassTarget($fallback);
    return;
}

if ($handler !== '' && !$handlerAttempted) {
    if ($looksLikeScriptTarget($handler)) {
        $resolvedHandler = $resolveScriptTarget($handler);
        if (!is_file($resolvedHandler)) {
            throw new RuntimeException('Endpoint script not found: ' . $resolvedHandler);
        }
        $oldWorkingDirectory = getcwd();
        chdir(dirname($resolvedHandler));
        try {
            require $resolvedHandler;
        } finally {
            if ($oldWorkingDirectory !== false) {
                chdir($oldWorkingDirectory);
            }
        }
        return;
    }

    $executeClassTarget($handler);
    return;
}

if ($handlerFailure instanceof Throwable) {
    throw $handlerFailure;
}

throw new RuntimeException('Endpoint has no valid handler or fallback: ' . $__blcEndpointId);
