@php
    use Illuminate\Support\Facades\DB;
    $search_query = session('search_query', '');
    $s_value = "%{$search_query}%";
    $sLang = $siteLanguage ?? 1;
    $ld = $legacyData ?? null;
    $s_currency = $s_currency ?? '$';
    $currency_position = $currency_position ?? 'left';

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
    $sellerName = $seller->seller_user_name ?? '';
    $sellerImage = $ld ? $ld->getImageUrl2('sellers', 'seller_image', $seller->seller_image ?? '') : '';
    $proposalImage = $ld ? $ld->getImageUrl2('proposals', 'proposal_image', $proposal->proposal_image ?? '') : '';
    $sellerLevel = DB::table('seller_levels_meta')->where('level_id', $seller->seller_level ?? 1)->where('language_id', $sLang)->first();
    $levelTitle = $sellerLevel->title ?? '';
    $package = DB::table('proposal_packages')->where('proposal_id', $proposal->proposal_id)->first();
    $price = $package->price ?? 0;
    $reviews = DB::table('buyer_reviews')->where('proposal_id', $proposal->proposal_id)->get();
    $reviewCount = $reviews->count();
    $avgRating = $reviewCount > 0 ? round($reviews->avg('rating'), 1) : 0;
    $proposalUrl = $site_url . '/' . $sellerName . '/' . $proposal->proposal_url;
@endphp
@include('legacy.partials.proposal-card', [
    'proposal' => $proposal,
    'sellerName' => $sellerName,
    'sellerImage' => $sellerImage,
    'proposalImage' => $proposalImage,
    'levelTitle' => $levelTitle,
    'price' => $price,
    'reviewCount' => $reviewCount,
    'avgRating' => $avgRating,
    'proposalUrl' => $proposalUrl,
])
@endforeach
