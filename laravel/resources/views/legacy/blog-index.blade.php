@extends('legacy.layout')

@section('title', ' ' . ($site_name ?? 'GigZone') . ' - ' . ($lang['titles']['blog'] ?? 'Blog') . ' ')

@section('meta_description'){{ $site_desc ?? '' }}@endsection

@section('extra_css')
	<link href="{{ $site_url }}/styles/owl.carousel.css" rel="stylesheet">
	<link href="{{ $site_url }}/styles/owl.theme.default.css" rel="stylesheet">
	<link href="{{ $site_url }}/styles/sweat_alert.css" rel="stylesheet">
	<link href="{{ $site_url }}/styles/animate.css" rel="stylesheet">
@endsection

@section('head_extra')
<style>
  .media-object{
    width: 280px;
    height: 150px;
  }
</style>
@endsection

@section('body_class', 'is-responsive blog')

@section('content')
@php
    use Illuminate\Support\Facades\DB;
    $sLang = $siteLanguage ?? 1;
    $langDir = $lang_dir ?? 'left';
    $textRight = $textRight ?? 'text-left';
    $ld = $legacyData ?? null;

    $per_page = 5;
    $page = (int) request('page', 1);
    if ($page < 1) $page = 1;
    $start_from = ($page - 1) * $per_page;
    $search = request('search', '');
    $cat_id_param = request('cat_id');
    $author_param = request('author');
@endphp

<header id="how_to"><!--- how_to Starts --->
  <div class="cell">
    <h2 class="text-center text-white">{{ $lang['blog']['title'] ?? 'Blog' }}</h2>
    <h3 class="text-center mb-0">{{ $lang['blog']['desc'] ?? '' }}</h3>
  </div>
</header><!--- how_to Ends --->

<br><br>
<div class="container mb-5"><!--- container Starts --->
  <div class="row"><!--- row Starts --->
    
    <div class="col-md-8 mb-4 {{ $langDir == 'right' ? 'order-2 order-sm-1' : '' }}"><!--- col-md-8 Starts --->
      @php
        if (!empty($search)) {
            $posts = DB::select("select * from posts_meta LEFT JOIN posts ON posts_meta.post_id = posts.id where posts_meta.language_id=? AND posts_meta.title like ? order by 1 DESC LIMIT ? OFFSET ?", [$sLang, "%{$search}%", $per_page, $start_from]);
        } elseif (!empty($cat_id_param)) {
            $posts = DB::select("select * from posts where cat_id=? order by 1 DESC LIMIT ? OFFSET ?", [$cat_id_param, $per_page, $start_from]);
        } elseif (!empty($author_param)) {
            $posts = DB::select("select * from posts where author=? order by 1 DESC LIMIT ? OFFSET ?", [$author_param, $per_page, $start_from]);
        } else {
            $posts = DB::select("select * from posts order by 1 DESC LIMIT ? OFFSET ?", [$per_page, $start_from]);
        }
      @endphp

      @if(count($posts) == 0)
        <h2 class='h3 text-center bg-white p-5'>No Posts Found.</h2>
      @endif

      @foreach($posts as $post)
      @php
        $post_meta = DB::table('posts_meta')->where('post_id', $post->id)->where('language_id', $sLang)->first();
        $postTitle = $post_meta->title ?? '';
        $url = preg_replace('#[ -]+#', '-', $postTitle);
        $content = substr(strip_tags($post_meta->content ?? ''), 0, 250);
        $get_cat = DB::table('post_categories_meta')->where('cat_id', $post->cat_id)->where('language_id', $sLang)->first();
        $cat_name = $get_cat->cat_name ?? '';
        $postImage = $ld ? $ld->getImageUrl('posts', $post->image ?? '') : '';
      @endphp

      <div class="card mb-4"><!--- card Starts --->
        <div class="card-body row">
          <div class="col-lg-4 col-md-12 {{ $langDir == 'right' ? 'order-lg-2 order-md-1' : '' }}">
            <a href="{{ $post->id }}/{{ $url }}">
              <img src="{{ $postImage }}" class="img-fluid mb-3"/>
            </a>
          </div>
          <div class="col-lg-8 col-md-12 {{ $langDir == 'right' ? 'order-lg-1 order-md-2' : '' }}">
            <h5 class="mt-0 mb-2 {{ $textRight }}">{{ $postTitle }}</h5>
            <p class="small mb-1 {{ $textRight }}">
              <span class="text-muted">Published on:</span> {{ $post->date_time ?? '' }} | 
              <span class="text-muted">Category:</span> 
              <a href="index?cat_id={{ $post->cat_id }}">{{ $cat_name }}</a> | 
              <span class="text-muted">Author:</span> 
              <a href="#">{{ $post_meta->author ?? '' }}</a> 
            </p>
            <p class="post-content {{ $textRight }}">{{ $content }}...</p>
            <a href="{{ $post->id }}/{{ $url }}" class="btn btn-success float-right">Read More</a>
          </div>
        </div>
      </div><!--- card Ends --->
      @endforeach

      @if(empty($cat_id_param) && empty($author_param))
      @php
        if (!empty($search)) {
            $total_records = count(DB::select("select * from posts_meta LEFT JOIN posts ON posts_meta.post_id = posts.id where posts_meta.language_id=? AND posts_meta.title like ?", [$sLang, "%{$search}%"]));
        } else {
            $total_records = DB::table('posts')->count();
        }
        $total_pages = (int) ceil($total_records / $per_page);
      @endphp
      <nav class="nav justify-content-center">
        <ul class="pagination"><!--- pagination Starts --->
          <li class="page-item"><a href="index?search={{ $search }}&page=1" class="page-link">{{ $lang['pagination']['first_page'] ?? 'First' }}</a></li>
          <li class="page-item {{ 1 == $page ? 'active' : '' }}"><a class="page-link" href="index?search={{ $search }}&page=1">1</a></li>
          @php $i = max(2, $page - 5); @endphp
          @if($i > 2)
          <li class="page-item" href="#"><a class="page-link">...</a></li>
          @endif
          @for(; $i < min($page + 6, $total_pages); $i++)
          <li class="page-item {{ $i == $page ? 'active' : '' }}"><a href="index?search={{ $search }}&page={{ $i }}" class="page-link">{{ $i }}</a></li>
          @endfor
          @if($i != $total_pages && $total_pages > 1)
          <li class="page-item" href="#"><a class="page-link">...</a></li>
          @endif
          @if($total_pages > 1)
          <li class="page-item {{ $total_pages == $page ? 'active' : '' }}"><a class="page-link" href="index?search={{ $search }}&page={{ $total_pages }}">{{ $total_pages }}</a></li>
          @endif
          <li class="page-item"><a href="index?search={{ $search }}&page={{ $total_pages }}" class="page-link">{{ $lang['pagination']['last_page'] ?? 'Last' }}</a></li>
        </ul><!--- pagination Ends --->
      </nav>
      @endif
    </div><!--- col-md-8 Ends --->

    <div class="col-md-4 {{ $langDir == 'right' ? 'order-1 order-sm-2' : '' }}"><!--- col-md-4 Starts --->
      @include('legacy.partials.blog-sidebar')
    </div><!--- col-md-4 Ends --->

  </div><!--- row Ends --->
</div><!--- container Ends --->
@endsection
