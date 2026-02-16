<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Seller model - maps to legacy `sellers` table.
 * This is the primary user model for the marketplace.
 */
class Seller extends Authenticatable
{
    use Notifiable;

    protected $table = 'sellers';

    protected $primaryKey = 'seller_id';

    public $timestamps = false;

    protected $fillable = [
        'seller_user_name',
        'seller_name',
        'seller_email',
        'seller_pass',
        'seller_image',
        'seller_headline',
        'seller_about',
        'seller_country',
        'seller_level',
        'seller_status',
        'seller_ip',
        'seller_activity',
        'seller_register_date',
        'seller_recent_delivery',
        'seller_phone',
        'seller_language',
        'seller_balance',
        'seller_pending_balance',
        'seller_withdrawn',
        'seller_referral_code',
        'seller_referred_by',
        'seller_verification_code',
        'seller_email_verified',
        'seller_profile_completed',
    ];

    protected $hidden = [
        'seller_pass',
    ];

    /**
     * Get the password attribute for authentication.
     */
    public function getAuthPassword(): string
    {
        return $this->seller_pass;
    }

    /**
     * Get the name of the unique identifier for the user.
     */
    public function getAuthIdentifierName(): string
    {
        return 'seller_id';
    }

    /**
     * Proposals owned by this seller.
     */
    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class, 'proposal_seller_id', 'seller_id');
    }

    /**
     * Reviews received by this seller (as a seller).
     */
    public function buyerReviews(): HasMany
    {
        return $this->hasMany(BuyerReview::class, 'review_seller_id', 'seller_id');
    }

    /**
     * Reviews given by this seller (as a buyer).
     */
    public function sellerReviews(): HasMany
    {
        return $this->hasMany(SellerReview::class, 'review_seller_id', 'seller_id');
    }

    /**
     * Orders where this seller is the buyer.
     */
    public function buyingOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'buyer_id', 'seller_id');
    }

    /**
     * Orders where this seller is the service provider.
     */
    public function sellingOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'seller_id', 'seller_id');
    }

    /**
     * Conversations involving this seller.
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'sender_id', 'seller_id');
    }

    /**
     * Notifications for this seller.
     */
    public function sellerNotifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'notification_seller_id', 'seller_id');
    }

    /**
     * Check if seller is online based on activity timestamp.
     */
    public function isOnline(): bool
    {
        if (empty($this->seller_activity)) {
            return false;
        }

        $threshold = now()->subSeconds(10);

        return $this->seller_activity > $threshold->format('Y-m-d H:i:s');
    }

    /**
     * Get the seller's average rating.
     */
    public function getAverageRatingAttribute(): float
    {
        $reviews = $this->buyerReviews;

        if ($reviews->isEmpty()) {
            return 0.0;
        }

        return round($reviews->avg('buyer_rating'), 1);
    }
}
