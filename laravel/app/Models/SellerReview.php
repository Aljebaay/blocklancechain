<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SellerReview model - maps to legacy `seller_reviews` table.
 */
class SellerReview extends Model
{
    protected $table = 'seller_reviews';

    public $timestamps = false;

    protected $fillable = [
        'review_seller_id',
        'review_buyer_id',
        'review_proposal_id',
        'review_order_id',
        'seller_rating',
        'seller_review',
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
}
