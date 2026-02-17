@php
  $error_array = session('error_array', []);
  $enable_social_login_val = $enable_social_login ?? 'no';
  $fb_app_id_val = $row_general_settings->fb_app_id ?? '';
  $fb_app_secret_val = $row_general_settings->fb_app_secret ?? '';
  $g_client_id_val = $row_general_settings->g_client_id ?? '';
  $g_client_secret_val = $row_general_settings->g_client_secret ?? '';
  $make_phone_required = $make_phone_number_required ?? 0;
  $countries = \Illuminate\Support\Facades\DB::table('countries')->get();
@endphp
<!-- Registration Modal starts -->
<div class="modal fade" id="register-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <!-- modal-header Starts -->
        <i class="fa fa-user-plus"></i> 
        <h5 class="modal-title"> {{ $lang['modals']['register']['title'] ?? 'Register' }} </h5>
        <button class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <!-- modal-header Ends -->
      <div class="modal-body">
        <!-- modal-body Starts -->
        @if(session('register_errors'))
        <div class="alert alert-danger">
          <!--- alert alert-danger Starts --->
          <ul class="list-unstyled mb-0">
            @foreach(session('register_errors') as $idx => $error)
            <li class="list-unstyled-item">{{ $idx + 1 }}. {{ ucfirst($error) }}</li>
            @endforeach
          </ul>
        </div>
        <!--- alert alert-danger Ends --->
        <script type="text/javascript">
          $(document).ready(function(){
            $('#register-modal').modal('show');
          });
        </script>
        @endif
        <form action="" method="post" class="pb-3">
          @csrf

          <div class="form-group">
            <label class="form-control-label font-weight-bold"> {{ $lang['label']['full_name'] ?? 'Full Name' }} </label>
            <input type="text" class="form-control" name="name" placeholder="{{ $lang['placeholder']['full_name'] ?? 'Full Name' }}" value="{{ session('name', '') }}" required="">
          </div>

          <div class="form-group">

            <label class="form-control-label font-weight-bold"> 

              {{ $lang['label']['username'] ?? 'Username' }}
              <span class="font-weight-bold text-success">{{ $lang['warning']['no_spaces'] ?? '(no spaces)' }}</span>

            </label>

            <input type="text" class="form-control" name="u_name" placeholder="Enter Your Username" value="{{ session('u_name', '') }}" required="">
            <small class="form-text text-muted">{{ $lang['warning']['note'] ?? '' }}</small>
            
            @if(in_array("Opps! This username has already been taken. Please try another one", $error_array))
            <span style='color:red;'>{{ $lang['warning']['username_already'] ?? '' }}</span> <br>
            @endif

            @if(in_array("Username must be greater that 4 characters long or less than 25 characters.", $error_array))
            <span style='color:red;'>{{ $lang['warning']['username_greater'] ?? '' }}</span> <br>
            @endif

            @if(in_array("Spaces Are Not Allowed In Username. Please Remove The Spaces.", $error_array))
            <span style='color:red;'>{{ $lang['warning']['spaces_not_allowed'] ?? '' }}</span> <br>
            @endif

          </div>

          <div class="form-group">
            <label class="form-control-label font-weight-bold"> {{ $lang['label']['email'] ?? 'Email' }} </label>
            <input type="email" class="form-control" name="email" placeholder="{{ $lang['placeholder']['email'] ?? 'Email' }}" value="{{ session('email', '') }}" required="">
            @if(in_array("Email has already been taken. Try logging in instead.", $error_array))
            <span style='color:red;'>Email has already been taken. Try logging in instead.</span> <br>
            @endif
          </div>

          <div class="form-group phoneNo">
            <label class="form-control-label font-weight-bold"> 
              {{ $lang['label']['phone'] ?? 'Phone' }}
              {{ $make_phone_required == 1 ? ($lang['label']['phone_required'] ?? '(required)') : ($lang['label']['phone_optional'] ?? '(optional)') }}
            </label>
            <div class="input-group">

              <span class="input-group-addon p-0 border-0 rounded-0 w-50"><select name="country_code" class="form-control border-right-0">
@foreach($countries as $ctry)
@if(!empty($ctry->code))
	<option value="+{{ $ctry->code }}">{{ $ctry->name }} (+{{ $ctry->code }})</option>
@endif
@endforeach
</select></span>

              <input type="text" class="form-control w-750" name="phone" placeholder="{{ $lang['placeholder']['phone'] ?? 'Phone' }}" value="{{ session('phone', '') }}" {{ $make_phone_required == 1 ? 'required' : '' }}/>

            </div>
          </div>

          <div class="form-group">
            <label class="form-control-label font-weight-bold"> {{ $lang['label']['password'] ?? 'Password' }} </label>
            <input type="password" class="form-control" name="pass" placeholder="{{ $lang['placeholder']['password'] ?? 'Password' }}" required="">
          </div>

          <div class="form-group">
            <label class="form-control-label font-weight-bold"> {{ $lang['label']['password_confirm'] ?? 'Confirm Password' }} </label>
            <input type="password" class="form-control" name="con_pass" placeholder="{{ $lang['placeholder']['password_confirm'] ?? 'Confirm Password' }}" required="">
            @if(in_array("Passwords don't match. Please try again.", $error_array))
            <span style='color:red;'>{{ $lang['label']['dont_match'] ?? "Passwords don't match" }}</span> <br>
            @endif
          </div>

          <div class="form-group">
            <input type="checkbox" style="position: relative; top: 1px;" id="check" value="1" required=""/>
            <label for="check">
              I Accept 
              <a class="text-success" href="{{ $site_url }}/terms_and_conditions">Terms And Conditions</a>
            </label>
          </div>

          @if(request()->has('referral'))
          <input type="hidden" class="form-control" name="referral" value="{{ request('referral') }}">
          @else
          <input type="hidden" class="form-control" name="referral" value="">
          @endif
          <input type="hidden" name="timezone" value="">
          <input type="submit" name="register" class="btn btn-success btn-block" value="{{ $lang['button']['register'] ?? 'Register' }}">
        </form>
        @if($enable_social_login_val == 'yes')
        <div class="clearfix"></div>
        <div class="text-center">{{ $lang['modals']['register']['or'] ?? 'or' }}</div>
        <hr class="">
        <div class="line mt-3"><span></span></div>
        <div class="text-center">
          @if(!empty($fb_app_id_val) && !empty($fb_app_secret_val))
          <a href="#" class="btn btn-primary btn-fb-connect" >
          <i class="fa fa-facebook"></i> FACEBOOK
          </a>
          @endif
          @if(!empty($g_client_id_val) && !empty($g_client_secret_val))
          <a href="#" class="btn btn-danger btn-gplus-connect " >
          <i class="fa fa-google"></i> GOOGLE
          </a>
          @endif
        </div>
        <div class="clearfix"></div>
        @endif
        <div class="text-center mt-3 text-muted">
          {{ $lang['modals']['register']['already_account'] ?? 'Already have an account?' }}
          <a href="#" class="text-success" data-toggle="modal" data-target="#login-modal" data-dismiss="modal">
            {{ $lang['modals']['register']['login'] ?? 'Login' }}
          </a>
        </div>
      </div>
      <!-- modal-body Ends -->
    </div>
  </div>
</div><!-- Registration modal ends -->

<!-- Login modal start -->
<div class="modal fade login" id="login-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <!-- Modal header start -->
        <i class="fa fa-sign-in fa-log"></i> 
        <h5 class="modal-title">{{ $lang['modals']['login']['title'] ?? 'Login' }}</h5>
        <button class="close" type="button" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <!-- Modal header end -->
      <div class="modal-body">
        <!-- Modal body start -->
        @if(session('login_errors'))
        <div class="alert alert-danger">
          <!--- alert alert-danger Starts --->
          <ul class="list-unstyled mb-0">
            @foreach(session('login_errors') as $idx => $error)
            <li class="list-unstyled-item">{{ $idx + 1 }}. {{ ucfirst($error) }}</li>
            @endforeach
          </ul>
        </div>
        <!--- alert alert-danger Ends --->
        <script type="text/javascript">
          $(document).ready(function(){
            $('#login-modal').modal('show');
          });
        </script>
        @endif

        <form action="" method="post">
          @csrf
          <div class="form-group">
            <label class="form-group-label"> {{ $lang['label']['username'] ?? 'Username' }}</label>
            <input type="text" class="form-control" name="seller_user_name" placeholder="{{ $lang['placeholder']['username_or_email'] ?? 'Username or Email' }}"  value= "" required="">
          </div>
          <div class="form-group">
            <label class="form-group-label"> {{ $lang['label']['password'] ?? 'Password' }}</label>
            <input type="password" class="form-control" name="seller_pass" placeholder="{{ $lang['placeholder']['password'] ?? 'Password' }}" required="">
          </div>

          <input type="submit" name="login" class="btn btn-success btn-block" value="{{ $lang['button']['login_now'] ?? 'Login Now' }}">
        </form>
        @if($enable_social_login_val == 'yes')
        <div class="clearfix"></div>
        <div class="text-center pt-4 pb-2">{{ $lang['modals']['login']['or'] ?? 'or' }}</div>
        <hr class="">
        <div class="line mt-3"><span></span></div>
        <div class="text-center">

          @if(!empty($fb_app_id_val) && !empty($fb_app_secret_val))
          <a href="#" class="btn btn-primary btn-fb-connect">
            <i class="fa fa-facebook"></i> FACEBOOK
          </a>
          @endif

          @if(!empty($g_client_id_val) && !empty($g_client_secret_val))
          <a href="#" class="btn btn-danger btn-gplus-connect">
            <i class="fa fa-google"></i> GOOGLE
          </a>
          @endif

        </div>
        <div class="clearfix"></div>
        @endif
        <div class="text-center mt-3">
          <a href="#" class="text-success" data-toggle="modal" data-target="#register-modal" data-dismiss="modal">
          {{ $lang['modals']['login']['not_registerd'] ?? 'Not registered?' }}
          </a>
          &nbsp; &nbsp; | &nbsp; &nbsp;
          <a href="#" class="text-success" data-toggle="modal" data-target="#forgot-modal" data-dismiss="modal">
          {{ $lang['modals']['login']['forgot_password'] ?? 'Forgot password?' }}
          </a>
        </div>
      </div>
      <!-- Modal body ends -->
    </div>
  </div>
</div>
<!-- Login modal end -->

<!-- Forgot password starts -->
<div class="modal fade login" id="forgot-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><!-- Modal header starts -->
        <i class="fa fa-meh-o fa-log"></i>
        <h5 class="modal-title"> {{ $lang['modals']['forgot']['title'] ?? 'Forgot Password' }} </h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div><!-- Modal header ends -->
      <div class="modal-body"><!-- Modal body starts -->
        <p class="text-muted text-center mb-2">
          {{ $lang['modals']['forgot']['desc'] ?? 'Enter your email to reset your password.' }}
        </p>
        <form action="" method="post">
          @csrf
          <div class="form-group">
            <input type="text" name="forgot_email" class="form-control" placeholder="{{ $lang['placeholder']['email'] ?? 'Email' }}" required>
          </div>
          <input type="submit" class="btn btn-success btn-block" value="submit" name="forgot">
          <p class="text-muted text-center mt-4">
            {{ $lang['modals']['forgot']['not_member_yer'] ?? 'Not a member yet?' }}
            <a href="#"class="text-success" data-toggle="modal" data-target="#register-modal" data-dismiss="modal">
              {{ $lang['modals']['forgot']['join_now'] ?? 'Join Now' }}
            </a>
          </p>
        </form>
      </div><!-- Modal body ends -->
    </div>
  </div>
</div><!-- Forgot password ends -->
