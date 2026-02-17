@extends('legacy.layout')

@section('extra_css')
	<link href="{{ $site_url }}/styles/owl.carousel.css" rel="stylesheet">
	<link href="{{ $site_url }}/styles/owl.theme.default.css" rel="stylesheet">
	<link href="{{ $site_url }}/styles/animate.css" rel="stylesheet">
	@if(($row_general_settings->knowledge_bank ?? 'no') === 'yes')
		<link href="{{ $site_url }}/styles/knowledge_bank.css" rel="stylesheet">
	@endif
@endsection

@section('content')
@php
    use Illuminate\Support\Facades\DB;
    $ld = $legacyData ?? null;
    $langDir = $lang_dir ?? 'left';
    $loginSellerId = $seller_id ?? 0;
    $loginSellerName = $login_seller_name ?? '';
    $loginUserName = $login_user_name ?? '';
    $loginSellerOffers = $login_seller_offers ?? '0';
@endphp

<style>
  .carousel-item img,
  .carousel-item video{
    height: auto !important;
    background-color: black;
  }
</style>
<div class="container mt-3">
  <!-- Container starts -->
  <div class="row">
    <div class="col-md-3 {{ $langDir == 'right' ? 'order-2 order-sm-1' : '' }}">
      @include('legacy.partials.user-home-sidebar')
    </div>
    <div class="col-md-9 {{ $langDir == 'right' ? 'order-1 order-sm-2' : '' }}">
      <div id="demo3" class="carousel slide">
        <ul class="carousel-indicators">
          @foreach($auth_slides ?? [] as $idx => $slide)
          <li data-target="#demo3" data-slide-to="{{ $idx }}" {{ $idx == 0 ? 'class=active' : '' }}></li>
          @endforeach
        </ul>
        <div class="carousel-inner">
          @foreach($auth_slides ?? [] as $idx => $slide)
          @php
              $slideImage = $ld ? $ld->getImageUrl('slider', $slide->slide_image) : '';
              $sExtension = pathinfo($slideImage, PATHINFO_EXTENSION);
          @endphp
          <div class="carousel-item {{ $idx === 0 ? 'active' : '' }}">
              @if(in_array($sExtension, ['mp4', 'webm', 'ogg']))
                <video class="img-fluid w-100" controls muted {{ $idx === 0 ? 'autoplay' : '' }}>
                  <source src="{{ $slideImage }}" type="video/mp4">
                </video>
              @else
                <a href="{{ $slide->slide_url }}"> <img src="{{ $slideImage }}" class="img-fluid"> </a>
              @endif
              <div class="carousel-caption d-lg-block d-md-block d-none {{ $langDir == 'right' ? 'text-right' : '' }}"/>
                <h3>{{ $slide->slide_name }}</h3>
                <p>{{ $slide->slide_desc }}</p>
              </div>
          </div>
          @endforeach
        </div>
      </div>
      <div class="row mt-4 mb-3">
        <div class="col-md-12">
          <h2 class="{{ $langDir == 'right' ? 'float-right' : 'float-left' }}">{{ $lang['user_home']['featured_proposals'] ?? 'Featured Proposals' }}</h2>
          <button onclick="location.href='featured_proposals'" class="{{ $langDir == 'right' ? 'float-left' : 'float-right' }} btn btn-success">{{ $lang['view_all'] ?? 'View All' }}</button>
        </div>
      </div>
      <div class="row">
        @if(($auth_featured_proposals ?? collect())->isEmpty())
            <div class='col-md-12 text-center'>
            <p class='text-muted'><i class='fa fa-frown-o'></i> {{ $lang['user_home']['no_featured_proposals'] ?? 'No featured proposals available.' }} </p>
            </div>
        @else
          @foreach($auth_featured_proposals as $proposal)
          <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-3 pr-lg-1">
            @include('legacy.partials.proposal-card', ['proposal' => $proposal])
          </div>
          @endforeach
        @endif
      </div>
      <!-- If You have no gigs, show random gigs on homepage -->
      <div class="row mb-3">
        <div class="col-md-12">
          <h2 class="{{ $langDir == 'right' ? 'float-right' : 'float-left' }}">{{ $lang['user_home']['top_proposals'] ?? 'Top Proposals' }}</h2>
          <button onclick="location.href='top_proposals'" class="{{ $langDir == 'right' ? 'float-left' : 'float-right' }} btn btn-success">{{ $lang['view_all'] ?? 'View All' }}</button>
        </div>
      </div>
      <div class="row">
        @if(($auth_top_proposals ?? collect())->isEmpty())
            <div class='col-md-12 text-center'>
            <p class='text-muted'><i class='fa fa-frown-o'></i> {{ $lang['user_home']['no_top_proposals'] ?? 'No top proposals available.' }} </p>
            </div>
        @else
          @foreach($auth_top_proposals as $proposal)
          <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-3 pr-lg-1">
            @include('legacy.partials.proposal-card', ['proposal' => $proposal])
          </div>
          @endforeach
        @endif
      </div>
      <!-- If You have no gigs, show random gigs on homepage -->
      <div class="row mb-3">
        <div class="col-md-12">
          <h2 class="pl-0 pr-0 ml-0 mr-0 {{ $langDir == 'right' ? 'float-right' : 'float-left' }}">{{ $lang['user_home']['random_proposals'] ?? 'Random Proposals' }}</h2>
          <button onclick="location.href='random_proposals'" class="{{ $langDir == 'right' ? 'float-left' : 'float-right' }} btn btn-success">{{ $lang['view_all'] ?? 'View All' }}</button>
        </div>
      </div>
      <div class="row">
        @if(($auth_random_proposals ?? collect())->isEmpty())
            <div class='col-md-12 text-center'>
            <p class='text-muted'><i class='fa fa-frown-o'></i> {{ $lang['user_home']['no_random_proposals'] ?? 'No proposals available.' }} </p>
            </div>
        @else
          @foreach($auth_random_proposals as $proposal)
          <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-3 pr-lg-1">
            @include('legacy.partials.proposal-card', ['proposal' => $proposal])
          </div>
          @endforeach
        @endif
      </div>
      <br>
      <!-- If You have no gigs, show random gigs on homepage Ends -->
      @php
        $countActiveProposals = $count_active_proposals ?? 0;
        $requestsList = $auth_buyer_requests ?? collect();
        // Count requests where seller hasn't sent an offer yet
        $requestsCount = 0;
        foreach($requestsList as $rq) {
            $co = DB::table('send_offers')->where('request_id', $rq->request_id)->where('sender_id', $loginSellerId)->count();
            if ($co == 0) $requestsCount++;
        }
      @endphp
      @if($requestsCount != 0 && !empty($countActiveProposals))
      <div class="row mt-2 mb-3">
        <div class="col-md-12">
          <h2 class="{{ $langDir == 'right' ? 'float-right' : 'float-left' }}">{{ $lang['user_home']['recent_requests'] ?? 'Recent Requests' }}</h2>
          <button type="button" onclick="location.href='requests/buyer_requests'" class="{{ $langDir == 'right' ? 'float-left' : 'float-right' }} btn btn-success">{{ $lang['view_all'] ?? 'View All' }}</button>
        </div>
      </div>
      <div class="row buyer-requests">
        <div class="col-md-12">
          <div class="table-responsive box-table">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>Request Message</th>
                  <th>Offers</th>
                  <th>Duration</th>
                  <th>Budget</th>
                </tr>
              </thead>
              <tbody>
                @foreach($requestsList as $rq)
                @php
                    $countOffers = DB::table('send_offers')->where('request_id', $rq->request_id)->where('sender_id', $loginSellerId)->count();
                    if ($countOffers > 0) continue;
                    $rqSeller = DB::table('sellers')->where('seller_id', $rq->seller_id)->first();
                    $rqSellerUserName = $rqSeller->seller_user_name ?? '';
                    $rqSellerImage = $ld ? $ld->getImageUrl2('sellers', 'seller_image', $rqSeller->seller_image ?? '') : '';
                    $countSendOffers = DB::table('send_offers')->where('request_id', $rq->request_id)->count();
                    $rqBudgetFormatted = '';
                    if (!empty($rq->request_budget)) {
                        $rqBudgetFormatted = $ld ? $ld->showPrice($rq->request_budget) : '$' . number_format((float)$rq->request_budget, 2);
                    }
                @endphp
                <tr id="request_tr_{{ $rq->request_id }}">
                  <td>
                    <a href="{{ $rqSellerUserName }}" target="_blank">
                      @if(!empty($rqSellerImage))
                        <img src="{{ $rqSellerImage }}" class="request-img rounded-circle">
                      @else
                        <img src="empty-image.png" class="request-img rounded-circle">
                      @endif
                    </a>
                    <div class="request-description">
                      <h6>
                        <a href="{{ $rqSellerUserName }}" target="_blank">
                          {{ ucfirst($rqSellerUserName) }}
                        </a>
                      </h6>
                      <h6 class="text-success">{{ $rq->request_title }}</h6>
                      <p class="lead">{{ $rq->request_description }} </p>
                      @if(!empty($rq->request_file))
                      @php $rqFileUrl = $ld ? $ld->getImageUrl('buyer_requests', $rq->request_file) : ''; @endphp
                      <a href="{{ $rqFileUrl }}" download>
                        <i class="fa fa-arrow-circle-down"> </i> {{ $rq->request_file }}
                      </a>
                      @endif
                    </div>
                  </td>
                  <td>{{ $countSendOffers }}</td>
                  <td>{{ $rq->delivery_time }}</td>
                  <td class="text-success">
                    @if(!empty($rq->request_budget))
                    {!! $rqBudgetFormatted !!}
                    @else ----- @endif
                    <br>
                    @if($loginSellerOffers == '0')
                    <button class="btn btn-success btn-sm mt-4 send_button_{{ $rq->request_id }}" data-toggle="modal" data-target="#quota-finish">
                      {{ $lang['button']['send_an_offer'] ?? 'Send an Offer' }}
                    </button>
                    @else
                    <button class="btn btn-success btn-sm mt-4 send_button_{{ $rq->request_id }}">
                      {{ $lang['button']['send_offer'] ?? 'Send Offer' }}
                    </button>
                    @endif
                  </td>
                  @if($loginSellerOffers != '0')
                  <script type="text/javascript">
                    $(".send_button_{{ $rq->request_id }}").click(function(){
                     request_id = "{{ $rq->request_id }}";
                      $.ajax({
                       method: "POST",
                         url: "requests/send_offer_modal",
                           data: {request_id: request_id }
                        }).done(function(data){
                           $(".append-modal").html(data);
                        });
                      });
                  </script>
                  @endif
                </tr>
                @endforeach
              </tbody>
            </table>
            @if($requestsCount == 0)
                <center><h4 class='pb-2 pt-2'>{{ $lang['user_home']['no_recent_requests'] ?? 'No recent requests.' }}</h4></center>
            @else
            <center>
              <a href="requests/buyer_requests" class="btn btn-success btn-lg mb-3">
                <i class="fa fa-spinner"></i> {{ $lang['button']['load_more'] ?? 'Load More' }}
              </a>
            </center>
            @endif
          </div>
        </div>
      </div>
      @endif
    </div>
  </div>
</div>
<!-- Container ends -->
<br>
<div class="append-modal"></div>
<div id="quota-finish" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title h5"><i class="fa fa-frown-o fa-move-up"></i> Request Quota Reached</h5>
        <button class="close" data-dismiss="modal"> &times; </button>
      </div>
      <div class="modal-body">
        <center>
        <h5>You can only send a max of 10 offers per day. Today you've maxed out. Try again tomorrow. </h5>
        </center>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function(){

  var slider = $('#demo3').carousel({
    interval: 4000
  });

  var active = $(".carousel-item.active").find("video");
  var active_length = active.length;

  if(active_length == 1){
    slider.carousel('pause');
    $(".carousel-indicators").css({"bottom": "75px"});
  }

  $("#demo3").on('slide.bs.carousel', function(event){
    var eq = event.to;
    var video = $(event.relatedTarget).find("video");
    if(video.length == 1){
      slider.carousel('pause');
      $(".carousel-indicators").css({"bottom": "75px"});
      video.trigger('play');
    }else{
      $(".carousel-indicators").css({"bottom": "20px"});
    }
  });

  $('video').on('ended',function(){
    slider.carousel({'pause': false});
  });

});

</script>
@endsection
