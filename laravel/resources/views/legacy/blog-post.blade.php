@extends('legacy.layout')

@section('title'){{ $site_name }} - {{ $lang['titles']['blog'] ?? 'Blog' }}@endsection

@section('head_extra')
<base href="{{ $site_url }}/blog/"/>
<link href="../styles/owl.carousel.css" rel="stylesheet">
<link href="../styles/owl.theme.default.css" rel="stylesheet">
<script src="https://platform-api.sharethis.com/js/sharethis.js#property=5c812224d11c6a0011c485fd&product=inline-share-buttons"></script>
@endsection

@section('body_class')is-responsive blog @endsection

@section('content')
  <header id="how_to"><!--- how_to Starts --->
   <div class="cell">
      <h2 class="text-center text-white">{{ $lang['blog']['title'] ?? 'Blog' }}</h2>
      <h3 class="text-center mb-0">{{ $lang['blog']['desc'] ?? '' }}</h3>
    </div>
  </header><!--- how_to Ends --->

  <br><br>
  <div class="container mb-5"><!--- container Starts --->
    <div class="row"><!--- row Starts --->
      
      <div class="col-md-8 mb-4 {{ ($lang_dir ?? '') == 'right' ? 'order-2 order-sm-1' : '' }}"><!--- col-md-8 Starts --->
        @include('legacy.partials.blog-single')
      </div><!--- col-md-8 Ends --->

      <div class="col-md-4 {{ ($lang_dir ?? '') == 'right' ? 'order-1 order-sm-2' : '' }}"><!--- col-md-4 Starts --->
        @include('legacy.partials.blog-sidebar')
      </div><!--- col-md-4 Ends --->

    </div><!--- row Ends --->
  </div><!--- container Ends --->
@endsection
