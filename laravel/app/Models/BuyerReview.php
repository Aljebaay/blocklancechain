<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * BuyerReview model - maps to legacy `buyer_reviews` table.
 */
class BuyerReview extends Model
{
    protected $table = 'buyer_reviews';

    public $timestamps = false;

    protected $fillable = [
        'review_seller_id',
        'review_buyer_id',
        'review_proposal_id',
        'review_order_id',
        'buyer_rating',
        'buyer_review',
        'review_date',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'review_seller_id', 'seller_id');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'review_buyer_id', 'seller_id');
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class, 'review_proposal_id', 'proposal_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'review_order_id', 'order_id');
    }
}
