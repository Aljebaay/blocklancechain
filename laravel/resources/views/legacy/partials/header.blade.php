@php
    use Illuminate\Support\Facades\DB;

    $isLoggedIn = session()->has('seller_user_name');

    // Announcement bar
    if (!isset($_COOKIE['close_announcement']) || ($_COOKIE['close_announcement'] ?? '') != ($bar_last_updated ?? '')) {
        $showAnnouncement = true;
    } else {
        $showAnnouncement = false;
    }
@endphp

@if($showAnnouncement && ($enable_bar ?? '0') == '1')
  <div id="announcement_bar" style="background-color:{{ $bg_color }};color:{{ $text_color }};">
    <span class="time d-none">{{ $bar_last_updated }}</span>
    {!! $bar_text !!}
    <a href="#" class="float-right close-icon">
      <i class="fa fa-times"></i>
    </a>
  </div>
  <div id="announcement_bar_margin"></div>
@endif

<link href="{{ $site_url }}/styles/scoped_responsive_and_nav.css" rel="stylesheet">
<link href="{{ $site_url }}/styles/vesta_homepage.css" rel="stylesheet">

<div id="gnav-header" class="gnav-header global-nav clear gnav-3">
  <header id="gnav-header-inner" class="gnav-header-inner clear apply-nav-height col-group has-svg-icons body-max-width">
    <div class="col-xs-12">
      <div id="gig-zone-logo" class="apply-nav-height gig-zone-logo-svg gig-zone-logo-svg-logged-in @if($isLoggedIn) loggedInLogo @endif">
        <a href="{{ $site_url }}">
          @if(($site_logo_type ?? 'text') === 'image')
            <img class="desktop" src="{{ $site_logo_image }}" width="150">
          @else
            <span class="desktop text-logo">{{ $site_logo_text }}</span>
          @endif
          @if(($enable_mobile_logo ?? 0) == 1)
            <img class="mobile" src="{{ $site_mobile_logo }}" height="25">
          @endif
        </a>
      </div>
      <button id="mobilemenu" class="unstyled-button mobile-catnav-trigger apply-nav-height icon-b-1 tablet-catnav-enabled {{ ($enable_mobile_logo ?? 0) == 0 ? 'left' : '' }}">
        <span class="screen-reader-only"></span>
        <div class="text-gray-lighter text-body-larger">
          <span class="gig-zone-icon hamburger-icon nav-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
              <path d="M20,6H4A1,1,0,1,1,4,4H20A1,1,0,0,1,20,6Z" />
              <path d="M20,13H4a1,1,0,0,1,0-2H20A1,1,0,0,1,20,13Z" />
              <path d="M20,20H4a1,1,0,0,1,0-2H20A1,1,0,0,1,20,20Z" />
            </svg>
          </span>
        </div>
      </button>
      <div class="catnav-search-bar search-browse-wrapper with-catnav">
        <div class="search-browse-inner">
          <form id="gnav-search" class="search-nav expanded-search apply-nav-height" method="post">
            @csrf
            <div class="gnav-search-inner clearable">
              <label for="search-query" class="screen-reader-only">Search for items</label>
              <div class="search-input-wrapper text-field-wrapper">
                <input id="search-query" class="rounded" name="search_query"
                  placeholder="{{ $lang['search']['placeholder'] ?? 'Search for any service...' }}" value="{{ session('search_query', '') }}" autocomplete="off">
              </div>
              <div class="search-button-wrapper hide">
                <button class="btn btn-primary" style="color:#FFF;background-color: {{ $site_color ?? '#1DBF73' }}" name="search" type="submit" value="Search">
                  {{ $lang['search']['button'] ?? 'Search' }}
                </button>
              </div>
            </div>
            <ul class="search-bar-panel d-none"></ul>
          </form>
        </div>
      </div>
      <ul class="account-nav apply-nav-height">
        @if(!$isLoggedIn)
        <li class="register-link">
            <a href="{{ $site_url }}/freelancers">{{ $lang['freelancers_menu'] ?? 'Freelancers' }}</a>
        </li>
        <li class="sell-on-gig-zone-link d-none d-lg-block">
          <a href="#" data-toggle="modal" data-target="#register-modal">
            <span class="sell-copy">{{ $lang['become_seller'] ?? 'Become a Seller' }}</span>
            <span class="sell-copy short">{{ $lang['become_seller'] ?? 'Become a Seller' }}</span>
          </a>
        </li>
        <li class="register-link">
          <a href="#" data-toggle="modal" data-target="#login-modal">{{ $lang['sign_in'] ?? 'Sign In' }}</a>
        </li>
        <li class="sign-in-link mr-lg-0 mr-3">
          <a href="#" class="btn btn_join" style="color: white;background-color: {{ $site_color ?? '#1DBF73' }}" data-toggle="modal" data-target="#register-modal">
          @if(($deviceType ?? 'computer') === 'phone')
            {{ $lang['mobile_join_now'] ?? 'Join' }}
          @else
            {{ $lang['join_now'] ?? 'Join' }}
          @endif
          </a>
        </li>
        @else
          @include('legacy.partials.user-menu')
        @endif
      </ul>
    </div>
  </header>
</div>

<div class="clearfix"></div>
@include('legacy.partials.categories-nav')
<div class="clearfix"></div>

@if(request()->has('not_available'))
<div class="alert alert-danger text-center mb-0 h6">
  {{ $lang['not_availble'] ?? 'The requested page is not available.' }}
</div>
@endif

@if($isLoggedIn && ($seller_verification ?? 'ok') !== 'ok')
<div class="alert alert-warning clearfix activate-email-class mb-0">
  <div class="float-left mt-2">
    <i style="font-size: 125%;" class="fa fa-exclamation-circle"></i>
    {!! str_replace(['{seller_email}', '{link}'], [$seller_email ?? '', $site_url . '/customer_support'], $lang['popup']['email_confirm']['text'] ?? '') !!}
  </div>
  <div class="float-right">
    <button id="send-email" class="btn btn-success btn-sm float-right text-white">{{ $lang['popup']['email_confirm']['button'] ?? 'Resend Email' }}</button>
  </div>
</div>
<script>
  $(document).ready(function(){
    $("#send-email").click(function(){
      $("#wait").addClass('loader');
      $.ajax({
        method: "POST",
        url: "{{ $site_url }}/includes/send_email",
        success:function(){
          $("#wait").removeClass('loader');
          $("#send-email").html("Resend Email");
          swal({
            type: 'success',
            text: '{{ $lang['alert']['confirmation_email'] ?? 'Confirmation email sent.' }}',
          });
        }
      });
    });
  });
</script>
@endif

@include('legacy.partials.register-login-modals')
@include('legacy.partials.external-stylesheet')
