@php
    use Illuminate\Support\Facades\DB;
    $search_query = session('search_query', '');
    $s_value = "%{$search_query}%";
    $sLang = $siteLanguage ?? 1;
    $langDir = $lang_dir ?? 'left';
    $textAlign = $langDir == 'right' ? 'text-right' : '';
    $floatDir = $langDir == 'right' ? 'float-right' : 'float-left';
    $floatOpp = $langDir == 'right' ? 'float-left' : 'float-right';
@endphp

<div class="card border-success mb-3">
  <div class="card-body pb-2 pt-3 {{ $textAlign }}">
    <ul class="nav flex-column">
      <li class="nav-item checkbox checkbox-success">
        <label>
        <input type="checkbox" value="1" class="get_online_sellers">
        <span>{{ $lang['sidebar']['online_sellers'] ?? 'Online Sellers' }}</span>
        </label>
      </li>
    </ul>
  </div>
</div>

<div class="card border-success mb-3">
  <div class="card-body pb-2 pt-3 {{ $textAlign }}">
    <ul class="nav flex-column">
      <li class="nav-item checkbox checkbox-success">
        <label>
        <input type="checkbox" value="1" class="get_instant_delivery">
        <span>{{ $lang['sidebar']['instant_delivery'] ?? 'Instant Delivery' }}</span>
        </label>
      </li>
    </ul>
  </div>
</div>

<div class="card border-success mb-3">
  <div class="card-header bg-success">
    <h3 class="{{ $floatDir }} text-white h5">{{ $lang['sidebar']['sort_by']['title'] ?? 'Sort By' }}</h3>
  </div>
  <div class="card-body">
    <label class="checkcontainer">{{ $lang['sidebar']['sort_by']['new'] ?? 'Newest' }}
      <input type="radio" checked value="DESC" class="get_order" name="radio">
      <span class="checkmark"></span>
    </label>
    <label class="checkcontainer">{{ $lang['sidebar']['sort_by']['old'] ?? 'Oldest' }}
      <input type="radio" value="ASC" class="get_order" name="radio">
      <span class="checkmark"></span>
    </label>
  </div>
</div>

<div class="card border-success mb-3">
  <div class="card-header bg-success">
    <h3 class="{{ $floatDir }} text-white h5">{{ $lang['sidebar']['seller_country'] ?? 'Seller Country' }}</h3>
    <button class="btn btn-secondary btn-sm {{ $floatOpp }} clear_seller_country clearlink" onclick="clearCountry()">
      {{ $lang['sidebar']['clear_filter'] ?? 'Clear' }}
    </button>
  </div>
  <div class="card-body">
    <ul class="nav flex-column">
    @php
    $countries = [];
    $proposalSellers = DB::select("select DISTINCT proposal_seller_id from proposals where proposal_title like ? AND proposal_status='active'", [$s_value]);
    @endphp
    @foreach($proposalSellers as $ps)
    @php
    $seller = DB::table('sellers')->where('seller_id', $ps->proposal_seller_id)->first();
    $seller_country = $seller->seller_country ?? '';
    @endphp
    @if(!empty($seller_country) && !isset($countries[$seller_country]))
    @php $countries[$seller_country] = true; @endphp
    <li class="nav-item checkbox checkbox-success">
      <label>
      <input type="checkbox" value="{{ $seller_country }}" class="get_seller_country">
      <span>{{ $seller_country }}</span>
      </label>
    </li>
    @endif
    @endforeach
    </ul>
  </div>
</div>

<div class="card border-success mb-3 seller-cities d-none">
  <div class="card-header bg-success">
    <h3 class="{{ $floatDir }} text-white h5">{{ $lang['sidebar']['seller_city'] ?? 'Seller City' }}</h3>
    <button class="btn btn-secondary btn-sm {{ $floatOpp }} clear_seller_city clearlink" onclick="clearCity()">
      {{ $lang['sidebar']['clear_filter'] ?? 'Clear' }}
    </button>
  </div>
  <div class="card-body">
    <ul class="nav flex-column">
    @php
    $cities = [];
    $proposalSellers2 = DB::select("select DISTINCT proposal_seller_id from proposals where proposal_title like ? AND proposal_status='active'", [$s_value]);
    @endphp
    @foreach($proposalSellers2 as $ps)
    @php
    $seller = DB::table('sellers')->where('seller_id', $ps->proposal_seller_id)->first();
    $seller_country = $seller->seller_country ?? '';
    $seller_city = $seller->seller_city ?? '';
    @endphp
    @if(!empty($seller_city) && !isset($cities[$seller_city]))
    @php $cities[$seller_city] = true; @endphp
    <li class="nav-item checkbox checkbox-success" data-country="{{ $seller_country }}">
      <label>
        <input type="checkbox" value="{{ $seller_city }}" class="get_seller_city">
        <span>{{ $seller_city }}</span>
      </label>
    </li>
    @endif
    @endforeach
    </ul>
  </div>
</div>

<div class="card border-success mb-3">
  <div class="card-header bg-success">
    <h3 class="{{ $floatDir }} text-white h5">{{ $lang['sidebar']['categories'] ?? 'Categories' }}</h3>
    <button class="btn btn-secondary btn-sm {{ $floatOpp }} clear_cat_id clearlink" onclick="clearCat()">
    {{ $lang['sidebar']['clear_filter'] ?? 'Clear' }}
    </button>
  </div>
  <div class="card-body">
    <ul class="nav flex-column">
      @php
        $catProposals = DB::select("select DISTINCT proposal_cat_id from proposals where proposal_title like ? AND proposal_status='active'", [$s_value]);
      @endphp
      @foreach($catProposals as $cp)
      @php
        $catMeta = DB::table('cats_meta')->where('cat_id', $cp->proposal_cat_id)->where('language_id', $sLang)->first();
        $category_title = $catMeta->cat_title ?? '';
      @endphp
      @if(!empty($category_title))
      <li class="nav-item checkbox checkbox-success">
        <label>
        <input type="checkbox" value="{{ $cp->proposal_cat_id }}" class="get_cat_id">
        <span>{{ $category_title }}</span>
        </label>
      </li>
      @endif
      @endforeach
    </ul>
  </div>
</div>

<div class="card border-success mb-3">
  <div class="card-header bg-success">
    <h3 class="{{ $floatDir }} text-white h5">{{ $lang['sidebar']['delivery_time'] ?? 'Delivery Time' }}</h3>
    <button class="btn btn-secondary btn-sm {{ $floatOpp }} clear_delivery_time clearlink" onclick="clearDelivery()">
    {{ $lang['sidebar']['clear_filter'] ?? 'Clear' }}
    </button>
  </div>
  <div class="card-body">
    <ul class="nav flex-column">
      @php
        $delProposals = DB::select("select DISTINCT delivery_id from proposals where proposal_title like ? AND proposal_status='active'", [$s_value]);
      @endphp
      @foreach($delProposals as $dp)
      @php
        $deliveryTime = DB::table('delivery_times')->where('delivery_id', $dp->delivery_id)->first();
        $delivery_title = $deliveryTime->delivery_title ?? '';
      @endphp
      @if(!empty($delivery_title))
      <li class="nav-item checkbox checkbox-success">
        <label>
        <input type="checkbox" value="{{ $dp->delivery_id }}" class="get_delivery_time">
        <span>{{ $delivery_title }}</span>
        </label>
      </li>
      @endif
      @endforeach
    </ul>
  </div>
</div>

<div class="card border-success mb-3">
  <div class="card-header bg-success">
    <h3 class="{{ $floatDir }} text-white h5">{{ $lang['sidebar']['seller_level'] ?? 'Seller Level' }}</h3>
    <button class="btn btn-secondary btn-sm {{ $floatOpp }} clear_seller_level clearlink" onclick="clearLevel()">
    {{ $lang['sidebar']['clear_filter'] ?? 'Clear' }}
    </button>
  </div>
  <div class="card-body">
    <ul class="nav flex-column">
      @php
        $lvlProposals = DB::select("select DISTINCT level_id from proposals where proposal_title like ? AND proposal_status='active'", [$s_value]);
      @endphp
      @foreach($lvlProposals as $lp)
      @php
        $levelMeta = DB::table('seller_levels_meta')->where('level_id', $lp->level_id)->where('language_id', $sLang)->first();
        $level_title = $levelMeta->title ?? '';
      @endphp
      @if(!empty($level_title))
      <li class="nav-item checkbox checkbox-success">
        <label>
        <input type="checkbox" value="{{ $lp->level_id }}" class="get_seller_level">
        <span>{{ $level_title }}</span>
        </label>
      </li>
      @endif
      @endforeach
    </ul>
  </div>
</div>

<div class="card border-success mb-3">
  <div class="card-header bg-success">
    <h3 class="{{ $floatDir }} text-white h5">{{ $lang['sidebar']['seller_lang'] ?? 'Seller Language' }}</h3>
    <button class="btn btn-secondary btn-sm {{ $floatOpp }} clear_seller_language clearlink" onclick="clearLanguage()">
    {{ $lang['sidebar']['clear_filter'] ?? 'Clear' }}
    </button>
  </div>
  <div class="card-body">
    <ul class="nav flex-column">
      @php
        $langProposals = DB::select("select DISTINCT language_id from proposals where not language_id='0' and proposal_title like ? AND proposal_status='active'", [$s_value]);
      @endphp
      @foreach($langProposals as $lngp)
      @php
        $sellerLang = DB::table('seller_languages')->where('language_id', $lngp->language_id)->first();
        $language_title = $sellerLang->language_title ?? '';
      @endphp
      @if(!empty($language_title))
      <li class="nav-item checkbox checkbox-success">
        <label>
        <input type="checkbox" value="{{ $lngp->language_id }}" class="get_seller_language">
        <span>{{ $language_title }}</span>
        </label>
      </li>
      @endif
      @endforeach
    </ul>
  </div>
</div>
