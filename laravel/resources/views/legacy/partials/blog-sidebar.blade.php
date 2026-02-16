@php
    use Illuminate\Support\Facades\DB;
    $sLang = $siteLanguage ?? 1;
    $langDir = $lang_dir ?? 'left';
    $textRight = $textRight ?? 'text-left';
@endphp
<div class="card mb-3"><!--- card Starts -->

	<div class="card-body"><!--- card-body Starts -->

		<form action="index" method="get">
		
			<div class="input-group">
				@if($langDir == 'right')
					<div class="input-group-prepend">
						<button class="btn btn-success rounded-0 rounded-right" type="submit">
							<i class="fa fa-search"></i>
						</button>
					</div>
					<input type="text" class="form-control {{ $textRight }}" placeholder="{{ $lang['placeholder']['search'] ?? 'Search' }}" name="search" value="{{ request('search', '') }}" required />
				@else
					<input type="text" class="form-control" placeholder="{{ $lang['placeholder']['search'] ?? 'Search' }}" name="search" value="{{ request('search', '') }}" required />
					<div class="input-group-prepend">
						<button class="btn btn-success rounded-0 rounded-right" type="submit">
							<i class="fa fa-search"></i>
						</button>
					</div>			
				@endif
			</div>

		</form>

	</div><!--- card-body Ends -->

</div><!--- card Ends -->

<div class="card card-primary">
	<div class="card-header {{ $textRight }}">Categories</div>
	<div class="card-body">
		<ul class="mb-0 list-unstyled ml-3 mr-3 {{ $textRight }}">
			@php
			$blog_categories = DB::table('post_categories')->get();
			@endphp
			@foreach($blog_categories as $cat)
			@php
				$cat_meta = DB::table('post_categories_meta')
					->where('cat_id', $cat->id)
					->where('language_id', $sLang)
					->first();
				$cat_name = $cat_meta->cat_name ?? '';
				$cat_image = $cat->cat_image ?? '';
			@endphp
				<li>
					<a href="index?cat_id={{ $cat->id }}">
						@if(!empty($cat_image))
							<img src="{{ $site_url }}/blog_cat_images/{{ $cat_image }}" width="18" class='mr-1'>
						@else
							<span style="margin-left: 26px;"></span>
						@endif
						{{ $cat_name }}
					</a>
				</li>
			@endforeach
		</ul>
	</div>
</div>
