<!DOCTYPE html>
<html lang="en" class="ui-toolkit">
<head>
    <title>{{  ?? 'manage_requests' }} - manage_requests</title>
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
<div class="container-fluid mt-5">
    <div class="row">
        <div class="col-md-12 mb-4">
            <h1 class="pull-left">Manage Requests</h1>
            <a href="/requests/post_request" class="btn btn-success pull-right">
                <i class="fa fa-plus-circle"></i> Post New Request
            </a>
        </div>
        <div class="col-md-12">
            @php
                 = ['active' => 'Active Requests', 'pause' => 'Pause Requests', 'pending' => 'Pending Approval', 'unapproved' => 'Unapproved'];
            @endphp
            <ul class="nav nav-tabs flex-column flex-sm-row  mt-4">
                @foreach( as  => )
                    @php  = isset([]) ? count([]) : 0; @endphp
                    <li class="nav-item">
                        <a href="#{{  }}" data-toggle="tab" class="nav-link {{ ->first ? 'active' : '' }} make-black">
                            {{  }} <span class="badge badge-success">{{  }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
            <div class="tab-content mt-4">
                @foreach( as  => )
                    @php  = [] ?? []; @endphp
                    <div id="{{  }}" class="tab-pane fade {{ ->first ? 'show active' : '' }}">
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
                                @if(count() === 0)
                                    <tr><td colspan="6" class="text-center">
                                        @if( === 'active') <i class='fa fa-meh-o'></i> You've have no active requests at the moment.
                                        @elseif( === 'pause') <h3 class='pt-4 pb-4'><i class='fa fa-smile-o'></i> You currently have no requests paused.</h3>
                                        @elseif( === 'pending') <h3 class='pt-4 pb-4'><i class='fa fa-smile-o'></i> You currently have no requests pending.</h3>
                                        @else <h3 class='pt-4 pb-4'><i class='fa fa-smile-o'></i> You currently have no unapproved requests.</h3>
                                        @endif
                                    </td></tr>
                                @else
                                    @foreach( as )
                                        @php
                                             = (int) (->request_id ?? 0);
                                             = [] ?? 0;
                                        @endphp
                                        <tr>
                                            <td>{{ ->request_title ?? '' }}</td>
                                            <td>{{ ->request_description ?? '' }}</td>
                                            <td>{{ ->request_date ?? '' }}</td>
                                            <td>{{  }}</td>
                                            <td class="text-success">{{ ->request_budget ?? '' }}</td>
                                            <td class="text-center">
                                                <div class="dropdown">
                                                    <button class="btn btn-secondary dropdown-toggle" type="button">Actions</button>
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
