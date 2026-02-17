@php
    use Illuminate\Support\Facades\DB;
    $ld = $legacyData ?? null;
    $langDir = $lang_dir ?? 'left';
    $loginSellerId = $seller_id ?? 0;
    $loginUserName = $login_user_name ?? session('seller_user_name', '');
    $sellerImage = $seller_image ?? '';
    $countActiveProposals = $count_active_proposals ?? 0;

    // Sidebar buy-it-again proposal IDs
    $buyAgainIds = $sidebar_buy_again ?? [];
    // Recently viewed proposal IDs
    $recentlyViewedIds = $sidebar_recently_viewed ?? [];
@endphp
<div class="card rounded-0 mb-3 welcome-box">
	<!-- card rounded-0 mb-3 welcome-box Starts -->
	<div class="card-body pb-2">
		<!-- card-body Starts -->
		<center>
			@if(!empty($sellerImage))
				<img src="{{ $sellerImage }}" class="img-fluid rounded-circle mb-3">
			@else
				<img src="{{ $site_url }}/user_images/empty-image.png" class="img-fluid rounded-circle mb-3">
			@endif
		</center>
		<h5>{{ $lang['welcome'] ?? 'Welcome' }}, <span class="text-success">{{ ucfirst($loginUserName) }}</span> </h5>
		<hr>
		<div class="row m-0">
			<!--- row Starts --->
			<div class="col-lg-6 m-0 p-0 pr-2 pb-lg-0 pr-lg-2 pb-md-2 pr-sm-2">
				<!--- col-md-6 Starts --->
				<h5><a href="{{ $site_url }}/dashboard">{{ $lang['menu']['dashboard'] ?? 'Dashboard' }}</a></h5>
				<h5><a href="{{ $site_url }}/proposals/create_proposal">{{ $lang['user_home']['add_proposal'] ?? 'Add a Proposal' }}</a></h5>
				<h5 class="mb-0"><a href="{{ $site_url }}/requests/post_request">{{ $lang['menu']['post_request'] ?? 'Post a Request' }}</a></h5>
			</div>
			<!--- col-md-6 Ends --->
			<div class="col-lg-6 m-0 p-0 pl-2 pt-lg-0 pl-lg-2 pl-md-0 pt-md-2 pl-sm-2">
				<!--- col-md-6 Starts --->
				@if(isset($countActiveProposals) && $countActiveProposals > 0)
					<h5><a href="{{ $site_url }}/selling_orders">{{ $lang['user_home']['view_sales'] ?? 'View Sales' }}</a></h5>
				@else
					<h5><a href="{{ $site_url }}/buying_orders">{{ $lang['user_home']['view_purchases'] ?? 'View Purchases' }}</a></h5>
				@endif
				<h5>
					<a href="{{ $site_url }}/settings?profile_settings">
						{{ $lang['user_home']['edit_profile'] ?? 'Edit Profile' }}
					</a>
				</h5>
				<h5 class="mb-0"><a href="{{ $site_url }}/settings">{{ $lang['menu']['settings'] ?? 'Settings' }}</a></h5>
			</div>
			<!--- col-md-6 Ends --->
		</div>
		<!--- row Ends --->
		<hr>
		<h5>
			<a href="{{ $site_url }}/customer_support">
				{{ $lang['user_home']['contact'] ?? 'Contact' }} {{ $site_name }}
			</a>
		</h5>
	</div><!-- card-body Ends -->
</div><!-- card rounded-0 mb-3 welcome-box Ends -->
<div class="rounded-0 carosel_sec">
	<h3 class="buy_head {{ $langDir == 'right' ? 'text-right' : '' }}">{{ $lang['sidebar']['buy_it_again'] ?? 'Buy It Again' }}</h3>
	@if(empty($buyAgainIds))
		<p class='text-muted'><i class='fa fa-frown-o'></i> {{ $lang['sidebar']['no_buy_it_again'] ?? 'No previous purchases.' }} </p>
	@else
		<div id="demo" class="carousel slide" data-ride="carousel">
			<!-- The slideshow -->
			<div class="carousel-inner " role="listbox">
				@php $i = 0; @endphp
				@foreach($buyAgainIds as $pid)
				@php
					$prp = DB::table('proposals')->where('proposal_id', $pid)->where('proposal_status', 'active')->first();
					if (!$prp) continue;
					$i++;
					$pImg = $ld ? $ld->getImageUrl2('proposals', 'proposal_img1', $prp->proposal_img1 ?? '') : '';
					$pSlr = DB::table('sellers')->where('seller_id', $prp->proposal_seller_id)->first();
					$pSlrName = $pSlr->seller_user_name ?? '';
					$pSlrImg = $ld ? $ld->getImageUrl2('sellers', 'seller_image', $pSlr->seller_image ?? '') : '';
					$pLvl = DB::table('seller_levels_meta')->where('level_id', $pSlr->seller_level ?? 0)->where('language_id', $siteLanguage ?? 1)->first();
					$pLvlName = $pLvl->title ?? '';
					$rStats = DB::selectOne("SELECT COUNT(*) as cnt, COALESCE(AVG(buyer_rating), 0) as avg_rating FROM buyer_reviews WHERE proposal_id = ?", [$pid]);
					$cReviews = $rStats ? (int)$rStats->cnt : 0;
					$avgRating = $rStats ? (float)$rStats->avg_rating : 0;
					$pPrice = $prp->proposal_price;
					if ($pPrice == 0) {
						$bp = DB::table('proposal_packages')->where('proposal_id', $pid)->where('package_name', 'Basic')->first();
						$pPrice = $bp->price ?? 0;
					}
					$currencySymbol = $s_currency ?? '$';
					$currencyPosition = $currency_position ?? 'left';
					$fmtPrice = ($currencyPosition === 'left') ? $currencySymbol . number_format((float)$pPrice, 2) : number_format((float)$pPrice, 2) . $currencySymbol;
				@endphp
						<div class="carousel-item {{ $i == 1 ? 'active' : '' }}">
							<div class="proposal-card-base mp-proposal-card">
								<!--- proposal-card-base mp-proposal-card Starts --->
								<a href="{{ $site_url }}/proposals/{{ $pSlrName }}/{{ $prp->proposal_url }}">
									<img src="{{ $pImg }}" class="img-fluid">
								</a>
								<div class="proposal-card-caption">
									<!--- proposal-card-caption Starts --->
									<div class="proposal-seller-info">
										<!--- gig-seller-info Starts --->
										<span class="fit-avatar s24">
											<img src="{{ $pSlrImg }}" class="rounded-circle" width="32" height="32">
										</span>
										<div class="seller-info-wrapper">
											<a href="{{ $site_url }}/{{ $pSlrName }}" class="seller-name">{{ $pSlrName }}</a>
											<div class="gig-seller-tooltip">
												{{ $pLvlName }}
											</div>
										</div>
									</div>
									<!--- gig-seller-info Ends --->
									<a href="{{ $site_url }}/proposals/{{ $pSlrName }}/{{ $prp->proposal_url }}" class="proposal-link-main">
										<h3>{{ $prp->proposal_title }}</h3>
									</a>
									<div class="rating-badges-container">
										<span class="proposal-rating">
											<svg class="fit-svg-icon full_star" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg" width="15" height="15">
												<path d="M1728 647q0 22-26 48l-363 354 86 500q1 7 1 20 0 21-10.5 35.5t-30.5 14.5q-19 0-40-12l-449-236-449 236q-22 12-40 12-21 0-31.5-14.5t-10.5-35.5q0-6 2-20l86-500-364-354q-25-27-25-48 0-37 56-46l502-73 225-455q19-41 49-41t49 41l225 455 502 73q56 9 56 46z"></path>
											</svg>
											<span>
												<strong>@if($prp->proposal_rating == '0') 0.0 @else {{ number_format($avgRating, 1) }} @endif</strong>
												({{ $cReviews }})
											</span>
										</span>
									</div>
								</div>
								<!--- proposal-card-caption Ends --->
								<footer class="proposal-card-footer">
									<!--- proposal-card-footer Starts --->
									<div class="proposal-fav">
									</div>
									<div class="proposal-price">
										<a class="js-proposal-card-imp-data">
											<small>Starting At</small>{!! $fmtPrice !!}
										</a>
									</div>
								</footer>
								<!--- proposal-card-footer Ends --->
							</div>
							<!--- proposal-card-base mp-proposal-card Ends --->
						</div>
				@endforeach
			</div><!-- Left and right controls -->
			<a class="carousel-control-prev" href="#demo" data-slide="prev">
				<i class="fa fa-angle-left"></i>
			</a>
			<a class="carousel-control-next" href="#demo" data-slide="next">
				<i class="fa fa-angle-right"></i>
			</a>
		</div>
	@endif
</div>
<div class="rounded-0 mb-3 carosel_sec mt-3">
	<h3 class="buy_head {{ $langDir == 'right' ? 'text-right' : '' }}">{{ $lang['sidebar']['recently_viewed'] ?? 'Recently Viewed' }}</h3>
	@if(empty($recentlyViewedIds))
		<p class='text-muted'> <i class='fa fa-frown-o'></i> {{ $lang['sidebar']['no_recently_viewed'] ?? 'No recently viewed proposals.' }} </p>
	@else
		<div id="demo2" class="carousel slide" data-ride="carousel">
			<!-- The slideshow -->
			<div class="carousel-inner " role="listbox">
				@php $i = 0; @endphp
				@foreach($recentlyViewedIds as $pid)
				@php
					$prp = DB::table('proposals')->where('proposal_id', $pid)->where('proposal_status', 'active')->first();
					if (!$prp) continue;
					$i++;
					$pImg = $ld ? $ld->getImageUrl2('proposals', 'proposal_img1', $prp->proposal_img1 ?? '') : '';
					$pSlr = DB::table('sellers')->where('seller_id', $prp->proposal_seller_id)->first();
					$pSlrName = $pSlr->seller_user_name ?? '';
					$pSlrImg = $ld ? $ld->getImageUrl2('sellers', 'seller_image', $pSlr->seller_image ?? '') : '';
					$pLvl = DB::table('seller_levels_meta')->where('level_id', $pSlr->seller_level ?? 0)->where('language_id', $siteLanguage ?? 1)->first();
					$pLvlName = $pLvl->title ?? '';
					$rStats = DB::selectOne("SELECT COUNT(*) as cnt, COALESCE(AVG(buyer_rating), 0) as avg_rating FROM buyer_reviews WHERE proposal_id = ?", [$pid]);
					$cReviews = $rStats ? (int)$rStats->cnt : 0;
					$avgRating = $rStats ? (float)$rStats->avg_rating : 0;
					$pPrice = $prp->proposal_price;
					if ($pPrice == 0) {
						$bp = DB::table('proposal_packages')->where('proposal_id', $pid)->where('package_name', 'Basic')->first();
						$pPrice = $bp->price ?? 0;
					}
					$currencySymbol = $s_currency ?? '$';
					$currencyPosition = $currency_position ?? 'left';
					$fmtPrice = ($currencyPosition === 'left') ? $currencySymbol . number_format((float)$pPrice, 2) : number_format((float)$pPrice, 2) . $currencySymbol;
				@endphp
						<div class="carousel-item {{ $i == 1 ? 'active' : '' }}">
							<!--- carousel-item Starts --->
							<div class="proposal-card-base mp-proposal-card">
								<!--- proposal-card-base mp-proposal-card Starts --->
								<a href="proposals/{{ $pSlrName }}/{{ $prp->proposal_url }}">
									<img src="{{ $pImg }}" class="img-fluid">
								</a>
								<div class="proposal-card-caption">
									<!--- proposal-card-caption Starts --->
									<div class="proposal-seller-info">
										<!--- gig-seller-info Starts --->
										<span class="fit-avatar s24">
											<img src="{{ $pSlrImg }}" class="rounded-circle" width="32" height="32">
										</span>
										<div class="seller-info-wrapper">
											<a href="{{ $site_url }}/{{ $pSlrName }}" class="seller-name">{{ $pSlrName }}</a>
											<div class="gig-seller-tooltip">
												{{ $pLvlName }}
											</div>
										</div>
									</div>
									<!--- gig-seller-info Ends --->
									<a href="{{ $site_url }}/proposals/{{ $pSlrName }}/{{ $prp->proposal_url }}" class="proposal-link-main">
										<h3>{{ $prp->proposal_title }}</h3>
									</a>
									<div class="rating-badges-container">
										<span class="proposal-rating">
											<svg class="fit-svg-icon full_star" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg" width="15" height="15">
												<path d="M1728 647q0 22-26 48l-363 354 86 500q1 7 1 20 0 21-10.5 35.5t-30.5 14.5q-19 0-40-12l-449-236-449 236q-22 12-40 12-21 0-31.5-14.5t-10.5-35.5q0-6 2-20l86-500-364-354q-25-27-25-48 0-37 56-46l502-73 225-455q19-41 49-41t49 41l225 455 502 73q56 9 56 46z"></path>
											</svg>
											<span>
												<strong>@if($prp->proposal_rating == '0') 0.0 @else {{ number_format($avgRating, 1) }} @endif</strong>
												({{ $cReviews }})
											</span>
										</span>
									</div>
								</div>
								<!--- proposal-card-caption Ends --->
								<footer class="proposal-card-footer">
									<!--- proposal-card-footer Starts --->

									<div class="proposal-fav">
									</div>

									<div class="proposal-price">
										<a class="js-proposal-card-imp-data">
											<small>Starting At</small>{!! $fmtPrice !!}
										</a>
									</div>
								</footer>
								<!--- proposal-card-footer Ends --->
							</div>
							<!--- proposal-card-base mp-proposal-card Ends --->
						</div>
						<!--- carousel-item Ends --->
				@endforeach

			</div>
			<!-- Left and right controls -->
			<a class="carousel-control-prev" href="#demo2" data-slide="prev">
				<i class="fa fa-angle-left"></i>
			</a>
			<a class="carousel-control-next" href="#demo2" data-slide="next">
				<i class="fa fa-angle-right"></i>
			</a>
		</div>
	@endif
</div>
<div class="card rounded-0 sticky-start mb-3 card_user">
	<div class="card-body">
		<img src="images/sales.png" class="img-fluid center-block" alt="none">
		<h4>{{ $lang['sidebar']['start_selling']['title'] ?? 'Start Selling' }}</h4>
		<p>{{ $lang['sidebar']['start_selling']['desc'] ?? 'Create your first gig and start earning.' }}</p>
		<button onclick="location.href='start_selling'" class="btn get_btn">{{ $lang['sidebar']['start_selling']['button'] ?? 'Get Started' }}</button>
	</div>
</div>
<br>
<script>
	$(document).ready(function() {
		// Sticky Code start //
		if ($(window).width() < 767) {
			//
		} else {
			$(".sticky-start").sticky({
				topSpacing: 20,
				zIndex: 500,
				bottomSpacing: 400,
			});
		}
		// Sticky code ends //
	});
</script>
