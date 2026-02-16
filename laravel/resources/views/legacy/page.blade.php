@extends('legacy.layout')

@section('title'){{ $site_name }} - {{ $page_title ?? '' }}@endsection

@section('body_class')is-responsive @endsection

@section('content')
  <div class="container mt-5 mb-5">
    <div class="row mb-4">
      <div class="col-md-12 {{ $textRight ?? 'text-left' }}">
        <nav class="nav-breadcrumb {{ $floatRight ?? '' }}" aria-label="breadcrumb">
          <ol class="breadcrumb bg-white pl-0">
            <li class="breadcrumb-item"><a href="{{ $site_url }}">Home</a></li>
            <li class="breadcrumb-item active">{{ $page_title ?? '' }}</li>
          </ol>
        </nav>
        <div class="clearfix"></div>
        <h1 class="mt-1">{{ $page_title ?? '' }}</h1>
        <p class="lead mt-4">{!! $page_content ?? '' !!}</p>
      </div>
    </div>
  </div>
@endsection
