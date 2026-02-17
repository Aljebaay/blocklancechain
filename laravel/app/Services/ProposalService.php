<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BuyerReview;
use App\Models\Proposal;
use App\Models\Seller;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * ProposalService - handles proposal-related business logic.
 * Replaces legacy functions from functions/functions.php and functions/filter.php.
 */
class ProposalService
{
    /**
     * Get proposals with filtering and pagination.
     * Replaces legacy get_proposals() function.
     *
     * @param  string  $type  - search, category, featured, top, random, tag
     * @param  array  $filters  - request filters
     * @param  int  $perPage  - items per page
     */
    public function getProposals(
        string $type,
        array $filters = [],
        int $perPage = 12,
    ): LengthAwarePaginator {
        $query = Proposal::with(['seller', 'buyerReviews'])
            ->where('proposal_status', 'active');

        $query = $this->applyTypeFilter($query, $type, $filters);
        $query = $this->applyCommonFilters($query, $filters);

        // Sorting
        if ($type === 'random') {
            $query->inRandomOrder();
        } elseif ($type === 'top') {
            $query->where('proposal_top_rated', 'yes');
        } elseif ($type === 'featured') {
            $query->where('proposal_featured', 'yes');
        }

        return $query->paginate($perPage);
    }

    /**
     * Get a single proposal by URL slug and seller username.
     */
    public function getProposalBySlug(string $username, string $proposalUrl): ?Proposal
    {
        return Proposal::with([
            'seller',
            'buyerReviews.buyer',
            'extras',
            'faqs',
            'packages',
            'gallery',
        ])
            ->whereHas('seller', function (Builder $query) use ($username) {
                $query->where('seller_user_name', $username);
            })
            ->where('proposal_url', $proposalUrl)
            ->first();
    }

    /**
     * Get a single proposal by ID.
     */
    public function getProposalById(int $proposalId): ?Proposal
    {
        return Proposal::with([
            'seller',
            'buyerReviews.buyer',
            'extras',
            'faqs',
            'packages',
            'gallery',
        ])->find($proposalId);
    }

    /**
     * Get proposals by seller.
     */
    public function getProposalsBySeller(int $sellerId, string $status = 'active'): Collection
    {
        return Proposal::where('proposal_seller_id', $sellerId)
            ->where('proposal_status', $status)
            ->get();
    }

    /**
     * Create a new proposal.
     */
    public function createProposal(array $data): Proposal
    {
        return Proposal::create($data);
    }

    /**
     * Update a proposal.
     */
    public function updateProposal(int $proposalId, array $data): bool
    {
        return Proposal::where('proposal_id', $proposalId)->update($data) > 0;
    }

    /**
     * Delete a proposal.
     */
    public function deleteProposal(int $proposalId): bool
    {
        return Proposal::where('proposal_id', $proposalId)->delete() > 0;
    }

    /**
     * Pause a proposal.
     */
    public function pauseProposal(int $proposalId): bool
    {
        return $this->updateProposal($proposalId, ['proposal_status' => 'paused']);
    }

    /**
     * Activate a proposal.
     */
    public function activateProposal(int $proposalId): bool
    {
        return $this->updateProposal($proposalId, ['proposal_status' => 'active']);
    }

    /**
     * Apply type-specific filter to the query builder.
     */
    private function applyTypeFilter(Builder $query, string $type, array $filters): Builder
    {
        switch ($type) {
            case 'search':
                if (! empty($filters['search'])) {
                    $search = $filters['search'];
                    $query->where(function (Builder $q) use ($search) {
                        $q->where('proposal_title', 'LIKE', "%{$search}%")
                            ->orWhere('proposal_tags', 'LIKE', "%{$search}%");
                    });
                }
                break;

            case 'category':
                if (! empty($filters['category_id'])) {
                    $query->where('proposal_category', $filters['category_id']);
                }
                if (! empty($filters['sub_category_id'])) {
                    $query->where('proposal_sub_category', $filters['sub_category_id']);
                }
                break;

            case 'tag':
                if (! empty($filters['tag'])) {
                    $query->where('proposal_tags', 'LIKE', "%{$filters['tag']}%");
                }
                break;
        }

        return $query;
    }

    /**
     * Apply common filters (price range, delivery time, seller level, etc.).
     */
    private function applyCommonFilters(Builder $query, array $filters): Builder
    {
        // Price range filter
        if (! empty($filters['min_price'])) {
            $query->where('proposal_price', '>=', (float) $filters['min_price']);
        }
        if (! empty($filters['max_price'])) {
            $query->where('proposal_price', '<=', (float) $filters['max_price']);
        }

        // Delivery time filter
        if (! empty($filters['delivery_time'])) {
            $query->where('proposal_delivery_time', '<=', (int) $filters['delivery_time']);
        }

        // Seller level filter
        if (! empty($filters['seller_level'])) {
            $query->whereHas('seller', function (Builder $q) use ($filters) {
                if (is_array($filters['seller_level'])) {
                    $q->whereIn('seller_level', $filters['seller_level']);
                } else {
                    $q->where('seller_level', $filters['seller_level']);
                }
            });
        }

        // Seller country filter
        if (! empty($filters['seller_country'])) {
            $query->whereHas('seller', function (Builder $q) use ($filters) {
                if (is_array($filters['seller_country'])) {
                    $q->whereIn('seller_country', $filters['seller_country']);
                } else {
                    $q->where('seller_country', $filters['seller_country']);
                }
            });
        }

        // Online sellers only filter
        if (! empty($filters['online_sellers'])) {
            $threshold = now()->subSeconds(10)->format('Y-m-d H:i:s');
            $query->whereHas('seller', function (Builder $q) use ($threshold) {
                $q->where('seller_activity', '>', $threshold);
            });
        }

        return $query;
    }

    /**
     * Get average rating for a proposal.
     */
    public function getAverageRating(int $proposalId): float
    {
        $avg = BuyerReview::where('review_proposal_id', $proposalId)
            ->avg('buyer_rating');

        return round((float) $avg, 1);
    }
}
