@php
    $price = number_format($proposal->proposal_price, 2);
    $sellerUrl = url('/' . $seller->seller_user_name);
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $proposal->proposal_title }} | {{ $seller->seller_user_name }}</title>
    <link href="{{ asset('styles/bootstrap.css') }}" rel="stylesheet">
    <link href="{{ asset('styles/styles.css') }}" rel="stylesheet">
    <link href="{{ asset('styles/custom.css') }}" rel="stylesheet">
    <link href="{{ asset('styles/desktop_proposals.css') }}" rel="stylesheet">
</head>
<body>
<div class="container mt-4 mb-5">
    <div class="row">
        <div class="col-md-8">
            <h1 class="mb-2">{{ $proposal->proposal_title }}</h1>
            <div class="text-muted mb-3">
                <span class="me-2">by <a href="{{ $sellerUrl }}">{{ $seller->seller_user_name }}</a></span>
                <span class="me-2">•</span>
                <span class="me-2">{{ $rating_avg ? number_format($rating_avg, 1) : '0.0' }} ★ ({{ $rating_count }})</span>
                @if($category)
                    <span class="me-2">•</span>
                    <span>{{ $category->cat_title ?? $category->cat_url }}</span>
                @endif
            </div>

            @if($proposal->proposal_img1)
                <div class="mb-4">
                    <img class="img-fluid rounded" src="{{ asset('proposals/' . $proposal->proposal_img1) }}" alt="{{ $proposal->proposal_title }}">
                </div>
            @endif

            <div class="card mb-4">
                <div class="card-body">
                    {!! $proposal->proposal_desc !!}
                </div>
            </div>

            @if($faqs->count())
                <div class="card mb-4">
                    <div class="card-header">FAQs</div>
                    <div class="card-body">
                        <div class="accordion" id="faqAccordion">
                            @foreach($faqs as $index => $faq)
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading{{ $index }}">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}">
                                            {{ $faq->faq_title }}
                                        </button>
                                    </h2>
                                    <div id="collapse{{ $index }}" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body">{!! $faq->faq_content !!}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            @if($reviews)
                <div class="card mb-4">
                    <div class="card-header">Reviews ({{ $rating_count }})</div>
                    <div class="card-body">
                        <p class="mb-0">Average rating: {{ $rating_avg ? number_format($rating_avg, 1) : '0.0' }} / 5</p>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <img src="{{ asset('user_images/' . ($seller->seller_image ?? 'empty.png')) }}" class="rounded-circle me-3" alt="{{ $seller->seller_user_name }}" width="56" height="56">
                        <div>
                            <div class="fw-bold">{{ $seller->seller_user_name }}</div>
                            <div class="text-muted small">{{ $seller->seller_country }}</div>
                        </div>
                    </div>
                    <h3 class="mb-3">${{ $price }}</h3>
                    @if($delivery)
                        <div class="mb-2 text-muted">Delivery: {{ $delivery->delivery_proposal_title }}</div>
                    @endif
                    <button class="btn btn-primary w-100 mb-2" disabled>Order (coming soon)</button>
                    <button class="btn btn-outline-secondary w-100" disabled>
                        @if($favorite === null)
                            Favorite
                        @else
                            {{ $favorite ? 'Unfavorite' : 'Favorite' }}
                        @endif
                    </button>
                </div>
            </div>

            @if($extras->count())
                <div class="card mb-3">
                    <div class="card-header">Extras</div>
                    <ul class="list-group list-group-flush">
                        @foreach($extras as $extra)
                            <li class="list-group-item d-flex justify-content-between">
                                <span>{{ $extra->extra_title }}</span>
                                <span class="fw-bold">${{ number_format($extra->extra_price, 2) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</div>

<script src="{{ asset('js/bootstrap.min.js') }}"></script>
</body>
</html>
