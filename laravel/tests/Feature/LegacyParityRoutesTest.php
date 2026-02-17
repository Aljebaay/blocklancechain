<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Regression tests ensuring Laravel accepts the same URLs + methods as legacy.
 *
 * Legacy forms use action="" which POSTs to the current page URL.
 * The register_login_forgot.php include file processes $_POST on every page.
 * These tests verify that POST /, POST /login, GET/POST /admin/login all work.
 */
class LegacyParityRoutesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedLegacyTables();
    }

    protected function tearDown(): void
    {
        // Drop tables in reverse dependency order
        Schema::dropIfExists('inbox_sellers');
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('order_notifications');
        Schema::dropIfExists('countries');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('proposals');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('language_data');
        Schema::dropIfExists('languages');
        Schema::dropIfExists('child_categories');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('admins');
        Schema::dropIfExists('sellers');
        Schema::dropIfExists('general_settings');
        parent::tearDown();
    }

    /**
     * Create the minimal legacy tables needed for routing tests.
     */
    private function seedLegacyTables(): void
    {
        if (! Schema::hasTable('general_settings')) {
            Schema::create('general_settings', function ($table) {
                $table->increments('id');
                $table->text('site_name')->default('TestSite');
                $table->text('site_url')->default('http://localhost');
                $table->text('site_email_address')->default('admin@test.com');
                $table->text('site_title')->default('TestSite');
                $table->text('site_desc')->default('');
                $table->text('site_keywords')->default('');
                $table->text('site_color')->default('#28a745');
                $table->string('site_hover_color', 255)->default('');
                $table->string('site_border_color', 255)->default('');
                $table->text('signup_email')->default('no');
                $table->integer('referral_money')->default(0);
                $table->text('site_copyright')->default('');
                $table->text('site_favicon')->default('');
                $table->text('site_logo_type')->default('text');
                $table->text('site_logo_text')->default('TestSite');
                $table->text('site_logo_image')->default('');
                $table->text('site_logo')->default('');
                $table->text('enable_social_login')->default('no');
                $table->text('fb_app_id')->default('');
                $table->text('fb_app_secret')->default('');
                $table->text('g_client_id')->default('');
                $table->text('g_client_secret')->default('');
                $table->text('knowledge_bank')->default('disabled');
                $table->text('site_currency')->default('$');
                $table->string('currency_position', 255)->default('left');
                $table->text('enable_referrals')->default('no');
                $table->text('approve_proposals')->default('no');
                $table->integer('make_phone_number_required')->default(0);
                $table->integer('enable_mobile_logo')->default(0);
                $table->string('site_mobile_logo', 255)->default('');
                $table->integer('language_switcher')->default(0);
                $table->text('google_analytics')->default('');
                $table->string('site_timezone', 255)->default('UTC');
            });

            DB::table('general_settings')->insert([
                'site_name' => 'TestSite',
                'site_url' => 'http://localhost',
                'site_email_address' => 'admin@test.com',
                'site_title' => 'TestSite',
            ]);
        }

        if (! Schema::hasTable('sellers')) {
            Schema::create('sellers', function ($table) {
                $table->increments('seller_id');
                $table->string('seller_name', 255)->default('');
                $table->string('seller_user_name', 255)->default('');
                $table->text('seller_email')->default('');
                $table->text('seller_pass')->default('');
                $table->string('seller_phone', 255)->default('');
                $table->text('seller_country')->default('');
                $table->text('seller_status')->default('active');
                $table->text('seller_member_since')->default('');
                $table->integer('seller_rating')->default(0);
                $table->integer('seller_level')->default(0);
                $table->integer('seller_balance')->default(0);
                $table->text('seller_image')->default('');
                $table->text('seller_cover_image')->default('');
                $table->text('seller_headline')->default('');
                $table->text('seller_about')->default('');
                $table->string('seller_activity', 255)->default('');
                $table->string('seller_timezone', 255)->default('');
                $table->text('seller_wallet')->default('');
                $table->integer('seller_payouts')->default(0);
                $table->text('seller_paypal_email')->default('');
                $table->text('seller_payoneer_email')->default('');
                $table->integer('seller_referral')->default(0);
                $table->string('seller_city', 255)->default('');
                $table->text('seller_verification')->default('');
                $table->text('seller_vacation')->default('no');
                $table->text('seller_vacation_reason')->default('');
                $table->text('seller_vacation_message')->default('');
                $table->text('seller_register_date')->default('');
                $table->string('enable_sound', 255)->default('yes');
                $table->integer('enable_notifications')->default(1);
                $table->integer('seller_offers')->default(0);
                $table->string('seller_ip', 255)->default('');
                $table->integer('seller_language')->default(0);
                $table->text('seller_recent_delivery')->default('');
                $table->bigInteger('seller_m_account_number')->default(0);
                $table->text('seller_m_account_name')->default('');
                $table->integer('seller_image_s3')->default(0);
                $table->integer('seller_cover_image_s3')->default(0);
            });
        }

        if (! Schema::hasTable('admins')) {
            Schema::create('admins', function ($table) {
                $table->increments('admin_id');
                $table->text('admin_name')->default('');
                $table->text('admin_user_name')->default('');
                $table->text('admin_email')->default('');
                $table->text('admin_pass')->default('');
                $table->text('admin_image')->default('');
                $table->text('admin_contact')->default('');
                $table->text('admin_country')->default('');
                $table->text('admin_job')->default('');
                $table->text('admin_about')->default('');
                $table->integer('isS3')->default(0);
            });
        }

        $this->seedSupportTables();
    }

    /**
     * Seed additional tables the views rely on (categories, footer, etc.).
     */
    private function seedSupportTables(): void
    {
        if (! Schema::hasTable('categories')) {
            Schema::create('categories', function ($table) {
                $table->increments('cat_id');
                $table->text('cat_name')->default('');
                $table->text('cat_url')->default('');
                $table->text('cat_icon')->default('');
                $table->text('cat_image')->default('');
                $table->integer('cat_image_s3')->default(0);
                $table->integer('cat_order')->default(0);
                $table->text('cat_status')->default('active');
            });
        }

        if (! Schema::hasTable('child_categories')) {
            Schema::create('child_categories', function ($table) {
                $table->increments('id');
                $table->integer('parent_id')->default(0);
                $table->text('child_name')->default('');
                $table->text('child_url')->default('');
                $table->integer('child_order')->default(0);
            });
        }

        if (! Schema::hasTable('languages')) {
            Schema::create('languages', function ($table) {
                $table->increments('language_id');
                $table->text('language_name')->default('');
                $table->text('language_icon')->default('');
                $table->integer('language_default')->default(0);
                $table->text('language_status')->default('active');
            });
            DB::table('languages')->insert([
                'language_name' => 'English',
                'language_default' => 1,
                'language_status' => 'active',
            ]);
        }

        if (! Schema::hasTable('language_data')) {
            Schema::create('language_data', function ($table) {
                $table->increments('id');
                $table->integer('language_id')->default(0);
                $table->text('language_key')->default('');
                $table->text('language_value')->default('');
            });
        }

        if (! Schema::hasTable('pages')) {
            Schema::create('pages', function ($table) {
                $table->increments('page_id');
                $table->text('page_title')->default('');
                $table->text('page_url')->default('');
                $table->text('page_content')->default('');
                $table->text('page_footer_column')->default('');
            });
        }

        if (! Schema::hasTable('proposals')) {
            Schema::create('proposals', function ($table) {
                $table->increments('proposal_id');
                $table->integer('seller_id')->default(0);
                $table->text('proposal_url')->default('');
                $table->text('proposal_title')->default('');
                $table->text('proposal_status')->default('active');
                $table->integer('cat_id')->default(0);
                $table->text('proposal_image')->default('');
                $table->integer('proposal_image_s3')->default(0);
                $table->text('basic_price')->default('0');
                $table->text('basic_title')->default('');
                $table->integer('proposal_featured')->default(0);
                $table->integer('proposal_top_rated')->default(0);
            });
        }

        if (! Schema::hasTable('reviews')) {
            Schema::create('reviews', function ($table) {
                $table->increments('review_id');
                $table->integer('proposal_id')->default(0);
                $table->integer('rating')->default(0);
            });
        }

        if (! Schema::hasTable('countries')) {
            Schema::create('countries', function ($table) {
                $table->increments('id');
                $table->string('country_name', 255)->default('');
                $table->string('country_code', 10)->default('');
            });
        }

        if (! Schema::hasTable('order_notifications')) {
            Schema::create('order_notifications', function ($table) {
                $table->increments('id');
                $table->integer('seller_id')->default(0);
                $table->integer('notifier_seller_id')->default(0);
                $table->text('notification_type')->default('');
                $table->text('notification_message')->default('');
                $table->text('notification_url')->default('');
                $table->text('notification_date')->default('');
                $table->integer('notification_read')->default(0);
            });
        }

        if (! Schema::hasTable('conversations')) {
            Schema::create('conversations', function ($table) {
                $table->increments('message_group_id');
                $table->integer('seller_a_id')->default(0);
                $table->integer('seller_b_id')->default(0);
            });
        }

        if (! Schema::hasTable('inbox_sellers')) {
            Schema::create('inbox_sellers', function ($table) {
                $table->increments('inbox_seller_id');
                $table->integer('message_group_id')->default(0);
                $table->integer('seller_id')->default(0);
                $table->text('inbox_status')->default('');
            });
        }
    }

    // ─────────────────────────────────────────────────────────────────
    //  Bug A: POST / must not throw MethodNotAllowed
    // ─────────────────────────────────────────────────────────────────

    #[Test]
    public function post_root_with_login_payload_does_not_throw_method_not_allowed(): void
    {
        DB::table('sellers')->insert([
            'seller_user_name' => 'testuser',
            'seller_name' => 'Test User',
            'seller_email' => 'test@test.com',
            'seller_pass' => password_hash('secret123', PASSWORD_DEFAULT),
            'seller_status' => 'active',
        ]);

        $response = $this->post('/', [
            '_token' => csrf_token(),
            'seller_user_name' => 'testuser',
            'seller_pass' => 'secret123',
            'login' => 'Login Now',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/');
    }

    #[Test]
    public function post_root_with_register_payload_does_not_throw_method_not_allowed(): void
    {
        $response = $this->post('/', [
            '_token' => csrf_token(),
            'name' => 'New User',
            'u_name' => 'newuser',
            'email' => 'new@test.com',
            'pass' => 'secret123',
            'con_pass' => 'secret123',
            'phone' => '1234567',
            'country_code' => '+1',
            'register' => 'Register',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/');
    }

    #[Test]
    public function post_root_with_forgot_payload_does_not_throw_method_not_allowed(): void
    {
        $response = $this->post('/', [
            '_token' => csrf_token(),
            'forgot_email' => 'nobody@test.com',
            'forgot' => 'submit',
        ]);

        $response->assertStatus(302);
    }

    #[Test]
    public function post_root_login_with_valid_credentials_sets_session(): void
    {
        DB::table('sellers')->insert([
            'seller_user_name' => 'testuser',
            'seller_name' => 'Test User',
            'seller_email' => 'test@test.com',
            'seller_pass' => password_hash('secret123', PASSWORD_DEFAULT),
            'seller_status' => 'active',
        ]);

        $response = $this->post('/', [
            '_token' => csrf_token(),
            'seller_user_name' => 'testuser',
            'seller_pass' => 'secret123',
            'login' => 'Login Now',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('seller_user_name', 'testuser');
    }

    #[Test]
    public function post_root_login_with_invalid_credentials_flashes_error(): void
    {
        $response = $this->post('/', [
            '_token' => csrf_token(),
            'seller_user_name' => 'nonexistent',
            'seller_pass' => 'wrongpass',
            'login' => 'Login Now',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('login_warning');
    }

    #[Test]
    public function post_root_register_with_valid_data_creates_seller(): void
    {
        $response = $this->post('/', [
            '_token' => csrf_token(),
            'name' => 'Brand New User',
            'u_name' => 'brandnew',
            'email' => 'brand@new.com',
            'pass' => 'secret123',
            'con_pass' => 'secret123',
            'phone' => '5551234',
            'country_code' => '+1',
            'register' => 'Register',
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('sellers', ['seller_user_name' => 'brandnew']);
        $response->assertSessionHas('seller_user_name', 'brandnew');
    }

    #[Test]
    public function post_root_register_with_duplicate_username_flashes_error(): void
    {
        DB::table('sellers')->insert([
            'seller_user_name' => 'taken',
            'seller_name' => 'Existing',
            'seller_email' => 'existing@test.com',
            'seller_pass' => password_hash('secret', PASSWORD_DEFAULT),
            'seller_status' => 'active',
        ]);

        $response = $this->post('/', [
            '_token' => csrf_token(),
            'name' => 'Another',
            'u_name' => 'taken',
            'email' => 'another@test.com',
            'pass' => 'secret123',
            'con_pass' => 'secret123',
            'register' => 'Register',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('register_errors');
    }

    // ─────────────────────────────────────────────────────────────────
    //  POST /login (standalone login page)
    // ─────────────────────────────────────────────────────────────────

    #[Test]
    public function post_login_with_access_button_works(): void
    {
        DB::table('sellers')->insert([
            'seller_user_name' => 'loginuser',
            'seller_name' => 'Login User',
            'seller_email' => 'login@test.com',
            'seller_pass' => password_hash('mypassword', PASSWORD_DEFAULT),
            'seller_status' => 'active',
        ]);

        $response = $this->post('/login', [
            '_token' => csrf_token(),
            'seller_user_name' => 'loginuser',
            'seller_pass' => 'mypassword',
            'access' => 'Login',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('seller_user_name', 'loginuser');
    }

    // ─────────────────────────────────────────────────────────────────
    //  Bug B: GET /admin/login must return 200
    // ─────────────────────────────────────────────────────────────────

    #[Test]
    public function get_admin_login_returns_200(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
        $response->assertSee('ADMIN');
        $response->assertSee('admin_email');
        $response->assertSee('admin_pass');
    }

    #[Test]
    public function get_admin_login_contains_form_posting_to_self(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
        $response->assertSee('method="post"', false);
        $response->assertSee('name="admin_login"', false);
    }

    #[Test]
    public function post_admin_login_with_valid_credentials_redirects(): void
    {
        DB::table('admins')->insert([
            'admin_name' => 'Admin',
            'admin_user_name' => 'admin',
            'admin_email' => 'admin@test.com',
            'admin_pass' => password_hash('adminpass', PASSWORD_DEFAULT),
        ]);

        $response = $this->post('/admin/login', [
            '_token' => csrf_token(),
            'admin_email' => 'admin@test.com',
            'admin_pass' => 'adminpass',
            'admin_login' => 'Sign in',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('admin_email');
    }

    #[Test]
    public function post_admin_login_with_invalid_credentials_flashes_error(): void
    {
        $response = $this->post('/admin/login', [
            '_token' => csrf_token(),
            'admin_email' => 'wrong@test.com',
            'admin_pass' => 'wrongpass',
            'admin_login' => 'Sign in',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/admin/login');
        $response->assertSessionHas('admin_login_error');
    }

    // ─────────────────────────────────────────────────────────────────
    //  POST /index should also work (legacy alias)
    // ─────────────────────────────────────────────────────────────────

    #[Test]
    public function post_index_with_login_payload_works(): void
    {
        DB::table('sellers')->insert([
            'seller_user_name' => 'indexuser',
            'seller_name' => 'Index User',
            'seller_email' => 'index@test.com',
            'seller_pass' => password_hash('pass123', PASSWORD_DEFAULT),
            'seller_status' => 'active',
        ]);

        $response = $this->post('/index', [
            '_token' => csrf_token(),
            'seller_user_name' => 'indexuser',
            'seller_pass' => 'pass123',
            'login' => 'Login Now',
        ]);

        $response->assertRedirect('/');
    }

    // ─────────────────────────────────────────────────────────────────
    //  POST /register should also work
    // ─────────────────────────────────────────────────────────────────

    #[Test]
    public function post_register_with_legacy_fields_works(): void
    {
        $response = $this->post('/register', [
            '_token' => csrf_token(),
            'name' => 'Reg User',
            'u_name' => 'reguser',
            'email' => 'reg@test.com',
            'pass' => 'secret123',
            'con_pass' => 'secret123',
            'register' => 'Register',
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('sellers', ['seller_user_name' => 'reguser']);
    }
}
