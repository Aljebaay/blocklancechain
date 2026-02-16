@php
use Illuminate\Support\Facades\DB;

$sLang = $siteLanguage ?? 1;
$langDir = $lang_dir ?? 'left';
$currentTag = $tag ?? '';

$tag_proposals = DB::select("SELECT DISTINCT proposal_seller_id FROM proposals WHERE proposal_tags LIKE ? AND proposal_status='active'", ['%'.$currentTag.'%']);
@endphp

<div class="card border-success mb-3">
  <div class="card-body pb-2 pt-3">
    <ul class="nav flex-column">
      <li class="nav-item checkbox checkbox-success">
        <label>
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

@php $countries = []; @endphp
<div class="card border-success mb-3">
  <div class="card-header bg-success">
    <h3 class="{{ $langDir == 'right' ? 'float-right' : 'float-left' }} text-white h5">{{ $lang['sidebar']['seller_country'] ?? 'Seller Country' }}</h3>
    <button class="btn btn-secondary btn-sm {{ $langDir == 'right' ? 'float-left' : 'float-right' }} clear_seller_country clearlink" onclick="clearCountry()">
      {{ $lang['sidebar']['clear_filter'] ?? 'Clear Filter' }}
    </button>
  </div>
  <div class="card-body">
    <ul class="nav flex-column">
    @foreach($tag_proposals as $row_p)
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

@php $cities = []; @endphp
<div class="card border-success mb-3 seller-cities d-none">
  <div class="card-header bg-success">
    <h3 class="{{ $langDir == 'right' ? 'float-right' : 'float-left' }} text-white h5">{{ $lang['sidebar']['seller_city'] ?? 'Seller City' }}</h3>
    <button class="btn btn-secondary btn-sm {{ $langDir == 'right' ? 'float-left' : 'float-right' }} clear_seller_city clearlink" onclick="clearCity()">
      {{ $lang['sidebar']['clear_filter'] ?? 'Clear Filter' }}
    </button>
  </div>
  <div class="card-body">
    <ul class="nav flex-column">
    @foreach($tag_proposals as $row_p)
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
  $dt_proposals = DB::select("SELECT DISTINCT delivery_id FROM proposals WHERE proposal_tags LIKE ? AND proposal_status='active'", ['%'.$currentTag.'%']);
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
        $dt_row = DB::table('delivery_times')->where('delivery_id', $row_p->delivery_id)->first();
        $dt_title = $dt_row->delivery_title ?? '';
      @endphp
      @if(!empty($dt_title))
      <li class="nav-item checkbox checkbox-success">
        <label>
        <input type="checkbox" value="{{ $row_p->delivery_id }}" class="get_delivery_time">
        <span> {{ $dt_title }} </span>
        </label>
      </li>
      @endif
      @endforeach
    </ul>
  </div>
</div>

@php
  $lv_proposals = DB::select("SELECT DISTINCT level_id FROM proposals WHERE proposal_tags LIKE ? AND proposal_status='active'", ['%'.$currentTag.'%']);
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
        $lv_meta = DB::table('seller_levels_meta')->where('level_id', $row_p->level_id)->where('language_id', $sLang)->first();
        $lv_title = $lv_meta->title ?? '';
      @endphp
      @if(!empty($lv_title))
      <li class="nav-item checkbox checkbox-primary">
        <label>
        <input type="checkbox" value="{{ $row_p->level_id }}" class="get_seller_level">
        <span> {{ $lv_title }} </span>
        </label>
      </li>
      @endif
      @endforeach
    </ul>
  </div>
</div>

@php
  $sl_proposals = DB::select("SELECT DISTINCT language_id FROM proposals WHERE not language_id='0' AND proposal_tags LIKE ? AND proposal_status='active'", ['%'.$currentTag.'%']);
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
        $sl_row = DB::table('seller_languages')->where('language_id', $row_p->language_id)->first();
        $sl_title = $sl_row->language_title ?? '';
      @endphp
      @if(!empty($sl_title))
      <li class="nav-item checkbox checkbox-primary">
        <label>
        <input type="checkbox" value="{{ $row_p->language_id }}" class="get_seller_language">
        <span> {{ $sl_title }} </span>
        </label>
      </li>
      @endif
      @endforeach
    </ul>
  </div>
</div>
