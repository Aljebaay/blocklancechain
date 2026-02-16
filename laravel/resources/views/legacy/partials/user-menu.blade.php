@php
    $isLoggedIn = session()->has('seller_user_name');
    if (!$isLoggedIn) return;
@endphp
{{-- User menu for logged-in sellers - matches legacy includes/comp/UserMenu.php --}}
<li class="notifications-li d-none d-lg-block">
    <a href="{{ $site_url }}/inbox"><i class="fa fa-envelope-o"></i></a>
</li>
<li class="notifications-li d-none d-lg-block">
    <a href="{{ $site_url }}/notifications"><i class="fa fa-bell-o"></i></a>
</li>
<li class="notifications-li d-none d-lg-block">
    <a href="{{ $site_url }}/orders/buying"><i class="fa fa-shopping-cart"></i></a>
</li>
<li class="user-options dropdown">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <img src="{{ $seller_image ?? '' }}" class="user-avatar rounded-circle" width="32" height="32">
    </a>
    <ul class="dropdown-menu dropdown-menu-right">
        <li class="dropdown-item"><a href="{{ $site_url }}/{{ session('seller_user_name') }}">Profile</a></li>
        <li class="dropdown-item"><a href="{{ $site_url }}/proposals">My Proposals</a></li>
        <li class="dropdown-item"><a href="{{ $site_url }}/orders/buying">Buying</a></li>
        <li class="dropdown-item"><a href="{{ $site_url }}/orders/selling">Selling</a></li>
        <li class="dropdown-item"><a href="{{ $site_url }}/earnings">Earnings</a></li>
        <li class="dropdown-divider"></li>
        <li class="dropdown-item"><a href="{{ $site_url }}/settings">Settings</a></li>
        <li class="dropdown-item"><a href="{{ $site_url }}/logout">Logout</a></li>
    </ul>
</li>
