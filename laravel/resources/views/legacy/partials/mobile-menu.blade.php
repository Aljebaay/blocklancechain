@php
    use Illuminate\Support\Facades\DB;
    $langDir = $lang_dir ?? 'left';
    $sLang = $siteLanguage ?? 1;
    $isLoggedIn = session()->has('seller_user_name');
    $orderClause = $langDir === 'right' ? 'order by 1 DESC' : '';

    $next_icon = "<div class='flag-img flag-img-right'><span class='gig-zone-icon float-right'><svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' aria-hidden='true' focusable='false'><path d='M10,17a1,1,0,0,1-.707-1.707L12.586,12,9.293,8.707a1,1,0,0,1,1.414-1.414L15.414,12l-4.707,4.707A1,1,0,0,1,10,17Z'></path></svg></span></div>";

    if ($isLoggedIn) {
        $seller_id_val = $seller_id ?? 0;
        $count_unread_notifications = DB::table('notifications')->where('receiver_id', $seller_id_val)->where('status', 'unread')->count();
        $count_favourites = DB::table('favorites')->where('seller_id', $seller_id_val)->count();
        $count_unread_inbox_messages = DB::table('inbox_messages')->where('message_receiver', $seller_id_val)->where('message_status', 'unread')->count();
        $count_cart_val = $count_cart ?? 0;
    }
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
                <a class="p-xs-1 text-gray display-inline-block width-full text-underline" href="{{ $site_url }}/categories/{{ rawurlencode($mCat2->cat_url) }}">
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
                <a href="{{ $site_url }}/categories/{{ rawurlencode($mCat2->cat_url) }}/{{ rawurlencode($mChild->child_url) }}">
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
                    <span class="gig-zone-icon icon-smaller mr-2">
                    <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve"><g transform="translate(1 1)"><g><g><path stroke="black" stroke-width="10" d="M336.067,208.92l-40.96,24.747c-14.507,8.533-29.867,16.213-44.373,23.04c-4.267,1.707-9.387,4.267-14.507,6.827 c-17.92,9.387-27.307,29.013-23.04,48.64c3.413,15.36,16.213,28.16,31.573,31.573c3.413,0.853,6.827,0.853,10.24,0.853 c16.213,0,30.72-9.387,38.4-23.893c1.707-4.267,4.267-9.387,6.827-14.507c7.68-14.507,14.507-29.867,23.04-44.373l24.747-40.96 c1.707-3.413,0.853-6.827-1.707-10.24C343.747,207.213,339.48,207.213,336.067,208.92z M308.76,252.44 c-9.387,15.36-17.067,30.72-23.893,46.08c-1.707,5.12-4.267,9.387-6.827,14.507c-5.12,11.093-17.067,16.213-29.013,13.653 c-9.387-1.707-17.067-9.387-18.773-18.773c-2.56-11.947,2.56-23.893,13.653-29.013c4.267-2.56,9.387-4.267,14.507-6.827 c15.36-7.68,31.573-15.36,46.08-23.893l11.093-6.827L308.76,252.44z"/><path stroke="black" stroke-width="13" d="M492.719,207.208c-0.108-0.575-0.27-1.146-0.492-1.702c-0.223-0.558-0.507-1.069-0.837-1.537 c-12.787-30.616-31.365-58.265-54.348-81.565c-0.324-0.482-0.701-0.944-1.135-1.378s-0.896-0.811-1.378-1.135 C388.233,74.226,324.764,45.933,255,45.933c-33.418,0-65.389,6.5-94.725,18.281c-0.575,0.108-1.146,0.27-1.701,0.492 c-0.557,0.223-1.069,0.507-1.537,0.837c-30.616,12.787-58.265,31.365-81.565,54.348c-0.482,0.324-0.944,0.701-1.378,1.135 s-0.812,0.896-1.135,1.378C27.293,168.7-1,232.169-1,301.933c0,58.027,18.773,113.493,55.467,158.72 c1.707,2.56,4.267,3.413,6.827,3.413h387.413c2.56,0,5.12-0.853,6.827-3.413C492.227,415.427,511,359.96,511,301.933 C511,268.515,504.5,236.544,492.719,207.208z M445.293,447H65.56c-11.539-14.88-21.122-30.844-28.695-47.616l23.575-9.557 c4.267-1.707,5.973-6.827,4.267-11.093c-1.707-4.267-6.827-5.973-11.093-4.267l-23.11,9.369 c-8.574-23.342-13.394-48.021-14.278-73.369H75.8c5.12,0,8.533-3.413,8.533-8.533S80.92,293.4,75.8,293.4H16.237 c0.957-26.907,6.416-52.715,15.648-76.694L55.32,226.84c0.853,0.853,2.56,0.853,3.413,0.853c3.413,0,5.973-1.707,7.68-5.12 c1.707-4.267,0-9.387-4.267-11.093l-23.672-10.237c10.699-22.831,24.906-43.721,41.922-61.967l17.59,17.59 c1.707,1.707,3.413,2.56,5.973,2.56s4.267-0.853,5.973-2.56c3.413-3.413,3.413-8.533,0-11.947l-17.59-17.59 c19.117-17.828,41.131-32.581,65.244-43.438l9.52,23.482c0.853,3.413,4.267,5.12,7.68,5.12c0.853,0,1.707,0,3.413-0.853 c4.267-1.707,5.973-6.827,4.267-11.093l-9.329-23.012c23.021-8.463,47.679-13.452,73.329-14.364v59.563 c0,5.12,3.413,8.533,8.533,8.533s8.533-3.413,8.533-8.533V63.17c26.907,0.957,52.715,6.416,76.694,15.648l-10.134,23.436 c-1.707,4.267,0,9.387,4.267,11.093c0.853,0.853,2.56,0.853,3.413,0.853c3.413,0,6.827-2.56,7.68-5.12l10.237-23.672 c22.831,10.699,43.721,24.906,61.967,41.922l-17.59,17.59c-3.413,3.413-3.413,8.533,0,11.947c1.707,1.707,3.413,2.56,5.973,2.56 c1.707,0,4.267-0.853,5.973-2.56l17.59-17.59c17.828,19.117,32.581,41.131,43.438,65.244l-23.482,9.52 c-4.267,1.707-5.973,6.827-4.267,11.093c0.853,3.413,4.267,5.12,7.68,5.12c0.853,0,1.707-0.853,3.413-0.853l23.013-9.329 c8.463,23.021,13.452,47.679,14.364,73.329H434.2c-5.12,0-8.533,3.413-8.533,8.533s3.413,8.533,8.533,8.533h59.575 c-0.927,26.593-6.172,52.45-15.418,76.799l-23.677-10.239c-4.267-1.707-9.387,0-11.093,4.267c-1.707,4.267,0,9.387,4.267,11.093 l23.981,10.37C464.644,418.298,455.773,433.121,445.293,447z"/></g></g></g></svg>
                    </span>
                    <div class="flag-body">
                      {{ $lang['menu']['dashboard'] ?? 'Dashboard' }}
                    </div>
                    {!! $next_icon !!}
                  </div>
                </a>
              </li>
              <li class="p-xs-2 bb-xs-1 text-body-larger top-nav-item a11y-focus-only">
                <a href="{{ $site_url }}/notifications">
                  <div class="flag">
                    <span class="gig-zone-icon icon-smaller mr-2">
                    <svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 229.238 229.238" style="enable-background:new 0 0 229.238 229.238;" xml:space="preserve"><path d="M220.228,172.242c-20.606-17.82-39.675-42.962-39.675-105.734c0-36.353-29.576-65.928-65.93-65.928 c-36.359,0-65.939,29.575-65.939,65.928c0,62.829-19.062,87.946-39.686,105.751C3.28,177.239,0,184.411,0,191.937 c0,4.142,3.358,7.5,7.5,7.5h71.175c3.472,16.663,18.268,29.222,35.944,29.222s32.473-12.558,35.944-29.222h71.175 c4.142,0,7.5-3.358,7.5-7.5C229.238,184.35,225.95,177.167,220.228,172.242z M114.619,213.659c-9.34,0-17.321-5.929-20.381-14.222 H135C131.94,207.73,123.959,213.659,114.619,213.659z M17.954,184.437c0.273-0.296,0.564-0.578,0.871-0.845 c31.443-27.146,44.858-62.162,44.858-117.084c0-28.082,22.852-50.928,50.939-50.928c28.083,0,50.93,22.846,50.93,50.928 c0,54.872,13.417,89.887,44.876,117.091c0.307,0.265,0.598,0.544,0.872,0.838H17.954z"/></svg>
                    </span>
                    <div class="flag-body">
                      Notifications
                      @if($count_unread_notifications > 0)
                      <span class="badge badge-pill badge-danger"> {{ $count_unread_notifications }} New</span>
                      @endif
                    </div>
                  </div>
                </a>
              </li>
              <li class="p-xs-2 bb-xs-1 text-body-larger top-nav-item a11y-focus-only">
                <a href="{{ $site_url }}/conversations/inbox">
                  <div class="flag">
                    <span class="gig-zone-icon icon-smaller mr-2">
                    <svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve"><g><g><path d="M467,61H45C20.218,61,0,81.196,0,106v300c0,24.72,20.128,45,45,45h422c24.72,0,45-20.128,45-45V106 C512,81.28,491.872,61,467,61z M460.786,91L256.954,294.833L51.359,91H460.786z M30,399.788V112.069l144.479,143.24L30,399.788z M51.213,421l144.57-144.57l50.657,50.222c5.864,5.814,15.327,5.795,21.167-0.046L317,277.213L460.787,421H51.213z M482,399.787 L338.213,256L482,112.212V399.787z"/></g></g></svg>
                    </span>
                    <div class="flag-body">
                      Messages
                      @if($count_unread_inbox_messages > 0)
                      <span class="badge badge-pill badge-danger"> {{ $count_unread_inbox_messages }} New</span>
                      @endif
                    </div>
                  </div>
                </a>
              </li>
              <li class="p-xs-2 bb-xs-1 text-body-larger top-nav-item a11y-focus-only">
                <a href="{{ $site_url }}/favorites">
                  <div class="flag">
                    <span class="gig-zone-icon icon-smaller mr-2">
                    <svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve"><g><g><path d="M474.644,74.27C449.391,45.616,414.358,29.836,376,29.836c-53.948,0-88.103,32.22-107.255,59.25 c-4.969,7.014-9.196,14.047-12.745,20.665c-3.549-6.618-7.775-13.651-12.745-20.665c-19.152-27.03-53.307-59.25-107.255-59.25 c-38.358,0-73.391,15.781-98.645,44.435C13.267,101.605,0,138.213,0,177.351c0,42.603,16.633,82.228,52.345,124.7 c31.917,37.96,77.834,77.088,131.005,122.397c19.813,16.884,40.302,34.344,62.115,53.429l0.655,0.574 c2.828,2.476,6.354,3.713,9.88,3.713s7.052-1.238,9.88-3.713l0.655-0.574c21.813-19.085,42.302-36.544,62.118-53.431 c53.168-45.306,99.085-84.434,131.002-122.395C495.367,259.578,512,219.954,512,177.351 C512,138.213,498.733,101.605,474.644,74.27z M309.193,401.614c-17.08,14.554-34.658,29.533-53.193,45.646 c-18.534-16.111-36.113-31.091-53.196-45.648C98.745,312.939,30,254.358,30,177.351c0-31.83,10.605-61.394,29.862-83.245 C79.34,72.007,106.379,59.836,136,59.836c41.129,0,67.716,25.338,82.776,46.594c13.509,19.064,20.558,38.282,22.962,45.659 c2.011,6.175,7.768,10.354,14.262,10.354c6.494,0,12.251-4.179,14.262-10.354c2.404-7.377,9.453-26.595,22.962-45.66 c15.06-21.255,41.647-46.593,82.776-46.593c29.621,0,56.66,12.171,76.137,34.27C471.395,115.957,482,145.521,482,177.351 C482,254.358,413.255,312.939,309.193,401.614z"/></g></g></svg>
                    </span>
                    <div class="flag-body">
                      Favorites
                      @if($count_favourites > 0)
                      <span class="badge badge-pill badge-danger"> {{ $count_favourites }}</span>
                      @endif
                    </div>
                  </div>
                </a>
              </li>
              <li class="p-xs-2 bb-xs-1 text-body-larger top-nav-item a11y-focus-only">
                <a href="{{ $site_url }}/cart">
                  <div class="flag">
                    <span class="gig-zone-icon icon-smaller mr-2">
                    <svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 33 33" style="enable-background:new 0 0 33 33;" xml:space="preserve"><g><g><path stroke="black" stroke-width="0.8" d="M27.049,30.134H5.951c-0.99,0-1.81-0.743-1.908-1.728L2.659,14.537c-0.014-0.141,0.032-0.281,0.127-0.385 c0.095-0.105,0.229-0.165,0.371-0.165h26.688c0.141,0,0.276,0.06,0.371,0.165c0.095,0.104,0.141,0.245,0.127,0.385l-1.383,13.869 C28.86,29.391,28.039,30.134,27.049,30.134z M3.708,14.988l1.329,13.319c0.047,0.472,0.439,0.827,0.913,0.827h21.099 c0.474,0,0.867-0.355,0.914-0.827l1.329-13.319H3.708z"/><g><g><path stroke="black" stroke-width="1" d="M7.604,9.106c-0.143,0-0.285-0.061-0.383-0.179C7.042,8.716,7.07,8.4,7.282,8.223l6.246-5.24 c0.213-0.177,0.528-0.15,0.704,0.062c0.178,0.211,0.15,0.527-0.062,0.704l-6.246,5.24C7.831,9.067,7.717,9.106,7.604,9.106z"/></g><g><path stroke="black" stroke-width="1" d="M25.397,9.106c-0.113,0-0.227-0.039-0.321-0.117l-6.247-5.24c-0.211-0.178-0.239-0.493-0.062-0.705 c0.179-0.211,0.493-0.239,0.705-0.062l6.247,5.24c0.211,0.178,0.239,0.493,0.062,0.705C25.682,9.045,25.54,9.106,25.397,9.106z"/></g></g><path stroke="black" stroke-width="0.8" d="M31.275,14.988H1.725C0.774,14.988,0,14.214,0,13.263v-3.198c0-0.951,0.774-1.725,1.725-1.725h29.55 c0.951,0,1.725,0.774,1.725,1.725v3.198C33,14.214,32.227,14.988,31.275,14.988z M1.725,9.339C1.325,9.339,1,9.664,1,10.064v3.198 c0,0.4,0.325,0.725,0.725,0.725h29.55c0.399,0,0.725-0.325,0.725-0.725v-3.198c0-0.4-0.325-0.725-0.725-0.725H1.725z"/></g><g><path d="M9.255,26.048c-0.276,0-0.5-0.224-0.5-0.5v-7.25c0-0.276,0.224-0.5,0.5-0.5s0.5,0.224,0.5,0.5v7.25 C9.755,25.825,9.532,26.048,9.255,26.048z"/><path d="M14.085,26.048c-0.276,0-0.5-0.224-0.5-0.5v-7.25c0-0.276,0.224-0.5,0.5-0.5s0.5,0.224,0.5,0.5v7.25 C14.585,25.825,14.362,26.048,14.085,26.048z"/><path d="M18.915,26.048c-0.276,0-0.5-0.224-0.5-0.5v-7.25c0-0.276,0.224-0.5,0.5-0.5s0.5,0.224,0.5,0.5v7.25 C19.415,25.825,19.191,26.048,18.915,26.048z"/><path d="M23.745,26.048c-0.276,0-0.5-0.224-0.5-0.5v-7.25c0-0.276,0.224-0.5,0.5-0.5s0.5,0.224,0.5,0.5v7.25 C24.245,25.825,24.021,26.048,23.745,26.048z"/></g></g></svg>
                    </span>
                    <div class="flag-body">
                      Cart
                      @if($count_cart_val > 0)
                        <span class="badge badge-pill badge-danger"> {{ $count_cart_val }}</span>
                      @endif
                    </div>
                  </div>
                </a>
              </li>
              <li class="p-xs-2 bb-xs-1 text-body-larger top-nav-item a11y-focus-only">
                <a href="{{ $site_url }}/logout">
                  <div class="flag">
                    <span class="gig-zone-icon icon-smaller mr-2">
                    <svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 490.3 490.3" style="enable-background:new 0 0 490.3 490.3;" xml:space="preserve"><g><g><path stroke="black" stroke-width="10" d="M0,121.05v248.2c0,34.2,27.9,62.1,62.1,62.1h200.6c34.2,0,62.1-27.9,62.1-62.1v-40.2c0-6.8-5.5-12.3-12.3-12.3 s-12.3,5.5-12.3,12.3v40.2c0,20.7-16.9,37.6-37.6,37.6H62.1c-20.7,0-37.6-16.9-37.6-37.6v-248.2c0-20.7,16.9-37.6,37.6-37.6h200.6 c20.7,0,37.6,16.9,37.6,37.6v40.2c0,6.8,5.5,12.3,12.3,12.3s12.3-5.5,12.3-12.3v-40.2c0-34.2-27.9-62.1-62.1-62.1H62.1 C27.9,58.95,0,86.75,0,121.05z"/><path stroke="black" stroke-width="10" d="M385.4,337.65c2.4,2.4,5.5,3.6,8.7,3.6s6.3-1.2,8.7-3.6l83.9-83.9c4.8-4.8,4.8-12.5,0-17.3l-83.9-83.9 c-4.8-4.8-12.5-4.8-17.3,0s-4.8,12.5,0,17.3l63,63H218.6c-6.8,0-12.3,5.5-12.3,12.3c0,6.8,5.5,12.3,12.3,12.3h229.8l-63,63 C380.6,325.15,380.6,332.95,385.4,337.65z"/></g></g></svg>
                    </span>
                    <div class="flag-body">
                      {{ $lang['menu']['logout'] ?? 'Logout' }}
                    </div>
                  </div>
                </a>
              </li>
            </ul>
          </div>
          {{-- Dashboard submenu --}}
          <div class="mobile-subnav bg-white animated" id="mobile-sub-catnav-content-d-1">
            <!--- mobile-subnav bg-white animated Starts --->
            <ul class="mobile-sub-catnav-container list-unstyled mobile-catnav-margin">
              <li class="p-xs-1 bb-xs-1 text-body-larger strong subnav-item a11y-focus-only">
                <a class="p-xs-1 display-inline-block text-underline" href="{{ $site_url }}/dashboard">
                View Dashboard Home
                </a>
              </li>
              <li class="p-xs-2 bb-xs-1 text-body-larger subnav-item" data-uid="s-1" data-name="{{ $lang['menu']['selling'] ?? 'Selling' }}">
                <a href="#">
                  <div class="flag">
                    <div class="flag-body">{{ $lang['menu']['selling'] ?? 'Selling' }}</div>
                    {!! $next_icon !!}
                  </div>
                </a>
              </li>
              <li class="p-xs-2 bb-xs-1 text-body-larger subnav-item" data-uid="b" data-name="{{ $lang['menu']['buying'] ?? 'Buying' }}">
                <a href="#">
                  <div class="flag">
                    <div class="flag-body">{{ $lang['menu']['buying'] ?? 'Buying' }}</div>
                    {!! $next_icon !!}
                  </div>
                </a>
              </li>
              <li class="p-xs-2 bb-xs-1 text-body-larger subnav-item" data-uid="r" data-name="{{ $lang['menu']['requests'] ?? 'Requests' }}">
                <a href="#">
                  <div class="flag">
                    <div class="flag-body">{{ $lang['menu']['requests'] ?? 'Requests' }}</div>
                    {!! $next_icon !!}
                  </div>
                </a>
              </li>
              <li class="p-xs-2 bb-xs-1 text-body-larger subnav-item" data-uid="contacts" data-name="{{ $lang['menu']['contacts'] ?? 'Contacts' }}">
                <a href="#">
                  <div class="flag">
                    <div class="flag-body">{{ $lang['menu']['contacts'] ?? 'Contacts' }}</div>
                    {!! $next_icon !!}
                  </div>
                </a>
              </li>
              <li class="p-xs-2 bb-xs-1 text-body-larger subnav-item" data-uid="referrals" data-name="{{ $lang['menu']['my_referrals'] ?? 'My Referrals' }}">
                <a href="#">
                  <div class="flag">
                    <div class="flag-body">{{ $lang['menu']['my_referrals'] ?? 'My Referrals' }}</div>
                    {!! $next_icon !!}
                  </div>
                </a>
              </li>
              <li class="p-xs-2 bb-xs-1 text-body-larger subnav-item">
                <a href="{{ $site_url }}/{{ session('seller_user_name') }}">
                  <div class="flag">
                    <div class="flag-body">{{ $lang['menu']['my_profile'] ?? 'My Profile' }}</div>
                  </div>
                </a>
              </li>

              <li class="p-xs-2 bb-xs-1 text-body-larger subnav-item" data-uid="settings" data-name="{{ $lang['menu']['settings'] ?? 'Settings' }}">
                <a href="#">
                  <div class="flag">
                    <div class="flag-body">{{ $lang['menu']['settings'] ?? 'Settings' }}</div>
                    {!! $next_icon !!}
                  </div>
                </a>
              </li>

              <li class="p-xs-2 bb-xs-1 text-body-larger subnav-item" data-uid="support" data-name="{{ $lang['menu']['customer_support'] ?? 'Customer Support' }}">
                <a href="#">
                  <div class="flag">
                    <div class="flag-body">{{ $lang['menu']['customer_support'] ?? 'Customer Support' }}</div>
                    {!! $next_icon !!}
                  </div>
                </a>
              </li>

            </ul>
          </div>
          <!--- mobile-subnav bg-white animated Ends --->

          {{-- Selling tertiary nav --}}
          <div class="mobile-tertiarynav bg-white animated display-none" id="mobile-tertiary-nav-s-1">
            <!--- mobile-tertiarynav Starts --->
            <ul class="list-unstyled mobile-catnav-margin">
              <li class="p-xs-1 bb-xs-1 text-body-larger">
                <a class="p-xs-1 display-inline-block" href="{{ $site_url }}/selling_orders">
                {{ $lang['menu']['orders'] ?? 'Orders' }}
                </a>
              </li>
              <li class="p-xs-1 bb-xs-1 text-body-larger">
                <a class="p-xs-1 display-inline-block" href="{{ $site_url }}/proposals/create_coupon">
                {{ $lang['menu']['create_coupon'] ?? 'Create Coupon' }}
                </a>
              </li>
              <li class="p-xs-1 bb-xs-1 text-body-larger">
                <a class="p-xs-1 display-inline-block" href="{{ $site_url }}/proposals/view_proposals">
                {{ $lang['menu']['my_proposals'] ?? 'My Proposals' }}
                </a>
              </li>
              <li class="p-xs-1 bb-xs-1 text-body-larger">
                <a class="p-xs-1 display-inline-block" href="{{ $site_url }}/requests/buyer_requests">
                {{ $lang['menu']['buyer_requests'] ?? 'Buyer Requests' }}
                </a>
              </li>
              <li class="p-xs-1 bb-xs-1 text-body-larger">
                <a class="p-xs-1 display-inline-block" href="{{ $site_url }}/revenue">
                {{ $lang['menu']['revenues'] ?? 'Revenues' }}
                </a>
              </li>
              <li class="p-xs-1 bb-xs-1 text-body-larger">
                <a class="p-xs-1 display-inline-block" href="{{ $site_url }}/withdrawal_requests">
                {{ $lang['menu']['withdrawal_requests'] ?? 'Withdrawal Requests' }}
                </a>
              </li>
              <li class="p-xs-1 bb-xs-1 text-body-larger strong">
                <a class="p-xs-1 display-inline-block text-underline" href="{{ $site_url }}/proposals/create_proposal">
                Create A New Proposal
                </a>
              </li>
            </ul>
          </div>
          <!--- mobile-tertiarynav Ends --->

          {{-- Buying tertiary nav --}}
          <div class="mobile-tertiarynav bg-white animated display-none" id="mobile-tertiary-nav-b">
            <!--- mobile-tertiarynav Starts --->
            <ul class="list-unstyled mobile-catnav-margin">
              <li class="p-xs-1 bb-xs-1 text-body-larger">
                <a class="p-xs-1 display-inline-block" href="{{ $site_url }}/buying_orders">
                {{ $lang['menu']['orders'] ?? 'Orders' }}
                </a>
              </li>
              <li class="p-xs-1 bb-xs-1 text-body-larger">
                <a class="p-xs-1 display-inline-block" href="{{ $site_url }}/purchases">
                {{ $lang['menu']['purchases'] ?? 'Purchases' }}
                </a>
              </li>
              <li class="p-xs-1 bb-xs-1 text-body-larger">
                <a class="p-xs-1 display-inline-block" href="{{ $site_url }}/favorites">
                {{ $lang['menu']['favorites'] ?? 'Favorites' }}
                </a>
              </li>
            </ul>
          </div>

          <!--- mobile-tertiarynav Ends --->
          {{-- Requests tertiary nav --}}
          <div class="mobile-tertiarynav bg-white animated display-none" id="mobile-tertiary-nav-r">
            <!--- mobile-tertiarynav Starts --->
            <ul class="list-unstyled mobile-catnav-margin">
              <li class="p-xs-1 bb-xs-1 text-body-larger">
                <a class="p-xs-1 display-inline-block" href="{{ $site_url }}/requests/post_request">
                {{ $lang['menu']['post_request'] ?? 'Post Request' }}
                </a>
              </li>
              <li class="p-xs-1 bb-xs-1 text-body-larger">
                <a class="p-xs-1 display-inline-block" href="{{ $site_url }}/requests/manage_requests">
                {{ $lang['menu']['manage_requests'] ?? 'Manage Requests' }}
                </a>
              </li>
            </ul>
          </div>
          <!--- mobile-tertiarynav Ends --->

          {{-- Contacts tertiary nav --}}
          <div class="mobile-tertiarynav bg-white animated display-none" id="mobile-tertiary-nav-contacts">
            <!--- mobile-tertiarynav Starts --->
            <ul class="list-unstyled mobile-catnav-margin">
              <li class="p-xs-1 bb-xs-1 text-body-larger">
                <a class="p-xs-1 display-inline-block" href="{{ $site_url }}/manage_contacts?my_buyers">
                {{ $lang['menu']['my_buyers'] ?? 'My Buyers' }}
                </a>
              </li>
              <li class="p-xs-1 bb-xs-1 text-body-larger">
                <a class="p-xs-1 display-inline-block" href="{{ $site_url }}/manage_contacts?my_sellers">
                {{ $lang['menu']['my_sellers'] ?? 'My Sellers' }}
                </a>
              </li>
            </ul>
          </div>
          <!--- mobile-tertiarynav Ends --->

          {{-- Referrals tertiary nav --}}
          <div class="mobile-tertiarynav bg-white animated display-none" id="mobile-tertiary-nav-referrals">
            <!--- mobile-tertiarynav Starts --->
            <ul class="list-unstyled mobile-catnav-margin">
              <li class="p-xs-1 bb-xs-1 text-body-larger">
                <a class="p-xs-1 display-inline-block" href="{{ $site_url }}/my_referrals">
                {{ $lang['menu']['user_referrals'] ?? 'User Referrals' }}
                </a>
              </li>
              <li class="p-xs-1 bb-xs-1 text-body-larger">
                <a class="p-xs-1 display-inline-block" href="{{ $site_url }}/proposal_referrals">
                {{ $lang['menu']['proposal_referrals'] ?? 'Proposal Referrals' }}
                </a>
              </li>
            </ul>
          </div>
          <!--- mobile-tertiarynav Ends --->

          {{-- Settings tertiary nav --}}
          <div class="mobile-tertiarynav bg-white animated display-none" id="mobile-tertiary-nav-settings">
            <!--- mobile-tertiarynav Starts --->
            <ul class="list-unstyled mobile-catnav-margin">
              <li class="p-xs-1 bb-xs-1 text-body-larger">
                <a class="p-xs-1 display-inline-block" href="{{ $site_url }}/settings?profile_settings">
                {{ $lang['menu']['profile_settings'] ?? 'Profile Settings' }}
                </a>
              </li>
              <li class="p-xs-1 bb-xs-1 text-body-larger">
                <a class="p-xs-1 display-inline-block" href="{{ $site_url }}/settings?account_settings">
                {{ $lang['menu']['account_settings'] ?? 'Account Settings' }}
                </a>
              </li>
            </ul>
          </div>
          <!--- mobile-tertiarynav Ends --->


          {{-- Support tertiary nav --}}
          <div class="mobile-tertiarynav bg-white animated display-none" id="mobile-tertiary-nav-support">
            <!--- mobile-tertiarynav Starts --->
            <ul class="list-unstyled mobile-catnav-margin">
              <li class="p-xs-1 bb-xs-1 text-body-larger">
                <a class="p-xs-1 display-inline-block" href="{{ $site_url }}/support?view_tickets">
                {{ $lang['menu']['my_tickets'] ?? 'My Tickets' }}
                </a>
              </li>
              <li class="p-xs-1 bb-xs-1 text-body-larger">
                <a class="p-xs-1 display-inline-block" href="{{ $site_url }}/customer_support">
                {{ $lang['menu']['support_new'] ?? 'New Ticket' }}
                </a>
              </li>
            </ul>
          </div>
          <!--- mobile-tertiarynav Ends --->

        </div>
      </div>
    </div>
  </div>
</div>
<!--- user-mobile Ends --->
@endif
