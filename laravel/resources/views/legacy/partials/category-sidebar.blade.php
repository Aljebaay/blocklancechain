@php
use Illuminate\Support\Facades\DB;

$sLang = $siteLanguage ?? 1;
$langDir = $lang_dir ?? 'left';
$session_cat_id = $active_cat_id ?? null;
$session_cat_child_id = $active_child_id ?? null;
$child_parent_id = null;

if($session_cat_child_id) {
    $child_row = DB::table('categories_children')->where('child_id', $session_cat_child_id)->first();
    if($child_row) $child_parent_id = $child_row->child_parent_id;
}

$online_sellers = [];
$instant_delivery = request('instant_delivery.0', 0);
$order_by = request('order.0', 'DESC');
$sellerCountry = [];
$sellerCity = [];
$delivery_time = [];
$seller_level = [];
$seller_language = [];
@endphp

<div class="card border-success mb-3">
  <div class="card-header bg-success">
    <h3 class="{{ $langDir == 'right' ? 'float-right' : 'float-left' }} h5 text-white">{{ $lang['sidebar']['categories'] ?? 'Categories' }}</h3>
  </div>
  <div class="card-body">
    <ul class="nav flex-column" id="proposal_category">
      @php
        $all_cats = DB::table('categories')->get();
      @endphp
      @foreach($all_cats as $row_cats)
      @php
        $sid_cat_id = $row_cats->cat_id;
        $sid_cat_url = $row_cats->cat_url;
        $cat_meta = DB::table('cats_meta')->where('cat_id', $sid_cat_id)->where('language_id', $sLang)->first();
        $sid_cat_title = $cat_meta->cat_title ?? '';
      @endphp
      <li class="nav-item">
        <span class="nav-link {{ $sid_cat_id == $session_cat_id ? 'active' : '' }}{{ $sid_cat_id == $child_parent_id ? 'active' : '' }}">     
        <a href="{{ $site_url }}/categories/{{ rawurlencode($sid_cat_url) }}" class="text-success"> {{ $sid_cat_title }}</a> 
        <a class="h5 text-success float-right" data-toggle="collapse" data-target="#cat_{{ $sid_cat_id }}">
        <i class="fa fa-arrow-circle-down"></i>
        </a>
        </span>
        <ul id="cat_{{ $sid_cat_id }}" class="collapse">
          @php
            $child_cats = DB::table('categories_children')->where('child_parent_id', $sid_cat_id)->get();
          @endphp
          @foreach($child_cats as $row_child_cat)
          @php
            $sid_child_id = $row_child_cat->child_id;
            $sid_child_url = $row_child_cat->child_url;
            $child_meta = DB::table('child_cats_meta')->where('child_id', $sid_child_id)->where('language_id', $sLang)->first();
            $sid_child_title = $child_meta->child_title ?? '';
          @endphp
          @if(!empty($sid_child_title))
          <li>
            <a class="nav-link text-success {{ $sid_child_id == $session_cat_child_id ? 'active' : '' }}" href="{{ $site_url }}/categories/{{ rawurlencode($sid_cat_url) }}/{{ rawurlencode($sid_child_url) }}">
            {{ $sid_child_title }}
            </a>
          </li>
          @endif
          @endforeach
        </ul>
      </li>
      @endforeach
    </ul>
  </div>
</div>
<div class="card border-success mb-3">
  <div class="card-body pb-2 pt-2">
    <ul class="nav flex-column">
      <li class="nav-item checkbox checkbox-success">
        <label class="pt-2">
        <input type="checkbox" value="1" class="get_online_sellers">
        <span>{{ $lang['sidebar']['online_sellers'] ?? 'Show Online Sellers' }}</span>
        </label>
      </li>
    </ul>
  </div>
</div>

<div class="card border-success mb-3">
  <div class="card-body pb-2 pt-3 {{ $langDir == 'right' ? 'text-right' : '' }}">
    <ul class="nav flex-column">
      <li class="nav-item checkbox checkbox-success">
        <label>
        <input type="checkbox" value="1" class="get_instant_delivery">
        <span>{{ $lang['sidebar']['instant_delivery'] ?? 'Show Instant Delivery Proposals' }}</span>
        </label>
      </li>
    </ul>
  </div>
</div>

<div class="card border-success mb-3">
  <div class="card-header bg-success">
    <h3 class="{{ $langDir == 'right' ? 'float-right' : 'float-left' }} text-white h5">{{ $lang['sidebar']['sort_by']['title'] ?? 'Sort By' }}</h3>
  </div>
  <div class="card-body">
    <label class="checkcontainer">{{ $lang['sidebar']['sort_by']['new'] ?? 'New' }}
      <input type="radio" checked value="DESC" class="get_order" name="radio">
      <span class="checkmark"></span>
    </label>

    <label class="checkcontainer">{{ $lang['sidebar']['sort_by']['old'] ?? 'Old' }}
      <input type="radio" value="ASC" class="get_order" name="radio">
      <span class="checkmark"></span>
    </label>
  </div>
</div>


@php
  $countries = [];
  if($session_cat_id) {
      $cat_proposals = DB::select("select DISTINCT proposal_seller_id from proposals where proposal_cat_id=? AND proposal_status='active'", [$session_cat_id]);
  } elseif($session_cat_child_id) {
      $cat_proposals = DB::select("select DISTINCT proposal_seller_id from proposals where proposal_child_id=? AND proposal_status='active'", [$session_cat_child_id]);
  } else {
      $cat_proposals = [];
  }
@endphp
<div class="card border-success mb-3">
  <div class="card-header bg-success">
    <h3 class="{{ $langDir == 'right' ? 'float-right' : 'float-left' }} text-white h5">{{ $lang["sidebar"]["seller_country"] ?? 'Seller Country' }}</h3>
    <button class="btn btn-secondary btn-sm {{ $langDir == 'right' ? 'float-left' : 'float-right' }} clear_seller_country clearlink" onclick="clearCountry()">
      {{ $lang['sidebar']['clear_filter'] ?? 'Clear Filter' }}
    </button>
  </div>
  <div class="card-body">
    <ul class="nav flex-column">
    @foreach($cat_proposals as $row_p)
    @php
      $sel = DB::table('sellers')->where('seller_id', $row_p->proposal_seller_id)->first();
      $sel_country = $sel->seller_country ?? '';
      if(!empty($sel_country) && !isset($countries[$sel_country])) {
          $countries[$sel_country] = $sel_country;
    @endphp
    <li class="nav-item checkbox checkbox-success">
      <label>
      <input type="checkbox" value="{{ $sel_country }}" class="get_seller_country">
      <span>{{ $sel_country }}</span>
      </label>
    </li>
    @php } @endphp
    @endforeach
    </ul>
  </div>
</div>


@php
  $cities = [];
@endphp
<div class="card border-success mb-3 seller-cities d-none">
  <div class="card-header bg-success">
    <h3 class="{{ $langDir == 'right' ? 'float-right' : 'float-left' }} text-white h5">{{ $lang["sidebar"]["seller_city"] ?? 'Seller City' }}</h3>
    <button class="btn btn-secondary btn-sm {{ $langDir == 'right' ? 'float-left' : 'float-right' }} clear_seller_city clearlink" onclick="clearCity()">
      {{ $lang['sidebar']['clear_filter'] ?? 'Clear Filter' }}
    </button>
  </div>
  <div class="card-body">
    <ul class="nav flex-column">
    @foreach($cat_proposals as $row_p)
    @php
      $sel = DB::table('sellers')->where('seller_id', $row_p->proposal_seller_id)->first();
      $sel_country = $sel->seller_country ?? '';
      $sel_city = $sel->seller_city ?? '';
      if(!empty($sel_city) && !isset($cities[$sel_city])) {
          $cities[$sel_city] = $sel_city;
    @endphp
    <li class="nav-item checkbox checkbox-success" data-country="{{ $sel_country }}">
      <label>
      <input type="checkbox" value="{{ $sel_city }}" class="get_seller_city">
      <span>{{ $sel_city }}</span>
      </label>
    </li>
    @php } @endphp
    @endforeach
    </ul>
  </div>
</div>


@php
  $delivery_times_list = [];
  if($session_cat_id) {
      $dt_proposals = DB::select("select DISTINCT delivery_id from proposals where proposal_cat_id=? AND proposal_status='active'", [$session_cat_id]);
  } elseif($session_cat_child_id) {
      $dt_proposals = DB::select("select DISTINCT delivery_id from proposals where proposal_child_id=? AND proposal_status='active'", [$session_cat_child_id]);
  } else {
      $dt_proposals = [];
  }
@endphp
<div class="card border-success mb-3">
  <div class="card-header bg-success">
    <h3 class="{{ $langDir == 'right' ? 'float-right' : 'float-left' }} text-white h5">{{ $lang['sidebar']['delivery_time'] ?? 'Delivery Time' }}</h3>
    <button class="btn btn-secondary btn-sm {{ $langDir == 'right' ? 'float-left' : 'float-right' }} clear_delivery_time clearlink" onclick="clearDelivery()">
    <i class="fa fa-times-circle"></i> Clear Filter
    </button>
  </div>
  <div class="card-body">
    <ul class="nav flex-column">
      @foreach($dt_proposals as $row_p)
      @php
        $dt_id = $row_p->delivery_id;
        $dt_row = DB::table('delivery_times')->where('delivery_id', $dt_id)->first();
        $dt_title = $dt_row->delivery_title ?? '';
      @endphp
      @if(!empty($dt_title))
      <li class="nav-item checkbox checkbox-success">
        <label>
        <input type="checkbox" value="{{ $dt_id }}" class="get_delivery_time">
        <span> {{ $dt_title }} </span>
        </label>
      </li>
      @endif
      @endforeach
    </ul>
  </div>
</div>
@php
  if($session_cat_id) {
      $lv_proposals = DB::select("select DISTINCT level_id from proposals where proposal_cat_id=? AND proposal_status='active'", [$session_cat_id]);
  } elseif($session_cat_child_id) {
      $lv_proposals = DB::select("select DISTINCT level_id from proposals where proposal_child_id=? AND proposal_status='active'", [$session_cat_child_id]);
  } else {
      $lv_proposals = [];
  }
@endphp
<div class="card border-success mb-3">
  <div class="card-header bg-success">
    <h3 class="{{ $langDir == 'right' ? 'float-right' : 'float-left' }} text-white h5">{{ $lang['sidebar']['seller_level'] ?? 'Seller Level' }}</h3>
    <button class="btn btn-secondary btn-sm {{ $langDir == 'right' ? 'float-left' : 'float-right' }} clear_seller_level clearlink" onclick="clearLevel()">
    <i class="fa fa-times-circle"></i> Clear Filter
    </button>
  </div>
  <div class="card-body">
    <ul class="nav flex-column">
      @foreach($lv_proposals as $row_p)
      @php
        $lv_id = $row_p->level_id;
        $lv_meta = DB::table('seller_levels_meta')->where('level_id', $lv_id)->where('language_id', $sLang)->first();
        $lv_title = $lv_meta->title ?? '';
      @endphp
      @if(!empty($lv_title))
      <li class="nav-item checkbox checkbox-primary">
        <label>
        <input type="checkbox" value="{{ $lv_id }}" class="get_seller_level">
        <span> {{ $lv_title }} </span>
        </label>
      </li>
      @endif
      @endforeach
    </ul>
  </div>
</div>
@php
  if($session_cat_id) {
      $sl_proposals = DB::select("select DISTINCT language_id from proposals where not language_id='0' and proposal_cat_id=? AND proposal_status='active'", [$session_cat_id]);
  } elseif($session_cat_child_id) {
      $sl_proposals = DB::select("select DISTINCT language_id from proposals where not language_id='0' and proposal_child_id=? AND proposal_status='active'", [$session_cat_child_id]);
  } else {
      $sl_proposals = [];
  }
@endphp
<div class="card border-success mb-3">
  <div class="card-header bg-success">
    <h2 class="float-left text-white h5">{{ $lang['sidebar']['seller_lang'] ?? 'Seller Lang' }}</h2>
    <button class="btn btn-secondary btn-sm {{ $langDir == 'right' ? 'float-left' : 'float-right' }} clear_seller_language clearlink" onclick="clearLanguage()">
      <i class="fa fa-times-circle"></i> Clear Filter
    </button>
  </div>
  <div class="card-body">
    <ul class="nav flex-column">
      @foreach($sl_proposals as $row_p)
      @php
        $sl_id = $row_p->language_id;
        $sl_row = DB::table('seller_languages')->where('language_id', $sl_id)->first();
        $sl_title = $sl_row->language_title ?? '';
      @endphp
      @if(!empty($sl_title))
      <li class="nav-item checkbox checkbox-primary">
        <label>
        <input type="checkbox" value="{{ $sl_id }}" class="get_seller_language">
        <span> {{ $sl_title }} </span>
        </label>
      </li>
      @endif
      @endforeach
    </ul>
  </div>
</div>
