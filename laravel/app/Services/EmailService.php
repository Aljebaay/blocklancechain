<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Mail;

/**
 * EmailService - handles email sending.
 * Replaces legacy mailer.php and email.php functions.
 * Uses Laravel's Mail facade instead of PHPMailer directly.
 */
class EmailService
{
    public function __construct(
        private readonly SiteSettingsService $settingsService,
    ) {}

    /**
     * Send a generic email.
     * Replaces legacy PHPMailer-based email sending.
     */
    public function send(string $to, string $subject, string $body): bool
    {
        try {
            Mail::html($body, function ($message) use ($to, $subject) {
                $settings = $this->settingsService->getGeneralSettings();
                $message->to($to)
                    ->subject($subject)
                    ->from(
                        config('mail.from.address', $settings->site_email_address ?? 'noreply@example.com'),
                        config('mail.from.name', $settings->site_name ?? 'GigZone'),
                    );
            });

            return true;
        } catch (\Exception $e) {
            report($e);

            return false;
        }
    }

    /**
     * Send welcome/signup email.
     * Replaces legacy signup email function.
     */
    public function sendWelcomeEmail(string $to, string $username): bool
    {
        $settings = $this->settingsService->getGeneralSettings();
        $siteName = $settings->site_name ?? 'GigZone';

        $subject = "Welcome to {$siteName}!";
        $body = "<h2>Welcome to {$siteName}, {$username}!</h2>"
            . "<p>Thank you for joining our marketplace. You can now start exploring services and offering your own.</p>"
            . "<p>Best regards,<br>{$siteName} Team</p>";

        return $this->send($to, $subject, $body);
    }

    /**
     * Send order notification email.
     */
    public function sendOrderNotification(string $to, string $orderNumber, string $status): bool
    {
        $settings = $this->settingsService->getGeneralSettings();
        $siteName = $settings->site_name ?? 'GigZone';

        $subject = "Order #{$orderNumber} - Status Update";
        $body = "<h2>Order Update</h2>"
            . "<p>Your order <strong>#{$orderNumber}</strong> status has been updated to: <strong>{$status}</strong>.</p>"
            . "<p>Best regards,<br>{$siteName} Team</p>";

        return $this->send($to, $subject, $body);
    }
}
