<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/**
 * Dispatches traditional form POSTs that legacy pages handle inline.
 *
 * Legacy behaviour: header.php includes register_login_forgot.php on every
 * page.  That file checks $_POST['register'], $_POST['login'],
 * $_POST['forgot'] and processes accordingly.  Because forms use
 * action="" they POST to whatever URL they are rendered on (/, /login,
 * /blog, etc.).
 *
 * This controller replicates that dispatch so Laravel accepts POSTs on
 * the same URLs without MethodNotAllowedHttpException.
 */
class LegacyPostController extends Controller
{
    /**
     * Accept POST on root (/) and /index – the most common targets for
     * the register/login/forgot modals rendered via header.blade.php.
     *
     * Also handles the home-page search form (action="" method="post").
     */
    public function dispatchRootPost(Request $request): RedirectResponse|Response
    {
        // ── Register ────────────────────────────────────────────────
        if ($request->has('register')) {
            return $this->handleRegister($request);
        }

        // ── Login (modal) ───────────────────────────────────────────
        if ($request->has('login')) {
            return $this->handleLogin($request);
        }

        // ── Forgot password ─────────────────────────────────────────
        if ($request->has('forgot')) {
            return $this->handleForgot($request);
        }

        // ── Home-page search form ───────────────────────────────────
        if ($request->has('search_query')) {
            session(['search_query' => $request->input('search_query', '')]);
            return redirect('/search');
        }

        // ── Blog comment form (name="submit" + comment field) ────────
        if ($request->has('submit') && $request->has('comment')) {
            return $this->handleBlogComment($request);
        }

        // Fallback – nothing matched, just redirect back.
        return redirect()->back();
    }

    /**
     * Accept POST on /login – the standalone login page form uses
     * name="access", while the login modal uses name="login".
     */
    public function dispatchLoginPost(Request $request): RedirectResponse
    {
        // Standalone login page uses submit button name="access"
        if ($request->has('access') || $request->has('login')) {
            return $this->handleLogin($request);
        }

        // Modals can also appear on /login page
        if ($request->has('register')) {
            return $this->handleRegister($request);
        }

        if ($request->has('forgot')) {
            return $this->handleForgot($request);
        }

        return redirect()->back();
    }

    // ─────────────────────────────────────────────────────────────────
    //  Private handlers matching legacy register_login_forgot.php
    // ─────────────────────────────────────────────────────────────────

    /**
     * Handle login POST – matches legacy logic in register_login_forgot.php
     * line 179: if(isset($_POST['login'])){…}
     * and login.php line ~80: if(isset($_POST['access'])){…}
     *
     * Legacy fields: seller_user_name, seller_pass
     * Legacy session on success: seller_user_name, seller_id, seller_email, etc.
     * Legacy redirect on success: JS swal then redirect to index
     * Legacy redirect on failure: Flash errors, redirect to same page
     */
    private function handleLogin(Request $request): RedirectResponse
    {
        $username = strip_tags((string) $request->input('seller_user_name', ''));
        $password = (string) $request->input('seller_pass', '');

        if (empty($username) || empty($password)) {
            session()->flash('login_errors', ['Username and password are required.']);
            return redirect()->back();
        }

        $seller = DB::table('sellers')
            ->where('seller_user_name', $username)
            ->first();

        if (!$seller) {
            // Legacy also checks by email
            $seller = DB::table('sellers')
                ->where('seller_email', $username)
                ->first();
        }

        if (!$seller || !password_verify($password, $seller->seller_pass)) {
            session()->flash('login_errors', ['Password or username is incorrect. Please try again.']);
            return redirect()->back();
        }

        // Check seller status (legacy checks)
        if ($seller->seller_status === 'deactivated') {
            session()->flash('login_errors', ['Your account has been deactivated.']);
            return redirect()->back();
        }

        if ($seller->seller_status === 'block-ban') {
            session()->flash('login_errors', ['Your account has been blocked.']);
            return redirect()->back();
        }

        // Set session keys matching legacy exactly
        $request->session()->regenerate();
        session([
            'seller_user_name' => $seller->seller_user_name,
            'seller_id'        => $seller->seller_id,
            'seller_email'     => $seller->seller_email,
        ]);

        return redirect('/');
    }

    /**
     * Handle register POST – matches legacy logic in register_login_forgot.php
     * line 14: if(isset($_POST['register'])){…}
     *
     * Legacy fields: name, u_name, email, pass, con_pass, phone,
     *                country_code, referral, timezone
     * Legacy redirect on error: Flash::add("register_errors", …)
     *                           then JS redirect to 'index'
     * Legacy redirect on success: redirect to index with login session set
     */
    private function handleRegister(Request $request): RedirectResponse
    {
        $name       = strip_tags((string) $request->input('name', ''));
        $uName      = strip_tags((string) $request->input('u_name', ''));
        $email      = strip_tags((string) $request->input('email', ''));
        $pass       = strip_tags((string) $request->input('pass', ''));
        $conPass    = strip_tags((string) $request->input('con_pass', ''));
        $phone      = strip_tags((string) $request->input('phone', ''));
        $countryCode = strip_tags((string) $request->input('country_code', ''));
        $referral   = strip_tags((string) $request->input('referral', ''));

        $errors = [];

        if (empty($name))    $errors[] = 'Full Name Is Required.';
        if (empty($uName))   $errors[] = 'User Name Is Required.';
        if (empty($email))   $errors[] = 'Email Is Required.';
        if (empty($pass))    $errors[] = 'Password Is Required.';
        if (empty($conPass)) $errors[] = 'Confirm Password Is Required.';

        if (!empty($errors)) {
            session()->flash('register_errors', $errors);
            session()->flash('form_data', $request->only(['name', 'u_name', 'email', 'phone', 'country_code']));
            return redirect('/');
        }

        // Check for arabic chars in username (legacy rule)
        if (preg_match('/[\x{0600}-\x{06FF}]/u', $uName)) {
            $errors[] = 'Foreign characters are not allowed in username, Please try another one.';
        }

        // Check username taken
        $exists = DB::table('sellers')->where('seller_user_name', $uName)->count();
        if ($exists > 0) {
            $errors[] = 'Sorry This user name has already been taken.';
        }

        // Check email taken
        $emailExists = DB::table('sellers')->where('seller_email', $email)->count();
        if ($emailExists > 0) {
            $errors[] = 'Sorry this email address has already been taken.';
        }

        // Password mismatch
        if ($pass !== $conPass) {
            $errors[] = 'Passwords do not match.';
        }

        // Password min length
        if (strlen($pass) < 6) {
            $errors[] = 'Password must contain at least 6 characters.';
        }

        if (!empty($errors)) {
            session()->flash('register_errors', $errors);
            session()->flash('form_data', $request->only(['name', 'u_name', 'email', 'phone', 'country_code']));
            return redirect('/');
        }

        // Determine country from IP (simplified)
        $country = '';
        try {
            $ctx = stream_context_create(['http' => ['timeout' => 3]]);
            $geo = @file_get_contents('https://www.geoplugin.net/json.gp?ip=' . rawurlencode((string) $request->ip()), false, $ctx);
            if (is_string($geo) && $geo !== '') {
                $data = json_decode($geo, true);
                if (is_array($data) && isset($data['geoplugin_countryName'])) {
                    $country = trim($data['geoplugin_countryName']);
                }
            }
        } catch (\Throwable) {
            // Silently ignore geo lookup failures
        }

        $fullPhone = trim($countryCode . ' ' . $phone);
        $date = date('F d, Y');

        DB::table('sellers')->insert([
            'seller_user_name'   => $uName,
            'seller_name'        => $name,
            'seller_email'       => $email,
            'seller_pass'        => password_hash($pass, PASSWORD_DEFAULT),
            'seller_phone'       => $fullPhone,
            'seller_country'     => $country,
            'seller_status'      => 'active',
            'seller_member_since' => $date,
            'seller_rating'      => 0,
            'seller_level'       => 0,
            'seller_balance'     => 0,
        ]);

        $newSeller = DB::table('sellers')->where('seller_user_name', $uName)->first();

        // Auto-login after registration (matching legacy)
        $request->session()->regenerate();
        session([
            'seller_user_name' => $newSeller->seller_user_name,
            'seller_id'        => $newSeller->seller_id,
            'seller_email'     => $newSeller->seller_email,
        ]);

        return redirect('/');
    }

    /**
     * Handle forgot password POST – matches legacy logic in
     * register_login_forgot.php line 280: if(isset($_POST['forgot'])){…}
     *
     * Legacy field: forgot_email
     */
    private function handleForgot(Request $request): RedirectResponse
    {
        $email = strip_tags((string) $request->input('forgot_email', ''));

        if (empty($email)) {
            session()->flash('forgot_errors', ['Email address is required.']);
            return redirect()->back();
        }

        $seller = DB::table('sellers')->where('seller_email', $email)->first();

        if (!$seller) {
            session()->flash('forgot_errors', ['This email address is not registered.']);
            return redirect()->back();
        }

        // TODO: Implement password reset email sending to match legacy
        // For now, flash a success message
        session()->flash('forgot_success', 'If that email is registered, a reset link has been sent.');
        return redirect()->back();
    }

    /**
     * Handle blog comment POST – matches legacy logic in
     * blog/includes/post_comments.php line 69: if(isset($_POST['submit'])){…}
     *
     * Legacy fields: comment
     * Legacy inserts into post_comments table with seller_id, post_id, comment, date
     * Then redirects to the same blog post page.
     */
    private function handleBlogComment(Request $request): RedirectResponse
    {
        $sellerId = session('seller_id');

        if (!$sellerId) {
            return redirect()->back();
        }

        $comment = strip_tags((string) $request->input('comment', ''));

        if (empty($comment)) {
            return redirect()->back();
        }

        // Extract blog post ID from the URL path: /blog/{id}/{slug?}
        $path = $request->path();
        $segments = explode('/', trim($path, '/'));
        $postId = isset($segments[1]) ? (int) $segments[1] : 0;

        if ($postId <= 0) {
            return redirect()->back();
        }

        DB::table('post_comments')->insert([
            'post_id'   => $postId,
            'seller_id' => $sellerId,
            'comment'   => $comment,
            'date'      => date('F m, Y'),
        ]);

        return redirect()->back();
    }
}
