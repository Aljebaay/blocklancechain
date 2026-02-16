@php
use Illuminate\Support\Facades\DB;

$id = $post_id ?? 0;
$language_id = request('lang', $siteLanguage ?? 1);
$textRight = $textRight ?? 'text-left';
$floatRight = $floatRight ?? '';

$post = DB::table('posts')->where('id', $id)->first();
$post_meta = DB::table('posts_meta')->where('post_id', $id)->where('language_id', $language_id)->first();

$title = !empty($post_meta->title) ? $post_meta->title : '';
$author = !empty($post_meta->author) ? $post_meta->author : '';
$content = !empty($post_meta->content) ? $post_meta->content : '';

$url = preg_replace('#[ -]+#', '-', $title);

$cat_meta = DB::table('post_categories_meta')
    ->where('cat_id', $post->cat_id ?? 0)
    ->where('language_id', $language_id)
    ->first();
$cat_name = !empty($cat_meta->cat_name) ? $cat_meta->cat_name : '';

$comments = DB::table('post_comments')->where('post_id', $id)->get();
$count_comments = $comments->count();

$legacyData_local = $legacyData ?? null;
@endphp
<div class="card mb-4"><!--- card Starts --->
	<div class="card-body {{ $textRight }}"><!--- card-body Starts --->
		
		<h1 class="h3">{{ $title }}</h1>
		<hr>
	   <p>
	   	Published on: <span class="text-muted">{{ $post->date_time ?? '' }}</span> | 
	   	Category: <a href="index?cat_id={{ $post->cat_id ?? '' }}" class="text-muted">{{ $cat_name }}</a> |
	   	Author: <a href="#" class="text-muted">{{ $author }}</a> 
	   </p>

		<img src="{{ $legacyData_local ? $legacyData_local->getImageUrl('posts', $post->image ?? '') : '' }}" class="img-fluid mb-3"/>
		<div class="mt-3 post-content">
			{!! $content !!}
		</div>

		<div class="clearfix"></div>

		<div class="sharethis-inline-share-buttons mt-2 {{ ($lang_dir ?? '') == 'right' ? 'float-left' : '' }}"></div>

	</div><!--- card-body Ends --->
</div><!--- card Ends --->

<div class="card mb-3"><!--- card Starts --->
   <div class="card-body {{ $textRight }}"><!--- card-body Starts --->
      <h4 class="mb-3">{{ $count_comments }} comments</h4>

      @if(session()->has('seller_user_name'))
      <form action="" method="post">
        @csrf
        <div class="form-group"><!--- form-group Starts --->
         <textarea name="comment" class="form-control {{ $textRight }}" placeholder="Add A Comment..."></textarea>
        </div><!--- form-group Ends --->
        <div class="form-group"><!--- form-group Starts --->
         <button class="btn btn-success" name="submit" type="submit"> Post Comment </button>
        </div><!--- form-group Ends --->
      </form>
      @else
      <div class="alert alert-info rounded-0">
      <p class="mt-1 mb-1 text-center">
         <strong>Sorry!</strong> You can't submit a comment without logging in first. If you have a general question, please email us at {{ $site_email_address ?? '' }}
      </p>
      </div>
      @endif

      <ul class="list-unstyled mt-4 text-left">
      @foreach($comments as $comment)
      @php
        $comment_seller = DB::table('sellers')->where('seller_id', $comment->seller_id)->first();
      @endphp
        <li class="media mb-3">
          @if($legacyData_local && $comment_seller)
          <img class="mr-3 img-thumbnail" src="{{ $legacyData_local->getImageUrl2('sellers', 'seller_image', $comment_seller->seller_image ?? '') }}" width="50">
          @endif
          <div class="media-body">
            <h5 class="mt-0 mb-1">
             {{ $comment_seller->seller_user_name ?? '' }}
             <small>
              commented - {{ $comment->date ?? '' }}
            </small>
            </h5>
            {{ htmlspecialchars($comment->comment ?? '') }}
          </div>
        </li>
      @endforeach
      </ul>

   </div><!--- card-body Ends --->
</div><!--- card Ends --->

<a href="index" class="btn btn-success {{ $floatRight }}"> <i class="fa fa-arrow-left"></i>&nbsp; Go Back</a>
