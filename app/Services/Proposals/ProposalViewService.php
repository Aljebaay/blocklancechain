<?php

namespace App\Services\Proposals;

use Illuminate\Support\Facades\DB;

class ProposalViewService
{
    /**
     * Fetch proposal + seller + related data for public view.
     *
     * @return array keyed payload for Blade
     */
    public function fetch(string $username, string $slug, ?int $viewerSellerId = null): ?array
    {
        $conn = DB::connection('legacy');

        $seller = $conn->table('sellers')->where('seller_user_name', $username)->first();
        if (!$seller) {
            return null;
        }

        // Status visibility rules approximated for public viewers
        $proposal = $conn->table('proposals')
            ->where('proposal_url', $slug)
            ->where('proposal_seller_id', $seller->seller_id)
            ->whereNotIn('proposal_status', [
                'draft', 'admin_pause', 'pause', 'pending', 'trash', 'declined', 'modification', 'deleted',
            ])
            ->first();

        if (!$proposal) {
            return null;
        }

        $cat = $conn->table('categories')->where('cat_id', $proposal->proposal_cat_id)->first();
        $child = $conn->table('categories_children')->where('child_id', $proposal->proposal_child_id)->first();
        $delivery = $conn->table('delivery_times')->where('delivery_id', $proposal->delivery_id)->first();

        $reviews = $conn->table('buyer_reviews')->where('proposal_id', $proposal->proposal_id)->pluck('buyer_rating')->all();
        $ratingCount = count($reviews);
        $ratingAvg = $ratingCount ? array_sum($reviews) / $ratingCount : 0;

        $extras = $conn->table('proposals_extras')->where('proposal_id', $proposal->proposal_id)->get();
        $faqs = $conn->table('proposals_faq')->where('proposal_id', $proposal->proposal_id)->get();

        $favorite = false;
        $inCart = false;
        if ($viewerSellerId) {
            $favorite = $conn->table('favorites')->where([
                'proposal_id' => $proposal->proposal_id,
                'seller_id' => $viewerSellerId,
            ])->exists();

            $inCart = $conn->table('cart')->where([
                'proposal_id' => $proposal->proposal_id,
                'seller_id' => $viewerSellerId,
            ])->exists();
        }

        return [
            'proposal' => $proposal,
            'seller' => $seller,
            'category' => $cat,
            'child' => $child,
            'delivery' => $delivery,
            'reviews' => $reviews,
            'rating_count' => $ratingCount,
            'rating_avg' => $ratingAvg,
            'extras' => $extras,
            'faqs' => $faqs,
            'favorite' => $favorite,
            'in_cart' => $inCart,
        ];
    }

    public function lookupSellerId(string $username): ?int
    {
        $row = DB::connection('legacy')->table('sellers')->where('seller_user_name', $username)->first();
        return $row ? (int) $row->seller_id : null;
    }
}
