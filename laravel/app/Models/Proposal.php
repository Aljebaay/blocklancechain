<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Proposal model - maps to legacy `proposals` table.
 */
class Proposal extends Model
{
    protected $table = 'proposals';

    protected $primaryKey = 'proposal_id';

    public $timestamps = false;

    protected $fillable = [
        'proposal_seller_id',
        'proposal_title',
        'proposal_url',
        'proposal_desc',
        'proposal_image',
        'proposal_category',
        'proposal_sub_category',
        'proposal_status',
        'proposal_price',
        'proposal_delivery_time',
        'proposal_tags',
        'proposal_date',
        'proposal_views',
        'proposal_featured',
        'proposal_top_rated',
        'proposal_video',
        'proposal_video_type',
        'proposal_instant_delivery',
        'proposal_requirements',
        'proposal_revisions',
        'language_id',
    ];

    /**
     * The seller who owns this proposal.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'proposal_seller_id', 'seller_id');
    }

    /**
     * Category this proposal belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'proposal_category', 'id');
    }

    /**
     * Sub-category this proposal belongs to.
     */
    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(ChildCategory::class, 'proposal_sub_category', 'id');
    }

    /**
     * Buyer reviews for this proposal.
     */
    public function buyerReviews(): HasMany
    {
        return $this->hasMany(BuyerReview::class, 'review_proposal_id', 'proposal_id');
    }

    /**
     * Extras/add-ons for this proposal.
     */
    public function extras(): HasMany
    {
        return $this->hasMany(ProposalExtra::class, 'proposal_id', 'proposal_id');
    }

    /**
     * FAQs for this proposal.
     */
    public function faqs(): HasMany
    {
        return $this->hasMany(ProposalFaq::class, 'proposal_id', 'proposal_id');
    }

    /**
     * Packages for this proposal.
     */
    public function packages(): HasMany
    {
        return $this->hasMany(ProposalPackage::class, 'proposal_id', 'proposal_id');
    }

    /**
     * Gallery images for this proposal.
     */
    public function gallery(): HasMany
    {
        return $this->hasMany(ProposalGallery::class, 'proposal_id', 'proposal_id');
    }

    /**
     * Orders for this proposal.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'proposal_id', 'proposal_id');
    }

    /**
     * Get average rating for this proposal.
     */
    public function getAverageRatingAttribute(): float
    {
        $reviews = $this->buyerReviews;

        if ($reviews->isEmpty()) {
            return 0.0;
        }

        return round($reviews->avg('buyer_rating'), 1);
    }

    /**
     * Scope: active proposals only.
     */
    public function scopeActive($query)
    {
        return $query->where('proposal_status', 'active');
    }

    /**
     * Scope: featured proposals only.
     */
    public function scopeFeatured($query)
    {
        return $query->where('proposal_featured', 'yes');
    }

    /**
     * Scope: top-rated proposals only.
     */
    public function scopeTopRated($query)
    {
        return $query->where('proposal_top_rated', 'yes');
    }
}
