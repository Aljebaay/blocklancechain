<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\LegacyScriptRunner;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Catch-all controller that routes ANY unmigrated request to the corresponding
 * legacy PHP file inside legacy/public/ via LegacyScriptRunner (subprocess).
 *
 * Once every endpoint has been converted to a native Laravel controller this
 * class and the entire legacy/ directory can be deleted.
 */
class LegacyBridgeController extends Controller
{
    /** @var array<string, string> */
    private const MIME_MAP = [
        'css'   => 'text/css; charset=UTF-8',
        'js'    => 'application/javascript; charset=UTF-8',
        'map'   => 'application/json; charset=UTF-8',
        'json'  => 'application/json; charset=UTF-8',
        'png'   => 'image/png',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'gif'   => 'image/gif',
        'svg'   => 'image/svg+xml',
        'ico'   => 'image/x-icon',
        'webp'  => 'image/webp',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'   => 'font/ttf',
        'otf'   => 'font/otf',
        'eot'   => 'application/vnd.ms-fontobject',
        'mp4'   => 'video/mp4',
        'webm'  => 'video/webm',
        'pdf'   => 'application/pdf',
        'mp3'   => 'audio/mpeg',
        'wav'   => 'audio/wav',
        'ogg'   => 'audio/ogg',
    ];

    /**
     * Handle any request that has not been claimed by a native Laravel route.
     */
    public function handle(Request $request, ?string $path = null): Response
    {
        $path = '/' . ltrim($path ?? '', '/');
        $legacyPublic = $this->legacyPublicPath();
        $platformBase = $this->platformBasePath();

        // ── 1. Static assets (fast path — no subprocess needed) ───────────
        $staticResponse = $this->serveStaticAsset($path, $legacyPublic, $platformBase);
        if ($staticResponse !== null) {
            return $staticResponse;
        }

        // ── 2. /includes/* proxy ──────────────────────────────────────────
        if (str_starts_with($path, '/includes/')) {
            return $this->serveInclude($request, $path, $platformBase);
        }

        // ── 3. Direct PHP file  (e.g. /home.php, /admin/login.php) ───────
        $directPhp = $this->resolvePhpFile($legacyPublic, $path);
        if ($directPhp !== null) {
            return $this->runLegacy($request, $directPhp, $path);
        }

        // ── 4. Directory with index.php  ─────────────────────────────────
        $dirIndex = $this->resolveDirectoryIndex($legacyPublic, $path);
        if ($dirIndex !== null) {
            return $this->runLegacy($request, $dirIndex, $path);
        }

        // ── 5. Extension-less PHP  (e.g. /login → login.php) ────────────
        $extensionless = $this->resolveExtensionless($legacyPublic, $path);
        if ($extensionless !== null) {
            return $this->runLegacy($request, $extensionless, $path);
        }

        // ── 6. Slug-based routes ─────────────────────────────────────────
        $segments = array_values(array_filter(explode('/', trim($path, '/'))));

        // /categories/{cat_url}/{child?}
        if (($segments[0] ?? '') === 'categories' && isset($segments[1])) {
            $extra = ['cat_url' => urldecode($segments[1])];
            if (isset($segments[2])) {
                $extra['cat_child_url'] = urldecode($segments[2]);
            }
            return $this->runLegacy($request, $legacyPublic . '/categories/category.php', $path, $extra);
        }

        // /proposals/{username}/{slug...}  (not reserved sub-dirs)
        if (($segments[0] ?? '') === 'proposals' && count($segments) >= 3) {
            $reserved = ['proposal_files', 'ajax', 'sections', 'coupons'];
            if (!in_array($segments[1], $reserved, true)) {
                return $this->runLegacy($request, $legacyPublic . '/proposals/proposal.php', $path, [
                    'username'     => urldecode($segments[1]),
                    'proposal_url' => urldecode(implode('/', array_slice($segments, 2))),
                ]);
            }
        }

        // /blog/{id}
        if (($segments[0] ?? '') === 'blog' && isset($segments[1]) && ctype_digit($segments[1])) {
            return $this->runLegacy($request, $legacyPublic . '/blog/post.php', $path, [
                'id' => $segments[1],
            ]);
        }

        // /article/{slug...}
        if (($segments[0] ?? '') === 'article' && isset($segments[1])) {
            return $this->runLegacy($request, $legacyPublic . '/article/article.php', $path, [
                'article_url' => urldecode(implode('/', array_slice($segments, 1))),
            ]);
        }

        // /tags/{tag...}
        if (($segments[0] ?? '') === 'tags' && isset($segments[1])) {
            return $this->runLegacy($request, $legacyPublic . '/tags/tag.php', $path, [
                'tag' => urldecode(implode('/', array_slice($segments, 1))),
            ]);
        }

        // /pages/{slug...}
        if (($segments[0] ?? '') === 'pages' && isset($segments[1])) {
            return $this->runLegacy($request, $legacyPublic . '/pages/index.php', $path, [
                'slug' => urldecode(implode('/', array_slice($segments, 1))),
            ]);
        }

        // Single-segment slug → handler.php
        if (count($segments) === 1 && preg_match('/^[0-9a-zA-Z_-]+$/', $segments[0])) {
            return $this->runLegacy($request, $legacyPublic . '/handler.php', $path, [
                'slug' => $segments[0],
            ]);
        }

        // ── 7. Final fallback → legacy index.php ────────────────────────
        $indexPhp = $legacyPublic . DIRECTORY_SEPARATOR . 'index.php';
        if (is_file($indexPhp)) {
            return $this->runLegacy($request, $indexPhp, $path);
        }

        abort(404);
    }

    // ─── helpers ──────────────────────────────────────────────────────────

    private function legacyPublicPath(): string
    {
        return base_path('legacy' . DIRECTORY_SEPARATOR . 'public');
    }

    private function platformBasePath(): ?string
    {
        $p = base_path('legacy' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . 'Platform');
        return is_dir($p) ? realpath($p) ?: $p : null;
    }

    /**
     * Serve a static (non-PHP) asset directly without spawning a subprocess.
     */
    private function serveStaticAsset(string $path, string $publicRoot, ?string $platformBase): ?Response
    {
        if ($path === '/') {
            return null;
        }

        $requestPath = parse_url($path, PHP_URL_PATH) ?: $path;
        $ext = strtolower(pathinfo($requestPath, PATHINFO_EXTENSION));

        if ($ext === '' || $ext === 'php' || !isset(self::MIME_MAP[$ext])) {
            return null;
        }

        // Try legacy public first
        $candidate = $this->resolveStaticCandidate($publicRoot, $requestPath);
        if ($candidate !== null) {
            return $this->staticFileResponse($candidate, $ext);
        }

        // Try platform (app/Modules/Platform)
        if ($platformBase !== null) {
            $candidate = $this->resolveStaticCandidate($platformBase, $requestPath);
            if ($candidate !== null) {
                return $this->staticFileResponse($candidate, $ext);
            }
        }

        // Not-found for known static extensions
        return response('Not Found', 404);
    }

    private function resolveStaticCandidate(string $basePath, string $requestPath): ?string
    {
        $relative = ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $requestPath), DIRECTORY_SEPARATOR);
        $candidate = realpath($basePath . DIRECTORY_SEPARATOR . $relative);
        if ($candidate === false || !is_file($candidate)) {
            return null;
        }
        if (strncmp($candidate, $basePath, strlen($basePath)) !== 0) {
            return null; // path traversal guard
        }
        return $candidate;
    }

    private function staticFileResponse(string $filePath, string $ext): Response
    {
        $mime = self::MIME_MAP[$ext] ?? (@mime_content_type($filePath) ?: 'application/octet-stream');
        return response(file_get_contents($filePath) ?: '', 200, [
            'Content-Type'   => $mime,
            'Content-Length'  => (string) filesize($filePath),
            'Cache-Control'  => 'public, max-age=86400',
        ]);
    }

    /**
     * Serve /includes/* from the platform includes directory.
     */
    private function serveInclude(Request $request, string $path, ?string $platformBase): Response
    {
        if ($platformBase === null) {
            abort(404);
        }

        $legacyPath = ltrim($path, '/');
        if (!str_ends_with(strtolower($legacyPath), '.php')) {
            $legacyPath .= '.php';
        }

        $includeFile = realpath($platformBase . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $legacyPath));
        $includesBase = realpath($platformBase . DIRECTORY_SEPARATOR . 'includes');

        if (
            $includeFile === false
            || $includesBase === false
            || !is_file($includeFile)
            || strncmp($includeFile, $includesBase, strlen($includesBase)) !== 0
        ) {
            abort(404);
        }

        return $this->runLegacy($request, $includeFile, $path);
    }

    private function resolvePhpFile(string $publicRoot, string $path): ?string
    {
        $full = $publicRoot . str_replace('/', DIRECTORY_SEPARATOR, $path);
        if (is_file($full) && strtolower(pathinfo($full, PATHINFO_EXTENSION)) === 'php') {
            return $full;
        }
        return null;
    }

    private function resolveDirectoryIndex(string $publicRoot, string $path): ?string
    {
        $dir = $publicRoot . rtrim(str_replace('/', DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);
        if (is_dir($dir)) {
            $index = $dir . DIRECTORY_SEPARATOR . 'index.php';
            if (is_file($index)) {
                return $index;
            }
        }
        return null;
    }

    private function resolveExtensionless(string $publicRoot, string $path): ?string
    {
        $phpPath = $publicRoot . rtrim(str_replace('/', DIRECTORY_SEPARATOR, $path), '/') . '.php';
        if (is_file($phpPath)) {
            return $phpPath;
        }
        return null;
    }

    /**
     * Execute a legacy PHP script via LegacyScriptRunner (isolated subprocess)
     * and return its output as a Laravel Response.
     */
    private function runLegacy(Request $request, string $scriptPath, string $uri, array $extraGet = []): Response
    {
        if (!is_file($scriptPath)) {
            abort(404);
        }

        // Merge extra query parameters (slug routing)
        foreach ($extraGet as $key => $value) {
            if ($value !== null) {
                $request->query->set($key, (string) $value);
            }
        }
        // Sync request superglobals
        $request->merge($request->query->all());

        $result = LegacyScriptRunner::run($request, $scriptPath, $uri);

        if ($result === null) {
            abort(500, 'Legacy runner returned null');
        }

        $status  = (int) ($result['status'] ?? 200);
        $body    = (string) ($result['body'] ?? '');
        $headers = is_array($result['headers'] ?? null) ? $result['headers'] : [];

        // Build response
        $response = response($body, $status > 0 ? $status : 200);

        // Apply captured headers
        $hasContentType = false;
        foreach ($headers as $headerLine) {
            if (!is_string($headerLine) || $headerLine === '') {
                continue;
            }
            $pos = strpos($headerLine, ':');
            if ($pos === false) {
                continue;
            }
            $name  = trim(substr($headerLine, 0, $pos));
            $value = trim(substr($headerLine, $pos + 1));
            if ($name === '' || $value === '') {
                continue;
            }
            // Skip headers that Laravel manages
            if (strcasecmp($name, 'Content-Length') === 0 || strcasecmp($name, 'Transfer-Encoding') === 0) {
                continue;
            }
            if (strcasecmp($name, 'Content-Type') === 0) {
                $hasContentType = true;
            }
            $response->headers->set($name, $value, strcasecmp($name, 'Set-Cookie') !== 0);
        }

        if (!$hasContentType) {
            $ext = strtolower(pathinfo(parse_url($uri, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
            $mime = ($ext !== '' && isset(self::MIME_MAP[$ext])) ? self::MIME_MAP[$ext] : 'text/html; charset=UTF-8';
            $response->headers->set('Content-Type', $mime);
        }

        // Override X-Handler to indicate legacy
        $response->headers->set('X-Handler', 'legacy');

        return $response;
    }
}
