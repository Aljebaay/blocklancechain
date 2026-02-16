<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Seller;
use Illuminate\Support\Facades\Hash;

/**
 * AuthService - handles authentication logic.
 * Replaces legacy login.php authentication logic.
 */
class AuthService
{
    /**
     * Attempt to authenticate a seller by username/email and password.
     * Returns an array with 'success' boolean and contextual data.
     * Matches legacy login behavior exactly.
     */
    public function attemptLogin(string $usernameOrEmail, string $password): array
    {
        // Legacy query: case-sensitive username match OR email match
        $seller = Seller::whereRaw('BINARY seller_user_name LIKE ?', [$usernameOrEmail])
            ->orWhere('seller_email', $usernameOrEmail)
            ->first();

        if (!$seller) {
            return [
                'success' => false,
                'error' => 'incorrect_login',
            ];
        }

        // Verify password using password_verify (bcrypt)
        if (!password_verify($password, $seller->seller_pass)) {
            return [
                'success' => false,
                'error' => 'incorrect_login',
            ];
        }

        // Check seller status
        if ($seller->seller_status === 'block-ban') {
            return [
                'success' => false,
                'error' => 'blocked',
            ];
        }

        if ($seller->seller_status === 'deactivated') {
            return [
                'success' => false,
                'error' => 'deactivated',
            ];
        }

        // Verify seller record exists with matching credentials
        $verifiedSeller = Seller::where(function ($query) use ($usernameOrEmail) {
            $query->where('seller_email', $usernameOrEmail)
                ->orWhere('seller_user_name', $usernameOrEmail);
        })
            ->where('seller_pass', $seller->seller_pass)
            ->first();

        if (!$verifiedSeller) {
            return [
                'success' => false,
                'error' => 'incorrect_login',
            ];
        }

        // Update seller status and IP
        $verifiedSeller->update([
            'seller_status' => 'online',
            'seller_ip' => request()->ip(),
        ]);

        // Set session
        session([
            'seller_user_name' => $verifiedSeller->seller_user_name,
        ]);

        // Regenerate session ID for security
        session()->regenerate();

        return [
            'success' => true,
            'seller' => $verifiedSeller,
            'display_name' => ucfirst($verifiedSeller->seller_user_name),
        ];
    }

    /**
     * Log out the current seller.
     */
    public function logout(): void
    {
        $username = session('seller_user_name');

        if ($username) {
            Seller::where('seller_user_name', $username)
                ->update(['seller_status' => 'offline']);
        }

        session()->forget(['seller_user_name', 'sessionStart']);
        session()->invalidate();
        session()->regenerateToken();
    }

    /**
     * Get the currently authenticated seller, or null.
     */
    public function currentSeller(): ?Seller
    {
        $username = session('seller_user_name');

        if (!$username) {
            return null;
        }

        return Seller::where('seller_user_name', $username)->first();
    }

    /**
     * Check if a seller is currently logged in.
     */
    public function isLoggedIn(): bool
    {
        return session()->has('seller_user_name');
    }

    /**
     * Register a new seller.
     */
    public function register(array $data): array
    {
        // Check if username already exists
        $existingUsername = Seller::where('seller_user_name', $data['seller_user_name'])->first();
        if ($existingUsername) {
            return [
                'success' => false,
                'error' => 'username_taken',
            ];
        }

        // Check if email already exists
        $existingEmail = Seller::where('seller_email', $data['seller_email'])->first();
        if ($existingEmail) {
            return [
                'success' => false,
                'error' => 'email_taken',
            ];
        }

        $seller = Seller::create([
            'seller_user_name' => $data['seller_user_name'],
            'seller_name' => $data['seller_name'] ?? $data['seller_user_name'],
            'seller_email' => $data['seller_email'],
            'seller_pass' => password_hash($data['seller_pass'], PASSWORD_BCRYPT),
            'seller_status' => 'offline',
            'seller_register_date' => date('Y-m-d'),
            'seller_country' => $data['seller_country'] ?? '',
            'seller_ip' => request()->ip(),
        ]);

        return [
            'success' => true,
            'seller' => $seller,
        ];
    }
}
