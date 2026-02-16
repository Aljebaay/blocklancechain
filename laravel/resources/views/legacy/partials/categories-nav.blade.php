@php
    use Illuminate\Support\Facades\DB;
    $langDir = $lang_dir ?? 'left';
    $sLang = $siteLanguage ?? 1;
    $isLoggedIn = session()->has('seller_user_name');

    $orderClause = $langDir === 'right' ? 'order by 1 DESC' : '';

    $navCats = DB::select("select * from categories where cat_featured='yes' {$orderClause} LIMIT 0,9");
    $catCount = DB::table('categories')->where('cat_featured', 'yes')->count();
@endphp

<div data-ui="cat-nav" id="desktop-category-nav" class="ui-toolkit cat-nav ">
  <div class="bg-white bg-transparent-homepage-experiment bb-xs-1 hide-xs hide-sm hide-md">
    <div class="col-group body-max-width">
      <ul class="col-xs-12 body-max-width display-flex-xs justify-content-space-between" role="menubar" data-ui="top-nav-category-list">
        @foreach($navCats as $navCat)
        @php
            $catMeta = DB::table('cats_meta')->where('cat_id', $navCat->cat_id)->where('language_id', $sLang)->first();
            $catTitle = $catMeta->cat_title ?? '';
        @endphp
        <li class="top-nav-item pt-xs-1 pb-xs-1 pl-xs-2 pr-xs-2 display-flex-xs align-items-center text-center" 
          data-linkable="true" data-ui="top-nav-category-link" data-node-id="c-{{ $navCat->cat_id }}">
          <a href="{{ $site_url }}/categories/category.php?cat_url={{ rawurlencode($navCat->cat_url) }}">
          {{ $catTitle }}
          </a>
        </li>
        @endforeach

        @if($catCount > 10)
        <li class="top-nav-item pt-xs-1 pb-xs-1 pl-xs-2 pr-xs-2 display-flex-xs align-items-center text-center" 
          data-linkable="true" data-ui="top-nav-category-link" data-node-id="c-more">
          <a href="#">{{ $lang['more'] ?? 'More' }}</a>
        </li>
        @else
        @php
            $extraCats = DB::select("select * from categories where cat_featured='yes' {$orderClause} LIMIT 9,1");
        @endphp
        @foreach($extraCats as $extraCat)
        @php
            $catMeta = DB::table('cats_meta')->where('cat_id', $extraCat->cat_id)->where('language_id', $sLang)->first();
            $catTitle = $catMeta->cat_title ?? '';
        @endphp
        <li class="top-nav-item pt-xs-1 pb-xs-1 pl-xs-2 pr-xs-2 display-flex-xs align-items-center text-center" 
          data-linkable="true" data-ui="top-nav-category-link" data-node-id="c-{{ $extraCat->cat_id }}">
          <a href="{{ $site_url }}/categories/category.php?cat_url={{ rawurlencode($extraCat->cat_url) }}">
          {{ $catTitle }}
          </a>
        </li>
        @endforeach
        @endif

      </ul>
    </div>
  </div>

  <div class="position-absolute col-xs-12 col-centered z-index-4">
    <div>
      @php
          $dropdownCats = DB::select("select * from categories where cat_featured='yes' {$orderClause} LIMIT 0,10");
      @endphp
      @foreach($dropdownCats as $ddCat)
      @php
          $ddChildCount = DB::table('categories_children')->where('child_parent_id', $ddCat->cat_id)->count();
      @endphp
      @if($ddChildCount > 0)
      <div class="body-sub-width vertical-align-top sub-nav-container bg-white overflow-hidden bl-xs-1 bb-xs-1 br-xs-1 catnav-mott-control display-none" data-ui="sub-nav" aria-hidden="true" data-node-id="c-{{ $ddCat->cat_id }}">
        <div class="width-full display-flex-xs">
          <ul class="list-unstyled display-inline-block col-xs-3 p-xs-3 pl-xs-5" role="presentation">
            @php
              $childCats0 = DB::select("select * from categories_children where child_parent_id='{$ddCat->cat_id}' LIMIT 0,10");
            @endphp
            @foreach($childCats0 as $child)
            @php
              $childMeta = DB::table('child_cats_meta')->where('child_id', $child->child_id)->where('language_id', $sLang)->first();
              $childTitle = $childMeta->child_title ?? '';
            @endphp
            <li>
              <a class="display-block text-gray text-body-larger pt-xs-1" href="{{ $site_url }}/categories/category.php?cat_url={{ rawurlencode($ddCat->cat_url) }}&cat_child_url={{ rawurlencode($child->child_url) }}">
              {{ $childTitle }}
              </a>
            </li>
            @endforeach
          </ul>
          <ul class="list-unstyled display-inline-block col-xs-3 p-xs-3 pl-xs-5" role="presentation">
            @php
              $childCats1 = DB::select("select * from categories_children where child_parent_id='{$ddCat->cat_id}' LIMIT 10,10");
            @endphp
            @foreach($childCats1 as $child)
            @php
              $childMeta = DB::table('child_cats_meta')->where('child_id', $child->child_id)->where('language_id', $sLang)->first();
              $childTitle = $childMeta->child_title ?? '';
            @endphp
            <li>
              <a class="display-block text-gray text-body-larger pt-xs-1" href="{{ $site_url }}/categories/category.php?cat_url={{ rawurlencode($ddCat->cat_url) }}&cat_child_url={{ rawurlencode($child->child_url) }}">
                {{ $childTitle }}
              </a>
            </li>
            @endforeach
          </ul>
          <ul class="list-unstyled display-inline-block col-xs-3 p-xs-3 pl-xs-5" role="presentation">
            @php
              $childCats2 = DB::select("select * from categories_children where child_parent_id='{$ddCat->cat_id}' LIMIT 20,10");
            @endphp
            @foreach($childCats2 as $child)
            @php
              $childMeta = DB::table('child_cats_meta')->where('child_id', $child->child_id)->where('language_id', $sLang)->first();
              $childTitle = $childMeta->child_title ?? '';
            @endphp
            <li>
              <a class="display-block text-gray text-body-larger pt-xs-1" href="{{ $site_url }}/categories/category.php?cat_url={{ rawurlencode($ddCat->cat_url) }}&cat_child_url={{ rawurlencode($child->child_url) }}">
                {{ $childTitle }}
              </a>
            </li>
            @endforeach
          </ul>
          <ul class="list-unstyled display-inline-block col-xs-3 p-xs-3 pl-xs-5" role="presentation">
            @php
              $childCats3 = DB::select("select * from categories_children where child_parent_id='{$ddCat->cat_id}' LIMIT 30,10");
            @endphp
            @foreach($childCats3 as $child)
            @php
              $childMeta = DB::table('child_cats_meta')->where('child_id', $child->child_id)->where('language_id', $sLang)->first();
              $childTitle = $childMeta->child_title ?? '';
            @endphp
            <li>
              <a class="display-block text-gray text-body-larger pt-xs-1" href="{{ $site_url }}/categories/category.php?cat_url={{ rawurlencode($ddCat->cat_url) }}&cat_child_url={{ rawurlencode($child->child_url) }}">
                {{ $childTitle }}
              </a>
            </li>
            @endforeach
          </ul>
        </div>
      </div>
      @endif
      @endforeach

      <div class="body-sub-width vertical-align-top sub-nav-container bg-white overflow-hidden bl-xs-1 bb-xs-1 br-xs-1 catnav-mott-control display-none" data-ui="sub-nav" aria-hidden="true" data-node-id="c-more">
        <div class="width-full display-flex-xs">
          <ul class="list-unstyled display-inline-block col-xs-3 p-xs-3 pl-xs-5" role="presentation">
            @php
              $moreCats1 = DB::select("select * from categories where cat_featured='yes' LIMIT 9,19");
            @endphp
            @foreach($moreCats1 as $mCat)
            @php
              $mMeta = DB::table('cats_meta')->where('cat_id', $mCat->cat_id)->where('language_id', $sLang)->first();
              $mTitle = $mMeta->cat_title ?? '';
            @endphp
            <li>
              <a class="display-block text-gray text-body-larger pt-xs-1" href="{{ $site_url }}/categories/category.php?cat_url={{ rawurlencode($mCat->cat_url) }}">
                {{ $mTitle }}
              </a>
            </li>
            @endforeach
          </ul>
          <ul class="list-unstyled display-inline-block col-xs-3 p-xs-3 pl-xs-5" role="presentation">
            @php
              $moreCats2 = DB::select("select * from categories where cat_featured='yes' LIMIT 19,29");
            @endphp
            @foreach($moreCats2 as $mCat)
            @php
              $mMeta = DB::table('cats_meta')->where('cat_id', $mCat->cat_id)->where('language_id', $sLang)->first();
              $mTitle = $mMeta->cat_title ?? '';
            @endphp
            <li>
              <a class="display-block text-gray text-body-larger pt-xs-1" href="{{ $site_url }}/categories/category.php?cat_url={{ rawurlencode($mCat->cat_url) }}">
                {{ $mTitle }}
              </a>
            </li>
            @endforeach
          </ul>
          <ul class="list-unstyled display-inline-block col-xs-3 p-xs-3 pl-xs-5" role="presentation">
            @php
              $moreCats3 = DB::select("select * from categories where cat_featured='yes' LIMIT 29,39");
            @endphp
            @foreach($moreCats3 as $mCat)
            @php
              $mMeta = DB::table('cats_meta')->where('cat_id', $mCat->cat_id)->where('language_id', $sLang)->first();
              $mTitle = $mMeta->cat_title ?? '';
            @endphp
            <li>
              <a class="display-block text-gray text-body-larger pt-xs-1" href="{{ $site_url }}/categories/category.php?cat_url={{ rawurlencode($mCat->cat_url) }}">
                {{ $mTitle }}
              </a>
            </li>
            @endforeach
          </ul>
        </div>
      </div>

    </div>
  </div>
</div>
@include('legacy.partials.mobile-menu')
