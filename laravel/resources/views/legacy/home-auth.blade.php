@extends('legacy.layout')

@section('extra_css')
	<link href="{{ $site_url }}/styles/owl.carousel.css" rel="stylesheet">
	<link href="{{ $site_url }}/styles/owl.theme.default.css" rel="stylesheet">
	<link href="{{ $site_url }}/styles/animate.css" rel="stylesheet">
@endsection

@section('content')
{{-- TODO: Match legacy user_home.php exactly --}}
<div class="container mt-5 mb-5">
	<h2>Welcome back, {{ session('seller_user_name', '') }}!</h2>
	<p>Authenticated user home page — parity implementation pending.</p>
</div>
@endsection
