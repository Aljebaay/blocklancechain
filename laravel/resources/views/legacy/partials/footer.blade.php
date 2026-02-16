@php
    $ld = $legacyData ?? null;
@endphp
<!-- start footer -->
<footer class="footer">
	<div class="container">
		<div class="row">

			<div class="col-md-8">
			
				<div class="row">
					
					<div class="col-md-4 col-12">
						<h3 data-toggle="collapse" data-target="#collapsecategories">{{ $lang['categories'] ?? 'Categories' }}</h3>
						<ul class="collapse show list-unstyled" id="collapsecategories">
						@foreach($footer_categories ?? [] as $link)
						@php
                            $linkUrl = $ld ? $ld->dynamicUrl($link->link_url, true) : ($site_url . '/' . ltrim($link->link_url, '/'));
                        @endphp
						<li class="list-unstyled-item"><a href="{{ $linkUrl }}">{!! $link->link_title !!}</a></li>
						@endforeach
						</ul>
					</div>

					<div class="col-md-4 col-12">
						<h3 class="h3Border" data-toggle="collapse" data-target="#collapseabout">{{ $lang['about'] ?? 'About' }}</h3>
						<ul class="collapse show list-unstyled" id="collapseabout">
						@foreach($footer_about ?? [] as $link)
						@php
                            $linkUrl = $ld ? $ld->dynamicUrl($link->link_url, true) : ($site_url . '/' . ltrim($link->link_url, '/'));
                        @endphp
						<li class="list-unstyled-item"><a href="{{ $linkUrl }}"><i class="fa {{ $link->icon_class ?? '' }}"></i> {!! $link->link_title !!}</a></li>
						@endforeach
						</ul>
					</div>
					
					<div class="col-md-4 col-12">
						<h3 class="h3Border" data-toggle="collapse" data-target="#collapsecategories2">{{ $lang['pages'] ?? 'Pages' }}</h3>
						<ul class="collapse show list-unstyled" id="collapsecategories2">
						@foreach($footer_pages ?? [] as $page)
						<li class="list-unstyled-item"><a href="{{ $site_url }}/pages/{{ $page->url }}">{{ $page->title }}</a></li>
						@endforeach
						</ul>
					</div>

				</div>

			</div>

			<div class="col-md-4 col-12">
				<h3 class="h3Border" data-toggle="collapse" data-target="#collapsefindusOn">{{ $lang['find_us_on'] ?? 'Find Us On' }}</h3>
				<div class="collapse show" id="collapsefindusOn">
					<ul class="list-inline social_icon">
					@foreach($footer_follow ?? [] as $link)
					@php
                        $linkUrl = $ld ? $ld->dynamicUrl($link->link_url, false) : $link->link_url;
                    @endphp
					<li class="list-inline-item"><a href="{{ $linkUrl }}"><i class="fa {{ $link->icon_class ?? '' }}"></i></a></li>
					@endforeach
					</ul>
					<div class="form-group mt-0">

					@if(($language_switcher ?? 0) == 1)
						<select id="languageSelect" class="form-control">
							@foreach($all_languages ?? [] as $lng)
							@php
                                $lngImage = $ld ? $ld->getImageUrl('languages', $lng->image ?? '') : '';
                            @endphp
							<option data-image="{{ $lngImage }}" data-url="{{ $site_url }}/change_language?id={{ $lng->id }}" @if($lng->id == ($siteLanguage ?? 1)) selected @endif>
								{{ $lng->title }}
							</option>
							@endforeach
						</select>
					@endif

					@if(($enable_google_translate ?? 0) == 1)
						<div id="google_translate_element" class="mt-2"></div>
					@endif

					@if(($enable_converter ?? 0) == 1)
						<div class="mt-2"></div>
						<select id="currencySelect2" class="form-control mt-2">
							<option data-url="{{ $site_url }}/change_currency?id=0">
								{{ ($s_currency_name ?? '') . ' (' . ($s_currency ?? '$') . ')' }}
							</option>
							@foreach($site_currencies ?? [] as $sc)
							<option data-url="{{ $site_url }}/change_currency?id={{ $sc->id }}" @if($sc->id == session('siteCurrency')) selected @endif>
								{{ $sc->name }} ({{ $sc->symbol }})
							</option>
							@endforeach
						</select>
					@endif

					</div>
					
					@if(!empty($google_app_link) || !empty($apple_app_link))
					<h5>{{ $lang['mobile_apps'] ?? 'Mobile Apps' }}</h5>
					@endif

					@if(!empty($google_app_link))
					<a href="{{ $google_app_link }}" target="_blank">
						<img src="{{ $site_url }}/images/google.png" class="pic">
					</a>
					@endif
					
					@if(!empty($apple_app_link))
					<a href="{{ $apple_app_link }}" target="_blank">
						<img src="{{ $site_url }}/images/app.png" class="pic1">
					</a>
					@endif

				</div>
			</div>

		</div>
	</div>
	<br>
</footer>
<!-- end footer -->
<section class="post_footer">
{{ $site_copyright ?? '' }}
</section>

@if(!isset($_COOKIE['close_cookie']))
<section class="clearfix cookies_footer row animated slideInLeft">
	<div class="col-md-4">
		<img src="{{ $site_url }}/images/cookie.png" class="img-fluid" alt="">
	</div>
	<div class="col-md-8">
		<div class="float-right close btn btn-sm"><i class="fa fa-times"></i></div>
		<h4 class="mt-0 mt-lg-2 {{ ($lang_dir ?? 'left') == 'right' ? 'text-right' : '' }}">{{ $lang['cookie_box']['title'] ?? 'Cookies' }}</h4>
		<p class="mb-1">{!! str_replace('{link}', $site_url . '/terms_and_conditions', $lang['cookie_box']['desc'] ?? '') !!}</p>
		<a href="#" class="btn btn-success btn-sm">{{ $lang['cookie_box']['button'] ?? 'Accept' }}</a>
	</div>
</section>
@endif
<section class="messagePopup animated slideInRight"></section>

<link rel="stylesheet" href="{{ $site_url }}/styles/msdropdown.css"/>

{{-- Footer JS --}}
<div id="wait"></div>

@if(!empty($google_analytics))
<script async src="https://www.googletagmanager.com/gtag/js?id={{ $google_analytics }}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '{{ $google_analytics }}');
</script>
@endif
<script src="{{ $site_url }}/js/msdropdown.js" defer></script>
<script type="text/javascript" src="{{ $site_url }}/js/jquery.sticky.js" defer></script>

@php
    $isLoggedIn = session()->has('seller_user_name');
    $loginSellerId = $seller_id ?? '';
    $loginSellerEnableSound = '';
    $loginSellerEnableNotifications = '0';
    if ($isLoggedIn) {
        $loginSeller = \Illuminate\Support\Facades\DB::table('sellers')
            ->where('seller_user_name', session('seller_user_name'))
            ->first();
        if ($loginSeller) {
            $loginSellerId = $loginSeller->seller_id;
            $loginSellerEnableSound = $loginSeller->enable_sound ?? '';
            $loginSellerEnableNotifications = $loginSeller->enable_notifications ?? '0';
        }
    }
@endphp

<script
type="text/javascript"
id="custom-js"
src="{{ $site_url }}/js/customjs.js"
defer
data-logged-id="{{ $isLoggedIn ? $loginSellerId : '' }}"
data-base-url="{{ $site_url }}"
data-enable-sound="{{ $isLoggedIn ? $loginSellerEnableSound : '' }}"
data-enable-notifications="{{ $isLoggedIn ? $loginSellerEnableNotifications : '0' }}"
data-disable-messages="{{ $disable_messages ?? '0' }}"
>
</script>

@if(($enable_google_translate ?? 0) == 1)
<script>
function googleTranslateElementInit() {
  new google.translate.TranslateElement({
    pageLanguage: 'en',
    layout: google.translate.TranslateElement.InlineLayout.SIMPLE
  },'google_translate_element');
}
</script>
<script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
@endif

<script type="text/javascript" src="{{ $site_url }}/js/categoriesProposal.js" defer></script>
<script type="text/javascript" src="{{ $site_url }}/js/popper.min.js" defer></script>
<script type="text/javascript" src="{{ $site_url }}/js/owl.carousel.min.js" defer></script>
<script type="text/javascript" src="{{ $site_url }}/js/bootstrap.js" defer></script>
<script type="text/javascript" src="{{ $site_url }}/js/summernote.js" defer></script>
