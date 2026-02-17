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
        // #region agent log
        $hasAdmin = session()->has('admin_email');
        file_put_contents(base_path('../.cursor/debug.log'), json_encode(['id'=>'log_'.uniqid(), 'timestamp'=>time()*1000, 'location'=>'AdminAuthController.php:showLogin', 'message'=>'Show login page', 'data'=>['has_admin_email'=>$hasAdmin, 'redirect_to'=>$hasAdmin ? '/admin' : null], 'hypothesisId'=>'D,E'])."\n", FILE_APPEND);
        // #endregion

        if ($hasAdmin) {
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

        // #region agent log
        $logPath = base_path('../.cursor/debug.log');
        file_put_contents($logPath, json_encode(['id'=>'log_'.uniqid(), 'timestamp'=>time()*1000, 'location'=>'AdminAuthController.php:login:entry', 'message'=>'Admin login attempt', 'data'=>['email_empty'=>empty($adminEmail), 'pass_empty'=>empty($adminPass)], 'hypothesisId'=>'A'])."\n", FILE_APPEND);
        // #endregion

        if (empty($adminEmail) || empty($adminPass)) {
            session()->flash('admin_login_error', 'Email and password are required.');
            return redirect('/admin/login');
        }

        $admin = DB::table('admins')
            ->where(function ($q) use ($adminEmail) {
                $q->where('admin_email', $adminEmail)
                  ->orWhere('admin_user_name', $adminEmail);
            })
            ->first();

        // #region agent log
        file_put_contents($logPath, json_encode(['id'=>'log_'.uniqid(), 'timestamp'=>time()*1000, 'location'=>'AdminAuthController.php:login:after_query', 'message'=>'Admin lookup result', 'data'=>['admin_found'=>($admin !== null), 'admin_id'=>$admin !== null ? $admin->admin_id : null], 'hypothesisId'=>'A'])."\n", FILE_APPEND);
        // #endregion

        $passwordOk = $admin && $this->verifyAdminPassword($adminPass, $admin);
        if (!$admin || !$passwordOk) {
            // #region agent log
            file_put_contents($logPath, json_encode(['id'=>'log_'.uniqid(), 'timestamp'=>time()*1000, 'location'=>'AdminAuthController.php:login:reject', 'message'=>'Login rejected', 'data'=>['no_admin'=>($admin === null), 'password_ok'=>$passwordOk], 'hypothesisId'=>'B'])."\n", FILE_APPEND);
            // #endregion
            session()->flash('admin_login_error', 'Opps! password or username is incorrect. Please try again.');
            return redirect('/admin/login');
        }

        // #region agent log
        file_put_contents($logPath, json_encode(['id'=>'log_'.uniqid(), 'timestamp'=>time()*1000, 'location'=>'AdminAuthController.php:login:password_ok', 'message'=>'Password verified', 'data'=>['admin_id'=>$admin->admin_id], 'hypothesisId'=>'B'])."\n", FILE_APPEND);
        // #endregion

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

        // #region agent log
        file_put_contents(base_path('../.cursor/debug.log'), json_encode(['id'=>'log_'.uniqid(), 'timestamp'=>time()*1000, 'location'=>'AdminAuthController.php:login:success', 'message'=>'Redirecting with success', 'data'=>['redirect_to'=>'/admin/login', 'session_has_admin_email'=>session()->has('admin_email')], 'hypothesisId'=>'C,D'])."\n", FILE_APPEND);
        // #endregion

        // Sync to native PHP session so the legacy admin panel can read $_SESSION
        $this->syncNativeSession([
            'admin_email'   => $adminEmail,
            'admin_id'      => $admin->admin_id ?? null,
            'admin_name'    => $admin->admin_name ?? 'Admin',
            'loggedin_time' => time(),
            'adminLanguage' => 1,
        ]);

        return redirect('/admin/login')->with('admin_login_success', true);
    }

    /**
     * Verify admin password against stored hash.
     *
     * The admin_pass column may contain:
     *   - A bcrypt hash (from password_hash / change_password.php)
     *   - An MD5 hash (from manual DB setup or older code)
     *   - A plaintext password (from manual DB insert)
     *
     * If a non-bcrypt match is found, the hash is upgraded to bcrypt.
     */
    private function verifyAdminPassword(string $password, object $admin): bool
    {
        $storedHash = $admin->admin_pass ?? '';

        if ($storedHash === '') {
            return false;
        }

        // 1. Try bcrypt (password_hash format: $2y$... or $2a$...)
        if (str_starts_with($storedHash, '$2y$') || str_starts_with($storedHash, '$2a$')) {
            return password_verify($password, $storedHash);
        }

        // 2. Try MD5 comparison
        if (strlen($storedHash) === 32 && ctype_xdigit($storedHash)) {
            if (md5($password) === $storedHash) {
                $this->upgradePasswordHash($admin->admin_id, $password);
                return true;
            }
            return false;
        }

        // 3. Try plaintext comparison
        if ($password === $storedHash) {
            $this->upgradePasswordHash($admin->admin_id, $password);
            return true;
        }

        return false;
    }

    /**
     * Upgrade a non-bcrypt password to bcrypt for future logins.
     */
    private function upgradePasswordHash(int $adminId, string $plainPassword): void
    {
        DB::table('admins')
            ->where('admin_id', $adminId)
            ->update(['admin_pass' => password_hash($plainPassword, PASSWORD_DEFAULT)]);
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

        // Destroy native PHP session too
        $this->destroyNativeSession();

        return redirect('/admin/login');
    }

    /**
     * Start the native PHP session (same store the legacy admin reads)
     * and write key/value pairs into $_SESSION.
     */
    private function syncNativeSession(array $data): void
    {
        $bootstrap = base_path('../app/Modules/Platform/includes/session_bootstrap.php');
        if (is_file($bootstrap)) {
            require_once $bootstrap;
            if (function_exists('blc_bootstrap_session')) {
                blc_bootstrap_session();
            }
        } elseif (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        foreach ($data as $key => $value) {
            $_SESSION[$key] = $value;
        }

        session_write_close();
    }

    /**
     * Destroy the native PHP session on logout.
     */
    private function destroyNativeSession(): void
    {
        $bootstrap = base_path('../app/Modules/Platform/includes/session_bootstrap.php');
        if (is_file($bootstrap)) {
            require_once $bootstrap;
            if (function_exists('blc_bootstrap_session')) {
                blc_bootstrap_session();
            }
        } elseif (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }
}
