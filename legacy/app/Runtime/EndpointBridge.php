<?php
declare(strict_types=1);

namespace App\Runtime;

use RuntimeException;
use Throwable;

final class EndpointBridge
{
    /** @var array<string,array<string,mixed>> */
    private array $manifest;

    /** @var array<string,mixed> */
    private array $switchConfig;

    private string $basePath;

    /**
     * @param array<string,array<string,mixed>> $manifest
     * @param array<string,mixed> $switchConfig
     */
    public function __construct(array $manifest, array $switchConfig, ?string $basePath = null)
    {
        $this->manifest = $manifest;
        $this->switchConfig = $switchConfig;
        $this->basePath = $basePath ?: dirname(__DIR__, 2);
    }

    public static function fromConfig(): self
    {
        $basePath = dirname(__DIR__, 2);
        $manifestFile = $basePath . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'endpoints.php';
        $configFile = $basePath . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'app.php';

        if (!is_file($manifestFile)) {
            throw new RuntimeException("Endpoint manifest not found: {$manifestFile}");
        }

        $manifest = require $manifestFile;
        $appConfig = is_file($configFile) ? (require $configFile) : [];

        if (!is_array($manifest)) {
            throw new RuntimeException('Endpoint manifest must return an array.');
        }

        $switchConfig = [];
        if (is_array($appConfig) && isset($appConfig['endpoint_switch']) && is_array($appConfig['endpoint_switch'])) {
            $switchConfig = $appConfig['endpoint_switch'];
        }

        return new self($manifest, $switchConfig, $basePath);
    }

    public function dispatch(string $endpointId): void
    {
        if (!isset($this->manifest[$endpointId]) || !is_array($this->manifest[$endpointId])) {
            throw new RuntimeException("Unknown endpoint id: {$endpointId}");
        }

        $entry = $this->manifest[$endpointId];
        $handler = isset($entry['handler']) && is_string($entry['handler']) ? $entry['handler'] : '';
        $fallback = isset($entry['fallback']) && is_string($entry['fallback']) ? $entry['fallback'] : '';
        $useNew = $this->shouldUseNew($endpointId, $entry);
        $handlerAttempted = false;
        $handlerFailure = null;

        if ($useNew && $handler !== '') {
            $handlerAttempted = true;
            try {
                $this->dispatchHandler($handler);
                return;
            } catch (Throwable $exception) {
                if (!$this->fallbackOnError()) {
                    throw $exception;
                }
                if ($fallback === '') {
                    throw $exception;
                }
                $handlerFailure = $exception;
            }
        }

        if ($fallback !== '') {
            $this->dispatchFallback($fallback);
            return;
        }

        if ($handler !== '' && !$handlerAttempted) {
            $this->dispatchHandler($handler);
            return;
        }

        if ($handlerFailure instanceof Throwable) {
            throw $handlerFailure;
        }

        throw new RuntimeException("Endpoint has no valid handler or fallback: {$endpointId}");
    }

    public function dispatchFallback(string $fallbackPath): void
    {
        $resolvedPath = $this->resolvePath($fallbackPath);
        $this->includeWithWorkingDirectory($resolvedPath);
    }

    /**
     * @param array<string,mixed> $entry
     */
    private function shouldUseNew(string $endpointId, array $entry): bool
    {
        if (array_key_exists('enabled', $entry)) {
            return (bool) $entry['enabled'];
        }

        $overrides = [];
        if (isset($this->switchConfig['overrides']) && is_array($this->switchConfig['overrides'])) {
            $overrides = $this->switchConfig['overrides'];
        }

        if (array_key_exists($endpointId, $overrides)) {
            return (bool) $overrides[$endpointId];
        }

        return isset($this->switchConfig['use_new_default']) ? (bool) $this->switchConfig['use_new_default'] : false;
    }

    private function fallbackOnError(): bool
    {
        return isset($this->switchConfig['fallback_on_error']) ? (bool) $this->switchConfig['fallback_on_error'] : true;
    }

    private function dispatchHandler(string $handler): void
    {
        if ($this->looksLikeScriptPath($handler)) {
            $resolvedPath = $this->resolvePath($handler);
            $this->includeWithWorkingDirectory($resolvedPath);
            return;
        }

        if (!class_exists($handler)) {
            throw new RuntimeException("Endpoint handler class not found: {$handler}");
        }

        $instance = new $handler();
        if (is_callable([$instance, 'handle'])) {
            $instance->handle(RuntimeContext::fromGlobals());
            return;
        }

        if (is_callable($instance)) {
            $instance(RuntimeContext::fromGlobals());
            return;
        }

        throw new RuntimeException("Endpoint handler is not callable: {$handler}");
    }

    private function looksLikeScriptPath(string $handler): bool
    {
        return str_ends_with($handler, '.php') || str_contains($handler, '/') || str_contains($handler, '\\');
    }

    private function includeWithWorkingDirectory(string $path): void
    {
        if (!is_file($path)) {
            throw new RuntimeException("Endpoint script not found: {$path}");
        }

        $oldWorkingDirectory = getcwd();
        $targetDirectory = dirname($path);

        chdir($targetDirectory);
        try {
            require $path;
        } finally {
            if ($oldWorkingDirectory !== false) {
                chdir($oldWorkingDirectory);
            }
        }
    }

    private function resolvePath(string $path): string
    {
        $normalized = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path);

        if (preg_match('/^(?:[A-Za-z]:[\\\\\\/]|[\\\\\\/])/', $normalized) === 1) {
            return $normalized;
        }

        return $this->basePath . DIRECTORY_SEPARATOR . ltrim($normalized, '\\/');
    }
}
