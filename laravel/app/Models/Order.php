<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Order model - maps to legacy `orders` table.
 */
class Order extends Model
{
    protected $table = 'orders';

    protected $primaryKey = 'order_id';

    public $timestamps = false;

    protected $fillable = [
        'buyer_id',
        'seller_id',
        'proposal_id',
        'order_number',
        'order_status',
        'order_amount',
        'order_quantity',
        'order_date',
        'order_delivery_date',
        'order_completed_date',
        'order_cancelled_date',
        'order_delivered_date',
        'order_extras',
        'order_note',
        'order_package',
        'payment_method',
        'payment_id',
        'processing_fee',
        'coupon_discount',
        'order_tip',
    ];

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'buyer_id', 'seller_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'seller_id', 'seller_id');
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class, 'proposal_id', 'proposal_id');
    }

    public function buyerReview(): HasMany
    {
        return $this->hasMany(BuyerReview::class, 'review_order_id', 'order_id');
    }

    public function sellerReview(): HasMany
    {
        return $this->hasMany(SellerReview::class, 'review_order_id', 'order_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(OrderMessage::class, 'order_id', 'order_id');
    }

    /**
     * Scope: active orders.
     */
    public function scopeActive($query)
    {
        return $query->where('order_status', 'active');
    }

    /**
     * Scope: completed orders.
     */
    public function scopeCompleted($query)
    {
        return $query->where('order_status', 'completed');
    }

    /**
     * Scope: cancelled orders.
     */
    public function scopeCancelled($query)
    {
        return $query->where('order_status', 'cancelled');
    }

    /**
     * Scope: delivered orders.
     */
    public function scopeDelivered($query)
    {
        return $query->where('order_status', 'delivered');
    }
}
