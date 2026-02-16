@php
use Illuminate\Support\Facades\DB;

$sLang = $siteLanguage ?? 1;
$currentTag = $tag ?? '';

$proposals = DB::table('proposals')
    ->where('proposal_tags', 'LIKE', '%'.$currentTag.'%')
    ->where('proposal_status', 'active')
    ->orderBy('proposal_id', 'DESC')
    ->limit(12)
    ->get();
@endphp

@if($proposals->count() > 0)
    @foreach($proposals as $proposal)
    @php
        $seller = DB::table('sellers')->where('seller_id', $proposal->proposal_seller_id)->first();
        $seller_user_name = $seller->seller_user_name ?? '';
        $seller_image = $seller->seller_image ?? '';
        $seller_level = $seller->seller_level ?? 0;

        $level_meta = DB::table('seller_levels_meta')
            ->where('level_id', $seller_level)
            ->where('language_id', $sLang)
            ->first();
        $level_title = $level_meta->title ?? '';

        $ratings = [];
        $reviews = DB::table('buyer_reviews')->where('proposal_id', $proposal->proposal_id)->get();
        foreach($reviews as $rev) {
            $ratings[] = $rev->buyer_rating;
        }
        $total = array_sum($ratings);
        $avg_rating = count($ratings) > 0 ? $total / count($ratings) : 0;
        $proposal_rating = substr((string)$avg_rating, 0, 1);
        if(empty($proposal_rating) || $proposal_rating == 'N') $proposal_rating = 0;

        $packages = DB::table('proposal_packages')->where('proposal_id', $proposal->proposal_id)->get();
        $starting_at = $proposal->proposal_price;
        if($starting_at == 0 && $packages->count() > 0) {
            $starting_at = $packages->min('price') ?? 0;
        }
    @endphp
    <div class="col-lg-3 col-md-4 col-sm-6 col-12 mb-4">
        @include('legacy.partials.proposal-card', [
            'proposal' => $proposal,
            'seller_user_name' => $seller_user_name,
            'seller_image' => $seller_image,
            'level_title' => $level_title,
            'proposal_rating' => $proposal_rating,
            'count_reviews' => count($ratings),
            'starting_at' => $starting_at,
        ])
    </div>
    @endforeach
@else
<div class="col-md-12 text-center p-5">
    <h4><i class="fa fa-smile-o"></i> {{ $lang['search_proposals']['no_results'] ?? "We haven't found any proposals/services matching that search." }}</h4>
</div>
@endif
