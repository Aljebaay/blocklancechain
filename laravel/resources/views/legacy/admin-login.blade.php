@php
    use Illuminate\Support\Facades\DB;
    $settings = DB::table('general_settings')->first();
    $site_name = $settings->site_name ?? 'Admin';
@endphp
<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">
  
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
  
    <title>{{ $site_name }} - Admin Login</title>
  
    <meta name="description" content="Admin login. You will need admin credentials to access the admin panel. Reset your password if you have trouble remebering it.">
  
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="apple-touch-icon" href="apple-icon.png">

    <!-- <link rel="shortcut icon" href="favicon.ico"> -->

    <link rel="stylesheet" href="{{ url('admin/assets/css/normalize.css') }}">
    <link rel="stylesheet" href="{{ url('admin/assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ url('admin/assets/css/animate.css') }}">
    <link rel="stylesheet" href="{{ url('admin/assets/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ url('admin/assets/css/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ url('admin/assets/css/flag-icon.min.css') }}">
    <link rel="stylesheet" href="{{ url('admin/assets/css/cs-skin-elastic.css') }}">
    <!-- <link rel="stylesheet" href="assets/css/bootstrap-select.less"> -->
    <link rel="stylesheet" href="{{ url('admin/assets/scss/style.css') }}">
    <link rel="stylesheet" href="{{ url('admin/assets/css/sweat_alert.css') }}">
    
    <script type="text/javascript" src="{{ url('admin/assets/js/ie.js') }}"></script>
    <script type="text/javascript" src="{{ url('admin/assets/js/sweat_alert.js') }}"></script>
    

    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,600,700,800' rel='stylesheet' type='text/css'>

    <!-- <script type="text/javascript" src="https://cdn.jsdelivr.net/html5shiv/3.7.3/html5shiv.min.js"></script> -->
    
    <style>
        
      .swal2-popup .swal2-styled.swal2-confirm {
  
          background-color: #28a745 !important;
      }
          
      .log-width{
          
          width: 550px;
          margin: 0 auto;
      }

    </style>

</head>
<body class="bg-dark">

<script src="{{ url('js/jquery.min.js') }}"></script>


    <div class="sufee-login d-flex align-content-center flex-wrap">
        <div class="container">
            <div class="login-content">
                <div class="login-logo pb-4">
                    <a href="{{ url('admin/login') }}">
                       <h2 class="text-white"> {{ $site_name }}  <span class="badge badge-success p-2 font-weight-bold">ADMIN</span></h2>
                    </a>
                </div>

          @if(request()->has('session_expired'))
            
            <div class="alert alert-danger mb-3 alert-dismissible fade show">

                    <button type="button" class="close" data-dismiss="alert">

                    <span>&times;</span>

                    </button>

                    <span class=" mt-3"><i class="fa  fa-1x fa-exclamation-circle"></i> Your session has expired. Please login again!</span>
                    
              </div>
            
          @endif

          @if(session('admin_login_error'))
            <div class="alert alert-danger mb-3">
                <span><i class="fa fa-exclamation-circle"></i> {{ session('admin_login_error') }}</span>
            </div>
          @endif
                
                <div class="login-form">
     
                <form action="" id="myform" method="post" autocomplete="off">
                @csrf

                <div class="form-group">

                <label>Email</label>

                <input type="text" class="input form-control" value="{{ session('r_email', '') }}" placeholder="Email or Username" name="admin_email" >

                </div>

                <div class="form-group">

                <label>Password</label>

                <input type="password" class="pass form-control" value="{{ session('r_email') ? session('r_passoword', ' ') : ' ' }}" placeholder="Password" name="admin_pass" >

                </div>

                <div class="checkbox pb-2">

                <label>

                <input type="checkbox" {{ session('r_email') ? 'checked="checked"' : '' }} name="remember"> Remember Me

                </label>

                <label class="pull-right">

                <a href="forgot-password">Forgotten Password?</a>

                </label>

                </div>

                <button type="submit" class="btn btn-success btn-flat m-b-30 m-t-30" name="admin_login">Sign in</button>

                </form>
                    
                    
                </div>
                
            </div>
          
        </div>
   
    </div>
	
	  <script>

    $(document).ready(function(){
     
    @if(!session('r_email'))

    setTimeout(function(){
    	
    document.getElementById("myform").reset();

    $(".pass").val("");

    },100);
    	  
    @endif
    	  
    });
		
    </script>
   
<script src="{{ url('admin/assets/js/plugins.js') }}"></script>

</body>

</html>
