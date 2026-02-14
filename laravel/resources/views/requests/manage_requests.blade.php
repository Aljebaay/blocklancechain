<!DOCTYPE html>
<html lang="en" class="ui-toolkit">
<head>
    <title>{{ $site_name ?? 'manage_requests' }} - manage_requests</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="/styles/bootstrap.css" rel="stylesheet">
    <link href="/styles/custom.css" rel="stylesheet">
    <link href="/styles/styles.css" rel="stylesheet">
    <link href="/styles/user_nav_styles.css" rel="stylesheet">
    <link href="/styles/sweat_alert.css" rel="stylesheet">
    <link href="/font_awesome/css/font-awesome.css" rel="stylesheet">
    <script type="text/javascript" src="/js/jquery.min.js"></script>
    <script type="text/javascript" src="/js/sweat_alert.js"></script>
</head>
<body class="is-responsive">
<div class="container-fluid mt-5 manage_requests">
    <div class="row">
        <div class="col-md-12 mb-4 d-flex justify-content-between align-items-center">
            <h1 class="m-0">Manage Requests</h1>
            <a href="/requests/post_request" class="btn btn-success">
                <i class="fa fa-plus-circle"></i> Post New Request
            </a>
        </div>
        <div class="col-md-12">
            @php
                $tabs = [
                    'active' => 'Active Requests',
                    'pause' => 'Pause Requests',
                    'pending' => 'Pending Approval',
                    'unapproved' => 'Unapproved',
                ];
            @endphp
            <ul class="nav nav-tabs flex-column flex-sm-row mt-4">
                @foreach($tabs as $key => $label)
                    @php
                        $count = isset($requests[$key]) ? count($requests[$key]) : 0;
                    @endphp
                    <li class="nav-item">
                        <a href="#{{ $key }}" data-toggle="tab" class="nav-link {{ $loop->first ? 'active' : '' }} make-black">
                            {{ $label }} <span class="badge badge-success">{{ $count }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="tab-content mt-4">
                @foreach($tabs as $key => $label)
                    @php
                        $items = $requests[$key] ?? [];
                    @endphp
                    <div id="{{ $key }}" class="tab-pane fade {{ $loop->first ? 'show active' : '' }}">
                        <div class="table-responsive box-table">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Date</th>
                                    <th>Offers</th>
                                    <th>Budget</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(count($items) === 0)
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            @if($key === 'active')
                                                <i class="fa fa-meh-o"></i> You've have no active requests at the moment.
                                            @elseif($key === 'pause')
                                                <h3 class="pt-4 pb-4"><i class="fa fa-smile-o"></i> You currently have no requests paused.</h3>
                                            @elseif($key === 'pending')
                                                <h3 class="pt-4 pb-4"><i class="fa fa-smile-o"></i> You currently have no requests pending.</h3>
                                            @else
                                                <h3 class="pt-4 pb-4"><i class="fa fa-smile-o"></i> You currently have no unapproved requests.</h3>
                                            @endif
                                        </td>
                                    </tr>
                                @else
                                    @foreach($items as $item)
                                        @php
                                            $requestId = (int) ($item->request_id ?? 0);
                                            $offers = $offerCounts[$requestId] ?? 0;
                                        @endphp
                                        <tr>
                                            <td>{{ $item->request_title ?? '' }}</td>
                                            <td>{{ $item->request_description ?? '' }}</td>
                                            <td>{{ $item->request_date ?? '' }}</td>
                                            <td>{{ $offers }}</td>
                                            <td class="text-success">{{ $item->request_budget ?? '' }}</td>
                                            <td class="text-center">
                                                <div class="dropdown">
                                                    <button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                                                        Actions
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item" href="/requests/pause_request?request_id={{ $requestId }}">Pause</a>
                                                        <a class="dropdown-item" href="/requests/resume_request?request_id={{ $requestId }}">Resume</a>
                                                        <a class="dropdown-item" href="/requests/update_request?request_id={{ $requestId }}">Edit</a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
</body>
</html>
