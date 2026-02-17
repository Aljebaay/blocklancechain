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
    $proposalVideo = $proposal->proposal_video ?? '';
    $proposalEnableReferrals = $proposal->proposal_enable_referrals ?? 'no';

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

    // Currency formatting via shared showPrice helper
    $formattedPrice = $ld ? $ld->showPrice($proposalPrice) : '$' . number_format((float)$proposalPrice, 2);

    // Online status
    $isOnline = false;
    if ($pSeller && !empty($pSeller->seller_activity)) {
        $lastActivity = strtotime($pSeller->seller_activity);
        $isOnline = (time() - $lastActivity) < 300;
    }

    // Favorite class - check if user has favorited this proposal
    $showFavoriteClass = 'proposal-favorite';
    if ($isLoggedIn && $loginSellerId) {
        $hasFavorited = DB::table('favorites')
            ->where('seller_id', $loginSellerId)
            ->where('proposal_id', $proposalId)
            ->count();
        if ($hasFavorited > 0) {
            $showFavoriteClass = 'proposal-favorite-active';
        }
    }

    // Instant delivery check
    $instantDeliveryRow = DB::table('instant_deliveries')->where('proposal_id', $proposalId)->first();
    $enableDelivery = $instantDeliveryRow->enable ?? 0;

    // Video plugin check
    $videoPlugin = $row_general_settings->video_plugin ?? 0;
    $enableVideo = 0;
    if ($videoPlugin == 1) {
        $videoSettings = DB::table('proposal_videosettings')->where('proposal_id', $proposalId)->first();
        $enableVideo = $videoSettings->enable ?? 0;
    }

    $enableReferrals = $enable_referrals ?? 'no';
@endphp
<div class="proposal-card-base mp-proposal-card">
	<!--- proposal-card-base mp-proposal-card Starts --->
	<a href="{{ $site_url }}/proposals/{{ $pSellerUserName }}/{{ $proposalUrl }}">
		<img src="{{ $proposalImg1 }}" class="img-fluid">
	</a>
	<div class="proposal-card-caption">
		<!--- proposal-card-caption Starts --->
		<div class="proposal-seller-info">
			<!--- gig-seller-info Starts --->
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
						<i data-id="{{ $proposalId }}" href="#" class="fa fa-heart {{ $showFavoriteClass }}" data-toggle="tooltip" data-placement="top" title="Favorite"></i>
					@endif
				@else
					<a href="#" data-toggle="modal" data-target="#login-modal">
						<i class="fa fa-heart proposal-favorite" data-toggle="tooltip" data-placement="top" title="Favorite"></i>
					</a>
				@endif
			</div>
		</div>
		<!--- gig-seller-info Ends --->
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
	<!--- proposal-card-caption Ends --->
	<footer class="proposal-card-footer">
		<!--- proposal-card-footer Starts --->
		<div class="proposal-fav">

			@if($proposalEnableReferrals == 'yes' && $enableReferrals == 'yes')

				@if($isLoggedIn)
					@if($proposalSellerId != $loginSellerId)
						<a class="icn-list proposal-offer" data-id="{{ $proposalId }}">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="16" height="16"><path d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0zm0 1.5a6.5 6.5 0 1 0 0 13 6.5 6.5 0 0 0 0-13zM6.379 4.96L8 6.586l1.621-1.626a.75.75 0 1 1 1.063 1.06L9.061 7.647l1.623 1.627a.75.75 0 1 1-1.063 1.06L8 8.707l-1.621 1.626a.75.75 0 1 1-1.063-1.06L6.94 7.647 5.316 6.02a.75.75 0 0 1 1.063-1.06z"></path></svg>
						</a>
					@endif
				@else
					<a class="icn-list" data-toggle="modal" data-target="#login-modal">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="16" height="16"><path d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0zm0 1.5a6.5 6.5 0 1 0 0 13 6.5 6.5 0 0 0 0-13zM6.379 4.96L8 6.586l1.621-1.626a.75.75 0 1 1 1.063 1.06L9.061 7.647l1.623 1.627a.75.75 0 1 1-1.063 1.06L8 8.707l-1.621 1.626a.75.75 0 1 1-1.063-1.06L6.94 7.647 5.316 6.02a.75.75 0 0 1 1.063-1.06z"></path></svg>
					</a>
				@endif

			@endif

			@if($enableVideo == 1)
				<a class="icn-list" data-toggle="tooltip" data-placement="top" title="{{ $lang['proposals']['video'] ?? 'Video' }}">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="16" height="16"><path d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0zm0 1.5a6.5 6.5 0 1 0 0 13 6.5 6.5 0 0 0 0-13zm-2 3.25a.75.75 0 0 1 .388.108l5 3a.75.75 0 0 1 0 1.284l-5 3A.75.75 0 0 1 5.25 12V5.5a.75.75 0 0 1 .75-.75z"></path></svg>
				</a>
			@endif

			@if($enableDelivery == 1)
				<a class="icn-list" data-toggle="tooltip" data-placement="top" title="{{ $lang['proposals']['instant_delivery'] ?? 'Instant Delivery' }}">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="16" height="16"><path d="M8.438 0l.262.003c4.078.146 7.3 3.527 7.3 7.622 0 4.221-3.404 7.625-7.625 7.625-4.22 0-7.625-3.404-7.625-7.625C.75 3.592 4.2.175 8.438 0zM8.375 2C5.27 2 2.75 4.52 2.75 7.625s2.52 5.625 5.625 5.625 5.625-2.52 5.625-5.625S11.48 2 8.375 2zm.406 1.875V7.22l2.344 1.406a.625.625 0 0 1-.643 1.072l-2.67-1.6a.625.625 0 0 1-.281-.523V3.875a.625.625 0 0 1 1.25 0z"></path></svg>
				</a>
			@endif

		</div>
		<div class="proposal-price">
			<a>
				<small>{{ $lang['proposals']['starting_at'] ?? 'STARTING AT' }}</small>{!! $formattedPrice !!}
			</a>
		</div>
	</footer>
	<!--- proposal-card-footer Ends --->
</div>
<!--- proposal-card-base mp-proposal-card Ends --->
