@extends('legacy.layout')

@section('title'){{ $site_name }} - {{ $proposal_title ?? '' }}@endsection

@section('head_extra')
<meta name="description" content="{{ $proposal_short_desc ?? '' }}">
<meta name="keywords" content="{{ $proposal_tags ?? '' }}">
<meta name="author" content="{{ $proposal_seller_user_name ?? '' }}">
@if(!empty($show_img1 ?? ''))
<meta property="og:image" content="{{ $show_img1 }}"/>
@endif
<link href="{{ $site_url }}/styles/proposalStyles.css" rel="stylesheet">
<link href="{{ $site_url }}/styles/desktop_proposals.css" rel="stylesheet">
<link href="{{ $site_url }}/styles/green-audio-player.css" rel="stylesheet">
@endsection

@section('body_class')is-responsive @endsection

@section('content')
@php
use Illuminate\Support\Facades\DB;

$sLang = $siteLanguage ?? 1;
$legacyData_local = $legacyData ?? null;

// Build image URLs
$show_img1 = $legacyData_local ? $legacyData_local->getImageUrl2('proposals', 'proposal_img1', $proposal_img1 ?? '') : '';
$show_img2 = $legacyData_local ? $legacyData_local->getImageUrl2('proposals', 'proposal_img2', $proposal_img2 ?? '') : '';
$show_img3 = $legacyData_local ? $legacyData_local->getImageUrl2('proposals', 'proposal_img3', $proposal_img3 ?? '') : '';
$show_img4 = $legacyData_local ? $legacyData_local->getImageUrl2('proposals', 'proposal_img4', $proposal_img4 ?? '') : '';

$proposal_rating_val = $proposal_rating ?? 0;
$count_reviews_val = $count_reviews ?? 0;
$count_faq_val = $count_faq ?? 0;
$count_extras_val = $count_extras ?? 0;
$proposal_price_val = $proposal_price ?? 0;
$proposal_seller_vacation_val = $proposal_seller_vacation ?? 'off';
$proposal_seller_id_val = $proposal_seller_id ?? 0;

// Category URLs
$proposal_cat_url = $proposal_cat_url ?? '';
$proposal_child_url = $proposal_child_url ?? '';
$proposal_cat_title = $proposal_cat_title ?? '';
$proposal_child_title = $proposal_child_title ?? '';
@endphp

<script src="https://platform-api.sharethis.com/js/sharethis.js#property=5c812224d11c6a0011c485fd&product=inline-share-buttons"></script>

<div class="mp-gig-top-nav">
  <nav>
    <ul class="container text-center" id="mainNav">
      <li class="selected">
        <a href="#introduction" class="gig-page-nav-link">{{ $lang['proposal']['nav']['introduction'] ?? 'Introduction' }}</a>
      </li>
      <li>
        <a href="#details" class="gig-page-nav-link">{{ $lang['proposal']['nav']['details'] ?? 'Details' }}</a>
      </li>
      @if($count_faq_val != 0)
      <li>
        <a href="#faq" class="gig-page-nav-link">{{ $lang['proposal']['nav']['faq'] ?? 'FAQ' }}</a>
      </li>
      @endif
      <li>
        <a href="#reviews" class="gig-page-nav-link">{{ $lang['proposal']['nav']['reviews'] ?? 'Reviews' }}</a>
      </li>
      <li>
        <a href="#related" class="gig-page-nav-link">{{ $lang['proposal']['nav']['related'] ?? 'Related' }}</a>
      </li>
      @if($proposal_seller_vacation_val == 'off')
      <li>
      <a href="#redirect" onclick="window.location.href='{{ $site_url }}/conversations/message.php?seller_id={{ $proposal_seller_id_val }}'" class="gig-page-nav-link"> 
        <i class="fa fa-comments-o fa-lg"></i> {{ $lang['proposal']['nav']['message_seller'] ?? 'Message Seller' }}</a>
      </li>
      @endif
    </ul>
  </nav>
</div>

<div class="container mt-5" id="introduction">
  <div class="row">
  <div class="col-lg-8 col-md-7 mb-3">
    <div class="card rounded-0 mb-5 border-0">
      <div class="card-body details pt-0">
        <div class="proposal-info {{ ($lang_dir ?? '') == 'right' ? 'text-right' : '' }}">
        <h3>{{ ucfirst($proposal_title ?? '') }}</h3>
        <hr>
        <nav class="breadcrumbs h-text-truncate mb-2">
          <a href="{{ $site_url }}/">Home</a>
          <a href="{{ $site_url }}/categories/category.php?cat_url={{ rawurlencode($proposal_cat_url) }}"> {{ $proposal_cat_title }} </a> 
          <a href="{{ $site_url }}/categories/category.php?cat_url={{ rawurlencode($proposal_cat_url) }}&cat_child_url={{ rawurlencode($proposal_child_url) }}">
          {{ $proposal_child_title }}
          </a>
        </nav>
        @for($i = 0; $i < $proposal_rating_val; $i++)
          <img class='mb-1' src='{{ $site_url }}/images/user_rate_full.png'>
        @endfor
        @for($i = $proposal_rating_val; $i < 5; $i++)
          <img class='mb-1' src='{{ $site_url }}/images/user_rate_blank.png'>
        @endfor
        <span class="text-muted">({{ $count_reviews_val }})</span>
        </div>

        <div id="mp-gig-gallery" class="carousel slide mt-3" data-ride="carousel">
          <div class="carousel-inner">
            @if(!empty($proposal_img1 ?? ''))
            <div class="carousel-item active">
              <img class="d-block w-100" src="{{ $show_img1 }}">
            </div>
            @endif
            @if(!empty($proposal_img2 ?? ''))
            <div class="carousel-item">
              <img class="d-block w-100" src="{{ $show_img2 }}">
            </div>
            @endif
            @if(!empty($proposal_img3 ?? ''))
            <div class="carousel-item">
              <img class="d-block w-100" src="{{ $show_img3 }}">
            </div>
            @endif
          </div>
          <a class="carousel-control-prev" href="#mp-gig-gallery" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          </a>
          <a class="carousel-control-next" href="#mp-gig-gallery" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
          </a>
        </div>

        <div id="details" class="mt-4 {{ ($lang_dir ?? '') == 'right' ? 'text-right' : '' }}">
          <h3>{{ $lang['proposal']['about_proposal'] ?? 'About This Proposal' }}</h3>
          <hr>
          {!! $proposal_desc ?? '' !!}
        </div>

        @if(!empty($proposal_tags ?? ''))
        <div class="mt-3">
          <h4>{{ $lang['proposal']['tags'] ?? 'Tags' }}</h4>
          @foreach(explode(',', $proposal_tags ?? '') as $ptag)
          @if(!empty(trim($ptag)))
          <a href="{{ $site_url }}/tags/{{ str_replace(' ', '-', trim($ptag)) }}" class="badge badge-light p-2 mr-1 mb-1">{{ trim($ptag) }}</a>
          @endif
          @endforeach
        </div>
        @endif

        <div class="sharethis-inline-share-buttons mt-3"></div>

        <div id="reviews" class="mt-5">
          <h3>{{ $lang['proposal']['reviews'] ?? 'Reviews' }} ({{ $count_reviews_val }})</h3>
          <hr>
          @php
            $buyer_reviews = DB::table('buyer_reviews')->where('proposal_id', $proposal_id ?? 0)->orderBy('id', 'DESC')->get();
          @endphp
          @foreach($buyer_reviews as $review)
          @php
            $reviewer = DB::table('sellers')->where('seller_id', $review->buyer_id)->first();
          @endphp
          <div class="media mb-3">
            @if($legacyData_local && $reviewer)
            <img class="mr-3 rounded-circle" src="{{ $legacyData_local->getImageUrl2('sellers', 'seller_image', $reviewer->seller_image ?? '') }}" width="48" height="48">
            @endif
            <div class="media-body">
              <h6 class="mt-0">{{ $reviewer->seller_user_name ?? '' }}
                <small class="text-muted">{{ $review->date ?? '' }}</small>
              </h6>
              @for($i = 0; $i < ($review->buyer_rating ?? 0); $i++)
                <img src='{{ $site_url }}/images/user_rate_full.png' width="14">
              @endfor
              @for($i = ($review->buyer_rating ?? 0); $i < 5; $i++)
                <img src='{{ $site_url }}/images/user_rate_blank.png' width="14">
              @endfor
              <p class="mt-1">{{ $review->buyer_review ?? '' }}</p>
            </div>
          </div>
          @endforeach
        </div>

      </div>
    </div>
  </div>

  <div class="col-lg-4 col-md-5">
    <div class="card rounded-0 mb-4 sticky-top" style="top:80px;">
      <div class="card-body">
        @if(($proposal_price_val ?? 0) > 0)
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="mb-0">{{ $lang['proposal']['starting_at'] ?? 'Starting At' }}</h4>
          <h3 class="mb-0 text-success total-price">{!! $legacyData_local ? $legacyData_local->showPrice($proposal_price_val) : '$'.$proposal_price_val !!}</h3>
        </div>
        @endif

        <div class="text-center mb-3">
          @if($legacyData_local)
          <img src="{{ $legacyData_local->getImageUrl2('sellers', 'seller_image', $proposal_seller_image ?? '') }}" class="rounded-circle mb-2" width="80" height="80">
          @endif
          <h5><a href="{{ $site_url }}/{{ $proposal_seller_user_name ?? '' }}">{{ $proposal_seller_user_name ?? '' }}</a></h5>
          <small class="text-muted">{{ $level_title ?? '' }}</small>
        </div>

        <div class="mb-3">
          <p><i class="fa fa-map-marker"></i> {{ $proposal_seller_country ?? '' }}</p>
          <p><i class="fa fa-clock-o"></i> {{ $delivery_proposal_title ?? '' }}</p>
          <p><i class="fa fa-shopping-cart"></i> {{ $proposal_order_queue ?? 0 }} {{ $lang['proposal']['orders_in_queue'] ?? 'Orders in Queue' }}</p>
        </div>

        @if($proposal_seller_vacation_val != 'on')
        <a href="{{ $site_url }}/conversations/message.php?seller_id={{ $proposal_seller_id_val }}" class="btn btn-outline-success btn-block mb-2">
          <i class="fa fa-comments-o"></i> {{ $lang['proposal']['nav']['message_seller'] ?? 'Message Seller' }}
        </a>
        @endif
      </div>
    </div>
  </div>
  </div>

  <div id="related" class="mb-5">
    <h3>{{ $lang['proposal']['nav']['related'] ?? 'Related Proposals' }}</h3>
    <hr>
    <div class="row">
      @php
        $related = DB::table('proposals')
            ->where('proposal_cat_id', $proposal_cat_id ?? 0)
            ->where('proposal_status', 'active')
            ->where('proposal_id', '!=', $proposal_id ?? 0)
            ->limit(4)
            ->get();
      @endphp
      @foreach($related as $rel)
      @php
        $rel_seller = DB::table('sellers')->where('seller_id', $rel->proposal_seller_id)->first();
        $rel_seller_name = $rel_seller->seller_user_name ?? '';
        $rel_seller_image = $rel_seller->seller_image ?? '';
        $rel_level = $rel_seller->seller_level ?? 0;
        $rel_level_meta = DB::table('seller_levels_meta')->where('level_id', $rel_level)->where('language_id', $sLang)->first();
        $rel_level_title = $rel_level_meta->title ?? '';
        $rel_reviews = DB::table('buyer_reviews')->where('proposal_id', $rel->proposal_id)->get();
        $rel_ratings = [];
        foreach($rel_reviews as $rr) $rel_ratings[] = $rr->buyer_rating;
        $rel_total = array_sum($rel_ratings);
        $rel_avg = count($rel_ratings) > 0 ? $rel_total / count($rel_ratings) : 0;
        $rel_rating = substr((string)$rel_avg, 0, 1);
        if(empty($rel_rating) || $rel_rating == 'N') $rel_rating = 0;
        $rel_packages = DB::table('proposal_packages')->where('proposal_id', $rel->proposal_id)->get();
        $rel_starting = $rel->proposal_price;
        if($rel_starting == 0 && $rel_packages->count() > 0) $rel_starting = $rel_packages->min('price') ?? 0;
      @endphp
      <div class="col-lg-3 col-md-4 col-sm-6 col-12 mb-4">
        @include('legacy.partials.proposal-card', [
            'proposal' => $rel,
            'seller_user_name' => $rel_seller_name,
            'seller_image' => $rel_seller_image,
            'level_title' => $rel_level_title,
            'proposal_rating' => $rel_rating,
            'count_reviews' => count($rel_ratings),
            'starting_at' => $rel_starting,
        ])
      </div>
      @endforeach
    </div>
  </div>
</div>

<div class="append-modal"></div>
@endsection

@section('scripts_extra')
<script type="text/javascript" src="{{ $site_url }}/js/green-audio-player.min.js"></script>
@endsection
