<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use App\Services\SiteSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * AuthController - handles login, logout, and registration.
 * Replaces legacy login.php, logout.php, and registration logic.
 */
class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly SiteSettingsService $settingsService,
    ) {}

    /**
     * Show the login page.
     * Replaces: app/Modules/Platform/login.php (GET)
     */
    public function showLogin(): View|RedirectResponse
    {
        if ($this->authService->isLoggedIn()) {
            return redirect('/');
        }

        return view('auth.login', [
            'settings' => $this->settingsService->getGeneralSettings(),
            'siteName' => $this->settingsService->getSiteName(),
        ]);
    }

    /**
     * Handle login form submission.
     * Replaces: app/Modules/Platform/login.php (POST)
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->attemptLogin(
            $request->validated('seller_user_name'),
            $request->validated('seller_pass'),
        );

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'error' => $result['error'],
            ], 401);
        }

        return response()->json([
            'success' => true,
            'redirect' => $this->settingsService->getSiteUrl(),
            'display_name' => $result['display_name'],
        ]);
    }

    /**
     * Handle logout.
     * Replaces: app/Modules/Platform/logout.php
     */
    public function logout(): RedirectResponse
    {
        $this->authService->logout();

        return redirect('/');
    }

    /**
     * Show the registration page.
     */
    public function showRegister(): View|RedirectResponse
    {
        if ($this->authService->isLoggedIn()) {
            return redirect('/');
        }

        return view('auth.register', [
            'settings' => $this->settingsService->getGeneralSettings(),
            'siteName' => $this->settingsService->getSiteName(),
        ]);
    }

    /**
     * Handle registration form submission.
     * Replaces: legacy registration modal logic.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'error' => $result['error'],
            ], 422);
        }

        // Auto-login after registration
        $this->authService->attemptLogin(
            $request->validated('seller_user_name'),
            $request->validated('seller_pass'),
        );

        return response()->json([
            'success' => true,
            'redirect' => $this->settingsService->getSiteUrl(),
        ]);
    }
}
