<!DOCTYPE html>
<html lang="en" class="ui-toolkit">
<head>
	<title>@yield('title', $site_title ?? $site_name ?? 'GigZone')</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="@yield('meta_description', $site_desc ?? '')">
	<meta name="keywords" content="{{ $site_keywords ?? '' }}">
	<meta name="author" content="{{ $site_author ?? '' }}">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700,300,100" rel="stylesheet">
	<link href="{{ $site_url }}/styles/bootstrap.css" rel="stylesheet">
	<link href="{{ $site_url }}/styles/custom.css" rel="stylesheet">
	<link href="{{ $site_url }}/styles/styles.css" rel="stylesheet">
	<link href="{{ $site_url }}/styles/categories_nav_styles.css" rel="stylesheet">
	<link href="{{ $site_url }}/font_awesome/css/font-awesome.css" rel="stylesheet">
	<link href="{{ $site_url }}/styles/sweat_alert.css" rel="stylesheet">
	@yield('extra_css')
	<script type="text/javascript" src="{{ $site_url }}/js/ie.js"></script>
	<script type="text/javascript" src="{{ $site_url }}/js/sweat_alert.js"></script>
	<script type="text/javascript" src="{{ $site_url }}/js/jquery.min.js"></script>
	<script type="text/javascript">
		$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
	</script>
	@if(!empty($site_favicon))
		<link rel="shortcut icon" href="{{ $site_favicon }}" type="image/x-icon" />
	@endif
	@yield('head_extra')
</head>
<body class="@yield('body_class', 'is-responsive')">

@include('legacy.partials.header')

@yield('content')

@include('legacy.partials.footer')

@if(($knowledge_bank ?? 'no') === 'yes' && !($hide_knowledge_bank ?? false))
<div class="sm popup-support-wrap">
	<div class="popup-support">
		<header class="hero-container" style="background-color: rgb(29,191,115); color: rgb(255,255,255);">
			<div class="hero">
				<h1 class="main-title">
					<a href="#" class="sm-back">
						<i class="pull-left fa fa-angle-left"></i>
					</a>
					{{ $lang['popup']['knowledge_bank']['title'] ?? '' }}
				</h1>
				<a class="support-nav" href="#">{{ $lang['popup']['knowledge_bank']['heading'] ?? '' }}</a>
				<h2 class="sub-title"></h2>
				<div class="search-box">
					<div class="search-placeholder">
						<span class="svg-icon search-magnifier"><i class="fa fa-search"></i></span>
					</div>
					<input type="text" id="sm-search" value="">
				</div>
			</div>
		</header>
		<div class="search-results">
			<div class="pull-left search-articles">
				<h3></h3>
				<ul></ul>
			</div>
			<div class="pull-left search-single">
				<div class="breadcrumbs">
					<a href="#" class="home-link" data-id="">
						<i class="fa fa-home"></i> <i class="fa fa-angle-right"></i>
						&nbsp;<span class="sm-category"></span>
					</a>
				</div>
				<div class="sm-title"></div>
				<div class="img imgtop"></div>
				<div class="sm-content"></div>
				<div class="img imgright"></div>
				<div class="img imgbottom"></div>
			</div>
		</div>
	</div>
</div>
<a class="support-que close pull-right">
	<i class="open-popup fa fa-question"></i>
	<i class="close-popup fa fa-remove"></i>
</a>
<script>var site_url='{{ $site_url }}';</script>
<script type="text/javascript" src="{{ $site_url }}/js/knowledge-bank.js"></script>
@endif

@yield('scripts')
@yield('scripts_extra')

</body>
</html>
