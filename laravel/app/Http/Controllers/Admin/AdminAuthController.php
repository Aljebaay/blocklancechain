<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * AdminAuthController - handles admin authentication.
 * Replaces: app/Modules/Platform/admin/login.php, logout.php
 *
 * Legacy admin/login.php is a self-submitting page:
 *   GET  /admin/login  → render login form
 *   POST /admin/login  → process $_POST['admin_login'], render same page
 *                         with swal success or error
 */
class AdminAuthController extends Controller
{
    /**
     * Show admin login page.
     * Legacy: GET /admin/login → render admin/login.php form
     */
    public function showLogin()
    {
        if (session()->has('admin_email')) {
            return redirect('/admin');
        }

        return view('legacy.admin-login');
    }

    /**
     * Handle admin login.
     * Legacy: POST /admin/login with admin_email, admin_pass, remember, admin_login
     *
     * Legacy renders the same HTML page with a JS swal alert on success/failure.
     * For parity we use session flash + redirect (the form submits to "" = same URL,
     * so the redirect re-renders the page where flash messages appear).
     */
    public function login(Request $request): RedirectResponse
    {
        $adminEmail = strip_tags((string) $request->input('admin_email', ''));
        $adminPass  = (string) $request->input('admin_pass', '');

        if (empty($adminEmail) || empty($adminPass)) {
            session()->flash('admin_login_error', 'Email and password are required.');
            return redirect('/admin/login');
        }

        $admin = DB::table('admins')
            ->where('admin_email', $adminEmail)
            ->orWhere('admin_user_name', $adminEmail)
            ->first();

        if (!$admin || !password_verify($adminPass, $admin->admin_pass)) {
            session()->flash('admin_login_error', 'Opps! password or username is incorrect. Please try again.');
            return redirect('/admin/login');
        }

        // Handle "Remember Me" exactly like legacy
        if (!empty($request->input('remember'))) {
            session(['r_email' => $adminEmail, 'r_passoword' => $adminPass]);
        } else {
            session()->forget(['r_email', 'r_passoword']);
        }

        session()->regenerate();

        session([
            'admin_email'    => $adminEmail,
            'admin_id'       => $admin->admin_id ?? null,
            'admin_name'     => $admin->admin_name ?? 'Admin',
            'loggedin_time'  => time(),
            'adminLanguage'  => 1,
        ]);

        return redirect('/admin/login')->with('admin_login_success', true);
    }

    /**
     * Handle admin logout.
     */
    public function logout(): RedirectResponse
    {
        session()->forget([
            'admin_email', 'admin_id', 'admin_name',
            'loggedin_time', 'adminLanguage',
        ]);
        session()->regenerate();

        return redirect('/admin/login');
    }
}
