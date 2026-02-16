@php
    use Illuminate\Support\Facades\DB;

    $isLoggedIn = session()->has('seller_user_name');
    $loginSellerId = $seller_id ?? 0;
    $ld = $legacyData ?? null;

    // Get proposal data
    $proposalId = $proposal->proposal_id;
    $proposalTitle = $proposal->proposal_title;
    $proposalPrice = $proposal->proposal_price;
    if ($proposalPrice == 0) {
        $basicPkg = DB::table('proposal_packages')
            ->where('proposal_id', $proposalId)
            ->where('package_name', 'Basic')
            ->first();
        $proposalPrice = $basicPkg->price ?? 0;
    }
    $proposalImg1 = $ld ? $ld->getImageUrl2('proposals', 'proposal_img1', $proposal->proposal_img1 ?? '') : '';
    $proposalSellerId = $proposal->proposal_seller_id;
    $proposalRating = $proposal->proposal_rating;
    $proposalUrl = $proposal->proposal_url;

    // Seller info
    $pSeller = DB::table('sellers')->where('seller_id', $proposalSellerId)->first();
    $pSellerUserName = $pSeller->seller_user_name ?? '';
    $pSellerImage = $ld ? $ld->getImageUrl2('sellers', 'seller_image', $pSeller->seller_image ?? '') : '';
    $pSellerLevel = $pSeller->seller_level ?? 0;

    // Level name
    $levelMeta = DB::table('seller_levels_meta')
        ->where('level_id', $pSellerLevel)
        ->where('language_id', $siteLanguage ?? 1)
        ->first();
    $sellerLevelName = $levelMeta->title ?? '';

    // Reviews
    $reviews = DB::table('buyer_reviews')->where('proposal_id', $proposalId)->get();
    $countReviews = $reviews->count();
    $averageRating = $countReviews > 0 ? $reviews->avg('buyer_rating') : 0;

    // Currency formatting
    $currencySymbol = $s_currency ?? '$';
    $currencyPosition = $currency_position ?? 'left';
    $formattedPrice = ($currencyPosition === 'left')
        ? $currencySymbol . number_format((float)$proposalPrice, 2)
        : number_format((float)$proposalPrice, 2) . $currencySymbol;

    // Online status
    $isOnline = false;
    if ($pSeller && !empty($pSeller->seller_activity)) {
        $lastActivity = strtotime($pSeller->seller_activity);
        $isOnline = (time() - $lastActivity) < 300;
    }
@endphp
<div class="proposal-card-base mp-proposal-card">
	<a href="{{ $site_url }}/proposals/{{ $pSellerUserName }}/{{ $proposalUrl }}">
		<img src="{{ $proposalImg1 }}" class="img-fluid">
	</a>
	<div class="proposal-card-caption">
		<div class="proposal-seller-info">
			<span class="fit-avatar s24">
				<img src="{{ $pSellerImage }}" class="rounded-circle" width="32" height="32">
			</span>
			<div class="seller-info-wrapper">
				<a href="{{ $site_url }}/{{ $pSellerUserName }}" class="seller-name">
					{{ $pSellerUserName }}
				</a>
				<div class="gig-seller-tooltip">
					{{ $sellerLevelName }}
				</div>
			</div>
			<div class="favoriteIcon">
				@if($isLoggedIn)
					@if($proposalSellerId != $loginSellerId)
						<i data-id="{{ $proposalId }}" href="#" class="fa fa-heart proposal-favorite" data-toggle="tooltip" data-placement="top" title="Favorite"></i>
					@endif
				@else
					<a href="#" data-toggle="modal" data-target="#login-modal">
						<i class="fa fa-heart proposal-favorite" data-toggle="tooltip" data-placement="top" title="Favorite"></i>
					</a>
				@endif
			</div>
		</div>
		<a href="{{ $site_url }}/proposals/{{ $pSellerUserName }}/{{ $proposalUrl }}" class="proposal-link-main js-proposal-card-imp-data">
			<h3>{{ $proposalTitle }}</h3>
		</a>
		<div class="rating-badges-container">
			<span class="proposal-rating">
				<svg class="fit-svg-icon full_star" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg" width="15" height="15">
					<path d="M1728 647q0 22-26 48l-363 354 86 500q1 7 1 20 0 21-10.5 35.5t-30.5 14.5q-19 0-40-12l-449-236-449 236q-22 12-40 12-21 0-31.5-14.5t-10.5-35.5q0-6 2-20l86-500-364-354q-25-27-25-48 0-37 56-46l502-73 225-455q19-41 49-41t49 41l225 455 502 73q56 9 56 46z"></path>
				</svg>
				<span>
					<strong>@if($proposalRating == '0') 0.0 @else {{ number_format($averageRating, 1) }} @endif</strong> ({{ $countReviews }})
				</span>
			</span>
		</div>
		@if($isOnline)
			<div class="is-online float-right">
				<i class="fa fa-circle"></i> {{ $lang['proposals']['online'] ?? 'Online' }}
			</div>
		@endif
	</div>
	<footer class="proposal-card-footer">
		<div class="proposal-fav">
		</div>
		<div class="proposal-price">
			<a>
				<small>{{ $lang['proposals']['starting_at'] ?? 'STARTING AT' }}</small>{!! $formattedPrice !!}
			</a>
		</div>
	</footer>
</div>
