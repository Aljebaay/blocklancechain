@php
    use Illuminate\Support\Facades\DB;
    $search_query = session('search_query', '');
    $s_value = "%{$search_query}%";
    $sLang = $siteLanguage ?? 1;

    $proposals = DB::select("select * from proposals where proposal_title like ? AND proposal_status='active' ORDER BY 1 DESC LIMIT 12", [$s_value]);
@endphp

@if(count($proposals) == 0)
    <div class="col-12 text-center mt-5 mb-5">
        <h3>{{ $lang['search']['no_results'] ?? 'No proposals found.' }}</h3>
    </div>
@endif

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
