@extends('legacy.layout')

@section('title', ($site_name ?? 'GigZone') . ' - ' . ($lang['titles']['register'] ?? 'Register'))

@section('extra_css')
	<link href="{{ $site_url }}/styles/animate.css" rel="stylesheet">
@endsection

@section('content')
{{-- TODO: Match legacy register.php exactly --}}
<div class="container mt-5 mb-5">
	<div class="row justify-content-center">
		<div class="col-lg-5 col-md-7">
			<h2 class="text-center">{{ $lang['register']['title'] ?? 'Create an Account' }}</h2>
			<p class="text-center">Register page — parity implementation pending.</p>
		</div>
	</div>
</div>
@endsection
