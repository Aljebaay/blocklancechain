@php
    use Illuminate\Support\Facades\DB;
    $langDir = $lang_dir ?? 'left';
    $sLang = $siteLanguage ?? 1;
    $isLoggedIn = session()->has('seller_user_name');
    $orderClause = $langDir === 'right' ? 'order by 1 DESC' : '';

    $next_icon = "<div class='flag-img flag-img-right'><span class='gig-zone-icon float-right'><svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' aria-hidden='true' focusable='false'><path d='M10,17a1,1,0,0,1-.707-1.707L12.586,12,9.293,8.707a1,1,0,0,1,1.414-1.414L15.414,12l-4.707,4.707A1,1,0,0,1,10,17Z'></path></svg></span></div>";
@endphp
<div class="cat-mobile" id="gig-zone-modal-container" style="display:none;"><!--- cat-mobile Starts --->
  <div data-overlay-mask="" class="overlay-mask mobile-catnav-overlay-mask" data-aria-hidden="true"></div>
  <div data-overlay-content-wrapper="" class="overlay-mask overlay-content-wrapper mobile-catnav-overlay-mask">
    <div class="mobile-catnav-wrapper overlay-region position-relative p-xs-0" id="mobile-catnav-overlay" aria-hidden="false" data-overlay-has-trigger="true" style="top: 0px;">
      <div data-ui="mobile-cat-nav" class="mobile-cat-nav bg-gray-lighter pb-xs-4 width-full position-fixed animated" style="height: 100vh;">

        <div class="bg-white display-flex-md show-md pt-md-3 pl-md-2 pb-md-3">
          <div class="flex-md-5 pl-md-0">
            <a role="button" href="{{ $site_url }}">
            <img src="{{ $site_logo_image ?? '' }}" width="158">
            </a>
          </div>
          <div class="flex-md-1 pr-md-2">
            <button class="btn-link text-right overlay-close flex-xs-1 justify-self-flex-end border-0 p-md-0" data-overlay-close="">
              <span class="screen-reader-only">Close Menu</span>
              <span class="gig-zone-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                  <path d="M13.414,12l6.293-6.293a1,1,0,0,0-1.414-1.414L12,10.586,5.707,4.293A1,1,0,0,0,4.293,5.707L10.586,12,4.293,18.293a1,1,0,1,0,1.414,1.414L12,13.414l6.293,6.293a1,1,0,0,0,1.414-1.414Z"></path>
                </svg>
              </span>
            </button>
          </div>
        </div>

        <div data-ui="mobile-catnav-header" class="mobile-catnav-header bb-xs-1 align-items-center bg-white display-flex-xs flex-nowrap position-relative height-50px">
          <button class="mobile-catnav-back-btn btn-link overlay-back p-xs-2 text-left display-none flex-xs-1 align-self-flex-start z-index-1 position-absolute" data-subnav-id="0" data-ternav-id="0" data-topnav-name="" data-subnav-name="">
            <span class="screen-reader-only">Go Back</span>
            <span class="gig-zone-icon">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path d="M16,21a0.994,0.994,0,0,1-.664-0.253L5.5,12l9.841-8.747a1,1,0,0,1,1.328,1.494L8.5,12l8.159,7.253A1,1,0,0,1,16,21Z"></path>
              </svg>
            </span>
          </button>
          <div class="flex-xs-4 width-full pt-md-4 pb-md-4 pl-xs-2">
            <h6 id="mobile-catnav-header-title" class="text-left position-absolute vertical-center"> Browse Categories</h6>
            <h6 id="mobile-sub-catnav-header-title" class="text-center position-absolute position-left position-right vertical-center pl-md-8 pr-md-8"></h6>
            <h6 id="mobile-tertiary-catnav-header-title" class="text-center position-absolute position-left position-right vertical-center pl-md-8 pr-md-8 display-none"></h6>
          </div>
          <div class="flex-xs-1 width-full">
            <button class="show-xs show-sm btn-link p-xs-2 overlay-close border-0 float-right" data-overlay-close="">
              <span class="screen-reader-only">Close Menu</span>
              <span class="gig-zone-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                  <path d="M13.414,12l6.293-6.293a1,1,0,0,0-1.414-1.414L12,10.586,5.707,4.293A1,1,0,0,0,4.293,5.707L10.586,12,4.293,18.293a1,1,0,1,0,1.414,1.414L12,13.414l6.293,6.293a1,1,0,0,0,1.414-1.414Z"></path>
                </svg>
              </span>
            </button>
          </div>
        </div>

        <div data-ui="mobile-catnav-scroll-wrapper" class="height-full overflow-y-scroll">

          <div class="mobile-topnav bg-white animated">
            <ul data-ui="mobile-top-catnav-container" class="mobile-top-catnav-container list-unstyled mobile-catnav-margin">
              @php
                $mobileCats = DB::select("select * from categories where cat_featured='yes' {$orderClause} LIMIT 0,10");
              @endphp
              @foreach($mobileCats as $mCat)
              @php
                $mMeta = DB::table('cats_meta')->where('cat_id', $mCat->cat_id)->where('language_id', $sLang)->first();
                $mTitle = $mMeta->cat_title ?? '';
                $mChildCount = DB::table('categories_children')->where('child_parent_id', $mCat->cat_id)->count();
              @endphp
              <li class="p-xs-2 bb-xs-1 text-body-larger top-nav-item" data-uid="1{{ $mCat->cat_id }}" data-name="{{ $mTitle }}">
                @if($mChildCount == 0)<a href="{{ $site_url }}/category?cat_id={{ $mCat->cat_id }}">@endif
                <div class="flag">
                  <div class="flag-body">{{ $mTitle }}</div>
                  @if($mChildCount > 0)
                  {!! $next_icon !!}
                  @endif
                </div>
                @if($mChildCount == 0)</a>@endif
              </li>
              @endforeach
            </ul>
          </div>

          @php
            $mobileCats2 = DB::select("select * from categories where cat_featured='yes' {$orderClause} LIMIT 0,10");
          @endphp
          @foreach($mobileCats2 as $mCat2)
          @php
            $mMeta2 = DB::table('cats_meta')->where('cat_id', $mCat2->cat_id)->where('language_id', $sLang)->first();
            $mTitle2 = $mMeta2->cat_title ?? '';
          @endphp
          <div class="mobile-subnav bg-white animated" id="mobile-sub-catnav-content-1{{ $mCat2->cat_id }}">
            <ul class="mobile-sub-catnav-container list-unstyled mobile-catnav-margin display-none">
              <li class="p-xs-1 bb-xs-1 text-body-larger strong subnav-item a11y-focus-only">
                <a class="p-xs-1 text-gray display-inline-block width-full text-underline" href="{{ $site_url }}/categories/category.php?cat_url={{ rawurlencode($mCat2->cat_url) }}">
                View All {{ $mTitle2 }}
                </a>
              </li>
              @php
                $mChildren = DB::select("select * from categories_children where child_parent_id='{$mCat2->cat_id}'");
              @endphp
              @foreach($mChildren as $mChild)
              @php
                $mcMeta = DB::table('child_cats_meta')->where('child_id', $mChild->child_id)->where('language_id', $sLang)->first();
                $mcTitle = $mcMeta->child_title ?? '';
              @endphp
              <li class="p-xs-2 bb-xs-1 text-body-larger subnav-item a11y-focus-only">
                <a href="{{ $site_url }}/categories/category.php?cat_url={{ rawurlencode($mCat2->cat_url) }}&cat_child_url={{ rawurlencode($mChild->child_url) }}">
                  <div class="flag">
                    <div class="flag-body">{{ $mcTitle }}</div>
                  </div>
                </a>
              </li>
              @endforeach
            </ul>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>
<!--- cat-mobile Ends --->
@if($isLoggedIn)
<div class="user-mobile" id="gig-zone-modal-container" style="display: none;">
  <!--- user-mobile Starts --->
  <div data-overlay-mask="" class="overlay-mask mobile-catnav-overlay-mask" data-aria-hidden="true"></div>
  <div data-overlay-content-wrapper="" class="overlay-mask overlay-content-wrapper mobile-catnav-overlay-mask">
    <div class="mobile-catnav-wrapper overlay-region position-relative p-xs-0" id="mobile-catnav-overlay" aria-hidden="false" data-overlay-has-trigger="true" style="top: 0px;">
      <div data-ui="mobile-cat-nav" class="mobile-cat-nav bg-gray-lighter pb-xs-4 width-full position-fixed animated" style="height: 100vh;">
        <div class="bg-white display-flex-md show-md pt-md-3 pl-md-2 pb-md-3">
          <div class="flex-md-5 pl-md-0">
            <a role="button" href="/?ref=catnav-logo">
            <img src="{{ $site_logo_image ?? '' }}" width="158">
            </a>
          </div>
          <div class="flex-md-1 pr-md-2">
            <button class="btn-link text-right overlay-close flex-xs-1 justify-self-flex-end border-0 p-md-0" data-overlay-close="">
              <span class="screen-reader-only">Close Menu</span>
              <span class="gig-zone-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                  <path d="M13.414,12l6.293-6.293a1,1,0,0,0-1.414-1.414L12,10.586,5.707,4.293A1,1,0,0,0,4.293,5.707L10.586,12,4.293,18.293a1,1,0,1,0,1.414,1.414L12,13.414l6.293,6.293a1,1,0,0,0,1.414-1.414Z"></path>
                </svg>
              </span>
            </button>
          </div>
        </div>
        <div data-ui="mobile-catnav-header" class="mobile-catnav-header bb-xs-1 align-items-center bg-white display-flex-xs flex-nowrap position-relative height-50px">
          <button class="mobile-catnav-back-btn btn-link overlay-back p-xs-2 text-left display-none flex-xs-1 align-self-flex-start z-index-1 position-absolute" data-subnav-id="0" data-ternav-id="0" data-topnav-name="" data-subnav-name="">
            <span class="gig-zone-icon">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" focusable="false">
                <path d="M16,21a0.994,0.994,0,0,1-.664-0.253L5.5,12l9.841-8.747a1,1,0,0,1,1.328,1.494L8.5,12l8.159,7.253A1,1,0,0,1,16,21Z"></path>
              </svg>
            </span>
          </button>
          <div class="flex-xs-4 width-full pt-md-4 pb-md-4 pl-xs-2">
            <h6 id="mobile-catnav-header-title" class="text-left position-absolute vertical-center">User Menu</h6>
            <h6 id="mobile-sub-catnav-header-title" class="text-center position-absolute position-left position-right vertical-center pl-md-8 pr-md-8"></h6>
            <h6 id="mobile-tertiary-catnav-header-title" class="text-center position-absolute position-left position-right vertical-center pl-md-8 pr-md-8 display-none"></h6>
          </div>
          <div class="flex-xs-1 width-full">
            <button class="show-xs show-sm btn-link p-xs-2 overlay-close border-0 float-right" data-overlay-close="">
              <span class="screen-reader-only">Close Menu</span>
              <span class="gig-zone-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                  <path d="M13.414,12l6.293-6.293a1,1,0,0,0-1.414-1.414L12,10.586,5.707,4.293A1,1,0,0,0,4.293,5.707L10.586,12,4.293,18.293a1,1,0,1,0,1.414,1.414L12,13.414l6.293,6.293a1,1,0,0,0,1.414-1.414Z"></path>
                </svg>
              </span>
            </button>
          </div>
        </div>
        <div data-ui="mobile-catnav-scroll-wrapper" class="height-full overflow-y-scroll">
          <div class="mobile-topnav bg-white animated">
            <ul class="mobile-top-catnav-container list-unstyled mobile-catnav-margin">
              <li class="p-xs-2 bb-xs-1 text-body-larger top-nav-item a11y-focus-only" data-uid="d-1" data-name="{{ $lang['menu']['dashboard'] ?? 'Dashboard' }}">
                <a href="#">
                  <div class="flag">
                    <div class="flag-body">{{ $lang['menu']['dashboard'] ?? 'Dashboard' }}</div>
                    {!! $next_icon !!}
                  </div>
                </a>
              </li>
              <li class="p-xs-2 bb-xs-1 text-body-larger top-nav-item a11y-focus-only">
                <a href="{{ $site_url }}/notifications">
                  <div class="flag">
                    <div class="flag-body">Notifications</div>
                  </div>
                </a>
              </li>
              <li class="p-xs-2 bb-xs-1 text-body-larger top-nav-item a11y-focus-only">
                <a href="{{ $site_url }}/conversations/inbox">
                  <div class="flag">
                    <div class="flag-body">Messages</div>
                  </div>
                </a>
              </li>
              <li class="p-xs-2 bb-xs-1 text-body-larger top-nav-item a11y-focus-only">
                <a href="{{ $site_url }}/favorites">
                  <div class="flag">
                    <div class="flag-body">Favorites</div>
                  </div>
                </a>
              </li>
              <li class="p-xs-2 bb-xs-1 text-body-larger top-nav-item a11y-focus-only">
                <a href="{{ $site_url }}/cart">
                  <div class="flag">
                    <div class="flag-body">Cart</div>
                  </div>
                </a>
              </li>
              <li class="p-xs-2 bb-xs-1 text-body-larger top-nav-item a11y-focus-only">
                <a href="{{ $site_url }}/logout">
                  <div class="flag">
                    <div class="flag-body">{{ $lang['menu']['logout'] ?? 'Logout' }}</div>
                  </div>
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!--- user-mobile Ends --->
@endif
