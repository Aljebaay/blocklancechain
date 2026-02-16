@extends('legacy.layout')

@section('head_extra')
	<style>.swal2-popup .swal2-styled.swal2-confirm{background-color: #28a745;}</style>
@endsection

@section('extra_css')
	<link href="{{ $site_url }}/styles/owl.carousel.css" rel="stylesheet">
	<link href="{{ $site_url }}/styles/owl.theme.default.css" rel="stylesheet">
	<link href="{{ $site_url }}/styles/animate.css" rel="stylesheet">
	@if(($row_general_settings->knowledge_bank ?? 'no') === 'yes')
		<link href="{{ $site_url }}/styles/knowledge_bank.css" rel="stylesheet">
	@endif
@endsection

@section('content')
@php
    $ld = $legacyData ?? null;
@endphp
<!-- start main -->
<div id="demo1" class="main carousel">
  <ul class="carousel-indicators">
    <li data-target="#demo1" data-slide-to="0" class="active"></li>
    @foreach($slides ?? [] as $idx => $slide)
      @if($idx > 0)
    <li data-target="#demo1" data-slide-to="{{ $idx }}"></li>
      @endif
    @endforeach
  </ul>
  <div class="carousel-inner">
    <div class="carousel-caption">
      <h1>{{ $section_heading ?? '' }}</h1>
      <h5>{{ $section_short_heading ?? '' }}</h5>
      <div class="row justify-content-center">
        <div class="col-md-5 col-11">
          <form action="" method="post">
            @csrf
            <div class="input-group space20">
              <input type="text" name="search_query" class="form-control" value="{{ session('search_query', '') }}" placeholder="{{ $lang['search']['placeholder'] ?? 'Search for any service...' }}">
              <div class="input-group-append move-icon-up">
                <button name="search" type="submit" class="search_button">
                    <img src="images/srch2.png" class="srch2">
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
    @foreach($slides ?? [] as $idx => $slide)
    @php
        $slideImage = $ld ? $ld->getImageUrl('home_section_slider', $slide->slide_image) : '';
        $sExtension = pathinfo($slideImage, PATHINFO_EXTENSION);
    @endphp
    <div class="carousel-item {{ $idx === 0 ? 'active' : '' }}">
        @if(in_array($sExtension, ['mp4', 'webm', 'ogg']))
          <video class="img-fluid w-100" controls muted {{ $idx === 0 ? 'autoplay' : '' }}>
            <source src="{{ $slideImage }}" type="video/mp4">
          </video>
        @else
            <img src="{{ $slideImage }}">
        @endif
    </div>
    @endforeach
  </div>

  <a class="carousel-control-prev" href="#demo1" data-slide="prev" style="width: 6%; opacity: 1;">
    <i class="fa fa-arrow-circle-o-left fa-3x"></i>
  </a>

  <a class="carousel-control-next" href="#demo1" data-slide="next" style="width: 6%; opacity: 1;">
    <i class="fa fa-arrow-circle-o-right fa-3x"></i>
  </a>

</div>
<!-- end main -->
<!-- start market -->
<div class="container mb-5 cards" style="max-width: 1360px !important;">
  <div class="row">
    <div class="col-md-12">
      <h1 class="mt-5 mb-1 {{ ($lang_dir ?? 'left') == 'right' ? 'text-right' : '' }}">{{ $lang['home']['cards']['title'] ?? '' }}</h1>
      <p class="subHeading mb-4 {{ ($lang_dir ?? 'left') == 'right' ? 'text-right' : '' }}">{{ $lang['home']['cards']['desc'] ?? '' }}</p>
      <div class="owl-carousel home-cards-carousel owl-theme">
        @foreach($home_cards ?? [] as $card)
        @php
            $cardImage = $ld ? $ld->getImageUrl('home_cards', $card->card_image) : '';
            $cardLink = $ld ? $ld->dynamicUrl($card->card_link, true) : ($site_url . '/' . ltrim($card->card_link, '/'));
        @endphp
        <div class="card-box">
          <div>
            <a href="{{ $cardLink }}" class="subcategory">
              <h4><small>{{ $card->card_desc }}</small>{{ $card->card_title }}</h4>
              <picture>
                <img src="{{ $cardImage }}">
              </picture>
            </a>
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </div>
</div>
<!-- start market -->
<section class="market">
<div class="container" style="max-width: 1360px !important;">
  <div class="row">
    <div class="col-md-12">
      <h2>{{ $lang['home']['categories']['title'] ?? '' }}</h2>
      <h4>{{ $lang['home']['categories']['desc'] ?? '' }}</h4>
      <div class="row space80">
        @foreach($categories_row1 ?? [] as $cat)
        @php
            $catImage = $ld ? $ld->getImageUrl('categories', $cat->cat_image) : '';
            $catMeta = \Illuminate\Support\Facades\DB::table('cats_meta')
                ->where('cat_id', $cat->cat_id)
                ->where('language_id', $siteLanguage)
                ->first();
            $catTitle = $catMeta->cat_title ?? '';
        @endphp
        <div class="col-md-3 col-6">
          <a href="categories/{{ $cat->cat_url }}">
            <div class="grn_box">
              <img src="{{ $catImage }}" class="mx-auto d-block">
              <p>{{ $catTitle }}</p>
            </div>
          </a>
        </div>
        @endforeach
      </div>
      <div class="space80 hidden-xs"></div>
      <div class="space20 visible-xs"></div>
      <div class="row space80">
        @foreach($categories_row2 ?? [] as $cat)
        @php
            $catImage = $ld ? $ld->getImageUrl('categories', $cat->cat_image) : '';
            $catMeta = \Illuminate\Support\Facades\DB::table('cats_meta')
                ->where('cat_id', $cat->cat_id)
                ->where('language_id', $siteLanguage)
                ->first();
            $catTitle = $catMeta->cat_title ?? '';
        @endphp
        <div class="col-md-3 col-6">
          <a href="categories/{{ $cat->cat_url }}">
            <div class="grn_box">
              <img src="{{ $catImage }}" class="mx-auto d-block" />
              <p>{{ $catTitle }}</p>
            </div>
          </a>
        </div>
        @endforeach
      </div>

    </div>
  </div>
</div>
</section>
<!-- end market -->
<!-- start timer -->
<section class="timer">
<div class="container" style="max-width: 1335px !important;">
  <div class="row">
    @foreach($section_boxes_first ?? [] as $box)
    @php
        $boxImage = $ld ? $ld->getImageUrl('section_boxes', $box->box_image) : '';
    @endphp
    <div class="col-md-4 pad0">
      <div class="box">
        <h5>{{ $box->box_title }}</h5>
        <p>{{ $box->box_desc }}</p>
      </div>
    </div>
    <div class="col-md-4 pad0">
      <div class="blu_box">
        <img src="{{ $boxImage }}" class="img-fluid mx-auto d-block">
      </div>
    </div>
    @endforeach
    @foreach($section_boxes_rest ?? [] as $box)
    @php
        $boxImage = $ld ? $ld->getImageUrl('section_boxes', $box->box_image) : '';
    @endphp
    <div class="col-md-4 pad0">
      <div class="box">
        <h5>{{ $box->box_title }}</h5>
        <p>{{ $box->box_desc }}</p>
      </div>
    </div>
    <div class="col-md-4 pad0">
      <div class="blu_box1">
        <img src="{{ $boxImage }}" class="img-fluid mx-auto d-block">
      </div>
    </div>
    @endforeach
  </div>
</div>
</section>
<!-- end timer -->
<!-- start top -->
<section class="top mb-0">
  <div class="container" style="max-width: 1360px !important;">
    <div class="row">
      <div class="col-md-12">
        <h1 class="text-center">{{ $lang['home']['proposals']['title'] ?? '' }}</h1>
        <h4 class="text-center">{{ $lang['home']['proposals']['desc'] ?? '' }}</h4>
        @php
          $viewMoreFeaturedLabel = $lang['home']['proposals']['view_more'] ?? 'View More';
        @endphp
        @if(($featured_proposals_count ?? 0) > 1)
        <span class="pull-right text-success"><a href="featured_proposals">{{ $viewMoreFeaturedLabel }}</a></span>
        @endif
        <div class="mt-5">
          <div class="row">
            @foreach($featured_proposals ?? [] as $proposal)
            <div class="col-xl-2dot4 col-lg-3 col-md-4 col-sm-6 col-xs-12 mb-4">
              @include('legacy.partials.proposal-card', ['proposal' => $proposal])
            </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>

$(document).ready(function(){

  var slider = $('#demo1').carousel({
    interval: 4000
  });

  var active = $(".carousel-item.active").find("video");
  var active_length = active.length;

  if(active_length == 1){
    slider.carousel('pause');
    console.log('paused');
    $(".carousel-indicators").css({"bottom": "90px"});
  }

  $("#demo1").on('slide.bs.carousel', function(event){
    var eq = event.to;
    var video = $(event.relatedTarget).find("video");
    if(video.length == 1){
        slider.carousel('pause');
        console.log('paused');
        $(".carousel-indicators").css({"bottom": "90px"});
        video.trigger('play');
    }else{
      $(".carousel-indicators").css({"bottom": "20px"});
    }
  });

  $('video').on('ended',function(){
    slider.carousel({'pause': false});
    console.log('started');
  });

});

</script>
@endsection
