@extends('legacy.layout')

@section('title'){{ $site_name }} - {{ ucfirst($profile_username ?? '') . "'s Profile" }}@endsection

@section('head_extra')
<link href="{{ $site_url }}/styles/proposalStyles.css" rel="stylesheet">
<link rel="stylesheet" href="{{ $site_url }}/styles/chosen.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js"></script>
@endsection

@section('body_class')is-responsive @endsection

@section('content')
@php
use Illuminate\Support\Facades\DB;

$sLang = $siteLanguage ?? 1;
$legacyData_local = $legacyData ?? null;
$seller_user_name = $profile_username ?? '';

$seller = DB::table('sellers')
    ->where('seller_user_name', $seller_user_name)
    ->whereNotIn('seller_status', ['deactivated', 'block-ban'])
    ->first();

if(!$seller) {
    echo "<script>window.open('" . ($site_url ?? '') . "/index?not_available','_self');</script>";
    return;
}

$seller_id = $seller->seller_id;
$seller_image = $seller->seller_image ?? '';
$seller_country = $seller->seller_country ?? '';
$seller_city = $seller->seller_city ?? '';
$seller_about = $seller->seller_about ?? '';
$seller_level = $seller->seller_level ?? 0;
$seller_rating = $seller->seller_rating ?? 0;
$seller_recent_delivery = $seller->seller_recent_delivery ?? '';
$seller_member_since = $seller->seller_register_date ?? '';
$seller_vacation = $seller->seller_vacation ?? 'off';
$seller_cover = $seller->seller_cover_image ?? '';
$seller_status_txt = $seller->seller_status ?? '';

$level_meta = DB::table('seller_levels_meta')
    ->where('level_id', $seller_level)
    ->where('language_id', $sLang)
    ->first();
$level_title = $level_meta->title ?? '';

$seller_proposals = DB::table('proposals')
    ->where('proposal_seller_id', $seller_id)
    ->where('proposal_status', 'active')
    ->get();
$count_proposals = $seller_proposals->count();

// Calculate overall rating
$all_ratings = [];
foreach($seller_proposals as $sp) {
    $revs = DB::table('buyer_reviews')->where('proposal_id', $sp->proposal_id)->get();
    foreach($revs as $r) $all_ratings[] = $r->buyer_rating;
}
$total_rating = array_sum($all_ratings);
$avg_rating = count($all_ratings) > 0 ? round($total_rating / count($all_ratings), 1) : 0;

// Languages
$seller_languages = DB::table('languages_relation')
    ->join('seller_languages', 'languages_relation.language_id', '=', 'seller_languages.language_id')
    ->where('languages_relation.seller_id', $seller_id)
    ->get();

// Skills
$seller_skills = DB::table('skills_relation')
    ->join('seller_skills', 'skills_relation.skill_id', '=', 'seller_skills.skill_id')
    ->where('skills_relation.seller_id', $seller_id)
    ->get();
@endphp

{{-- User Profile Header --}}
<div class="user-cover-image" style="background: url('{{ $legacyData_local ? $legacyData_local->getImageUrl2('sellers', 'seller_cover', $seller_cover) : '' }}') center center / cover no-repeat; min-height: 200px;">
</div>

<div class="container">
  <div class="row">
    <div class="col-md-4 mt-4">
      {{-- User Sidebar --}}
      <div class="card mb-4 rounded-0">
        <div class="card-body text-center">
          @if($legacyData_local)
          <img src="{{ $legacyData_local->getImageUrl2('sellers', 'seller_image', $seller_image) }}" class="rounded-circle mb-3" width="120" height="120">
          @endif
          <h4>{{ $seller_user_name }}</h4>
          @if(!empty($level_title))
          <span class="badge badge-success">{{ $level_title }}</span>
          @endif

          @for($i = 0; $i < floor($avg_rating); $i++)
            <img src='{{ $site_url }}/images/user_rate_full.png' width="14">
          @endfor
          @for($i = floor($avg_rating); $i < 5; $i++)
            <img src='{{ $site_url }}/images/user_rate_blank.png' width="14">
          @endfor
          <span class="text-muted">({{ count($all_ratings) }})</span>

          <hr>
          <div class="text-left">
            <p><i class="fa fa-map-marker"></i> {{ $seller_country }}@if(!empty($seller_city)), {{ $seller_city }}@endif</p>
            <p><i class="fa fa-clock-o"></i> {{ $lang['user_profile']['member_since'] ?? 'Member Since' }}: {{ $seller_member_since }}</p>
            @if(!empty($seller_recent_delivery))
            <p><i class="fa fa-truck"></i> {{ $lang['user_profile']['recent_delivery'] ?? 'Recent Delivery' }}: {{ $seller_recent_delivery }}</p>
            @endif
          </div>

          @if($seller_vacation == 'on')
          <div class="alert alert-warning mt-2">
            <i class="fa fa-plane"></i> {{ $lang['user_profile']['on_vacation'] ?? 'This seller is currently on vacation.' }}
          </div>
          @endif
        </div>
      </div>

      {{-- About --}}
      <div class="card mb-4 rounded-0">
        <div class="card-body">
          <h5>{{ $lang['user_profile']['about'] ?? 'About' }}</h5>
          <p>{{ $seller_about }}</p>
        </div>
      </div>

      {{-- Languages --}}
      @if($seller_languages->count() > 0)
      <div class="card mb-4 rounded-0">
        <div class="card-body">
          <h5>{{ $lang['user_profile']['languages'] ?? 'Languages' }}</h5>
          <ul class="list-unstyled">
            @foreach($seller_languages as $sl)
            <li>{{ $sl->language_title }}</li>
            @endforeach
          </ul>
        </div>
      </div>
      @endif

      {{-- Skills --}}
      @if($seller_skills->count() > 0)
      <div class="card mb-4 rounded-0">
        <div class="card-body">
          <h5>{{ $lang['user_profile']['skills'] ?? 'Skills' }}</h5>
          @foreach($seller_skills as $sk)
          <span class="badge badge-light p-2 mr-1 mb-1">{{ $sk->skill_title }}</span>
          @endforeach
        </div>
      </div>
      @endif
    </div>

    <div class="col-md-8">
      <div class="row">
        <div class="col-md-12">
          <div class="card mt-4 mb-4 rounded-0">
            <div class="card-body">
              <h2>
                {{ str_replace('{user_name}', $seller_user_name, $lang['user_profile']['user_proposals'] ?? "{user_name}'s Proposals") }}
              </h2>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        @if($count_proposals == 0)
        <div class="col-md-12">
          <h3 class="text-center mb-5 p-2">
            <i class="fa fa-smile-o"></i> {{ $lang['user_profile']['no_proposals'] ?? 'No active proposals yet.' }}
          </h3>
        </div>
        @else
          @foreach($seller_proposals as $sp)
          @php
            $sp_level_meta = DB::table('seller_levels_meta')->where('level_id', $seller_level)->where('language_id', $sLang)->first();
            $sp_level_title = $sp_level_meta->title ?? '';
            $sp_reviews = DB::table('buyer_reviews')->where('proposal_id', $sp->proposal_id)->get();
            $sp_ratings = [];
            foreach($sp_reviews as $sr) $sp_ratings[] = $sr->buyer_rating;
            $sp_total = array_sum($sp_ratings);
            $sp_avg = count($sp_ratings) > 0 ? $sp_total / count($sp_ratings) : 0;
            $sp_rating = substr((string)$sp_avg, 0, 1);
            if(empty($sp_rating) || $sp_rating == 'N') $sp_rating = 0;
            $sp_packages = DB::table('proposal_packages')->where('proposal_id', $sp->proposal_id)->get();
            $sp_starting = $sp->proposal_price;
            if($sp_starting == 0 && $sp_packages->count() > 0) $sp_starting = $sp_packages->min('price') ?? 0;
          @endphp
          <div class="col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
            @include('legacy.partials.proposal-card', [
                'proposal' => $sp,
                'seller_user_name' => $seller_user_name,
                'seller_image' => $seller_image,
                'level_title' => $sp_level_title,
                'proposal_rating' => $sp_rating,
                'count_reviews' => count($sp_ratings),
                'starting_at' => $sp_starting,
            ])
          </div>
          @endforeach
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
