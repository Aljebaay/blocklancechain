@extends('legacy.layout')

@section('title', ($site_name ?? 'GigZone') . ' - ' . ($lang['titles']['login'] ?? 'Login'))

@section('meta_description')Login or register for an account on {{ $site_name ?? 'GigZone' }}, a fast growing freelance marketplace, where sellers provide their services at extremely affordable prices.@endsection

@section('extra_css')
	<link href="{{ $site_url }}/styles/animate.css" rel="stylesheet">
@endsection

@section('content')

<div class="container mt-5">

	<div class="row justify-content-center">

		<div class="col-lg-5 col-md-7">

			<h2 class="text-center">{!! str_replace('{site_name}', $site_name ?? 'GigZone', $lang['login']['title'] ?? 'Welcome to {site_name}') !!}</h2>

			<div class="box-login mt-4">

				<h2 class="text-center mb-3 mt-3"><i class="fa fa-unlock-alt" ></i></h2>

				@if(session('login_errors'))
				<div class="alert alert-danger">
				<ul class="list-unstyled mb-0">
				@foreach(session('login_errors') as $idx => $error)
				<li class="list-unstyled-item">{{ $idx + 1 }}. {{ ucfirst($error) }}</li>
				@endforeach
				</ul>
				</div>
				@endif

				@if(session('login_warning'))
				<script>
				$(document).ready(function(){
					swal({
						type: 'warning',
						html: $('<div>').text('{{ session('login_warning') }}'),
						animation: false,
						customClass: 'animated tada'
					});
				});
				</script>
				@endif

				@if(session('login_success'))
				<script>
				$(document).ready(function(){
					swal({
						type: 'success',
						text: '{{ session('login_success') }}',
						timer: 4000,
						onOpen: function(){
							swal.showLoading()
						}
					}).then(function(){
						window.open('{{ $site_url }}','_self')
					});
				});
				</script>
				@endif

				<form action="" method="post">
					@csrf

					<div class="form-group">

						<input type="text" name="seller_user_name" class="form-control" placeholder="{{ $lang['placeholder']['username_or_email'] ?? 'Username or Email' }}" required>

	            </div>

	            <div class="form-group">

					<input type="password" name="seller_pass" class="form-control" placeholder="{{ $lang['placeholder']['password'] ?? 'Password' }}" required>

	            </div>

	            <div class="form-group">

					<input type="submit" name="access" class="btn btn-success btn-block" value="{{ $lang['button']['login'] ?? 'Login' }}" required>

	            </div>

				</form>
				@if(($enable_social_login ?? 'no') === 'yes')

				<div class="text-center pt-2 pb-2">{{ $lang['modals']['login']['or'] ?? 'or' }}</div>

				<hr class="mb-0 mt-0">

				<div class="line mt-3"><span></span></div>

				<div class="text-center">

				@if(!empty($fb_app_id ?? '') && !empty($fb_app_secret ?? ''))
				<a href="#" class="btn btn-primary text-white">
					<i class="fa fa-facebook"></i> FACEBOOK
				</a>
				@endif

				@if(!empty($g_client_id ?? '') && !empty($g_client_secret ?? ''))
				<a href="#" class="btn btn-danger text-white">
					<i class="fa fa-google"></i> GOOGLE
				</a>
				@endif

				</div>

				<div class="clearfix"></div>

            @endif

				<div class="text-center mt-3">

					<a href="#" data-toggle="modal" data-target="#register-modal">

					<i class="fa fa-user-plus"></i> {{ $lang['modals']['login']['not_registerd'] ?? 'Not Registered?' }}

               </a>

					&nbsp; &nbsp; | &nbsp; &nbsp;

               <a href="#" data-toggle="modal" data-target="#forgot-modal">

                  <i class="fa fa-meh-o"></i>	{{ $lang['modals']['login']['forgot_password'] ?? 'Forgot Password?' }}

               </a>

             </div>

            </div>


		</div>

	</div>

</div>

@endsection
