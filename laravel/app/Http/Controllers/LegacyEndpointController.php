<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Dispatches requests to legacy PHP handlers (config/endpoints.php).
 * Syncs Laravel Request to superglobals, runs bootstrap/dispatch.php,
 * and returns the legacy output so behavior and appearance stay identical.
 */
class LegacyEndpointController extends Controller
{
    /**
     * Run the legacy endpoint handler for the current request path.
     * Endpoint id is resolved from config/endpoints.php by matching path (without .php).
     */
    public function dispatch(Request $request): Response
    {
        $uri = trim($request->path(), '/');
        $endpointId = $this->resolveEndpointId($uri);
        if ($endpointId === null) {
            abort(404);
        }

        $this->syncRequestToSuperglobals($request);

        $legacyRoot = base_path('..');
        $dispatchPath = $legacyRoot . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'dispatch.php';
        if (!is_file($dispatchPath)) {
            abort(500, 'Legacy dispatcher not found.');
        }

        ob_start();
        $capturedByShutdown = false;

        register_shutdown_function(function () use (&$capturedByShutdown) {
            if ($capturedByShutdown) {
                return;
            }
            if (ob_get_level() > 0) {
                $capturedByShutdown = true;
                $content = ob_get_clean();
                if ($content !== false && $content !== '') {
                    echo $content;
                }
                exit;
            }
        });

        $__blcRoot = $legacyRoot;
        $__blcEndpointId = $endpointId;

        try {
            require $dispatchPath;
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        // Handler returned without exit()
        if (ob_get_level() > 0) {
            $content = ob_get_clean();
            return new Response($content ?? '', 200, ['Content-Type' => 'text/html; charset=UTF-8']);
        }

        return new Response('', 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    private function syncRequestToSuperglobals(Request $request): void
    {
        $_GET = $request->query->all();
        $_POST = $request->request->all();
        $_REQUEST = array_merge($_GET, $_POST);

        $uri = $request->getRequestUri();
        $method = $request->getMethod();
        $queryString = $request->getQueryString() ?? '';

        $_SERVER['REQUEST_URI'] = $uri;
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['QUERY_STRING'] = $queryString;

        // Preserve other common keys Laravel may have overwritten
        if (!$request->headers->has('Content-Type')) {
            $_SERVER['CONTENT_TYPE'] = $request->header('Content-Type', '');
        }
    }

    private function resolveEndpointId(string $uri): ?string
    {
        $file = base_path('../config/endpoints.php');
        if (!is_file($file)) {
            return null;
        }
        $endpoints = require $file;
        if (!is_array($endpoints)) {
            return null;
        }
        foreach ($endpoints as $id => $entry) {
            if (!is_array($entry) || !isset($entry['path']) || !is_string($entry['path'])) {
                continue;
            }
            $path = str_replace('\\', '/', $entry['path']);
            if (!str_ends_with($path, '.php')) {
                continue;
            }
            $entryUri = trim(substr($path, 0, -4), '/');
            if ($entryUri === $uri) {
                return $id;
            }
        }
        return null;
    }
}
