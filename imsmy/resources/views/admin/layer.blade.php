@extends('index')

@section('title','谷鸟管理')

@section('css')

@endsection
@section('css-framework')
    <!-- OneUI CSS framework -->
    <link rel="stylesheet" id="css-main" href="{{ asset('/css/admin/oneui.1.4.css') }}">
    @if(array_key_exists('skin',$_COOKIE) && $_COOKIE['skin'] != null && $_COOKIE['skin']  != 'default')
        <link rel="stylesheet" id="css-theme" href="{{ $_COOKIE['skin'] }}">
    @elseif(!array_key_exists('skin',$_COOKIE) || $_COOKIE['skin'] != 'default')
        <link rel="stylesheet" id="css-theme" href="{{ asset('css/admin/themes/city.min.css')}}">
    @endif
@endsection
@section('content')
<!-- Page Container -->
<!--
    Available Classes:

    'sidebar-l'                  Left Sidebar and right Side Overlay
    'sidebar-r'                  Right Sidebar and left Side Overlay
    'sidebar-mini'               Mini hoverable Sidebar (> 991px)
    'sidebar-o'                  Visible Sidebar by default (> 991px)
    'sidebar-o-xs'               Visible Sidebar by default (< 992px)

    'side-overlay-hover'         Hoverable Side Overlay (> 991px)
    'side-overlay-o'             Visible Side Overlay by default (> 991px)

    'side-scroll'                Enables custom scrolling on Sidebar and Side Overlay instead of native scrolling (> 991px)

    'header-navbar-fixed'        Enables fixed header
-->
<div id="page-container" class="sidebar-l sidebar-o side-scroll header-navbar-fixed">
    <!-- Side Overlay-->
    <aside id="side-overlay">
        <!-- Side Overlay Scroll Container -->
        <div id="side-overlay-scroll">
            <!-- Side Header -->
            <div class="side-header side-content">
                <!-- Layout API, functionality initialized in App() -> uiLayoutApi() -->
                <button class="btn btn-default pull-right"
                        type="button" data-toggle="layout"
                        data-action="side_overlay_close">
                    <i class="fa fa-times"></i>
                </button>
                        <span>
                            <img class="img-avatar img-avatar32" src="{{ asset('images/avatar1.jpg') }}" alt="">
                            <span class="font-w600 push-10-l">Roger Hart</span>
                        </span>
            </div>
            <!-- END Side Header -->

            <!-- Side Content -->
            <div class="side-content remove-padding-t">
                <!-- Notifications -->
                <div class="block pull-r-l">
                    <div class="block-header bg-gray-lighter">
                        <ul class="block-options">
                            <li>
                                <button type="button"
                                        data-toggle="block-option"
                                        data-action="refresh_toggle"
                                        data-action-mode="demo">
                                    <i class="fa fa-refresh"></i>
                                </button>
                            </li>
                            <li>
                                <button type="button" data-toggle="block-option" data-action="content_toggle"></button>
                            </li>
                        </ul>
                        <h3 class="block-title">Recent Activity</h3>
                    </div>
                    <div class="block-content">
                        <!-- Activity List -->
                        <ul class="list list-activity">
                            <li>
                                <i class="fa fa-wikipedia-w text-success"></i>
                                <div class="font-w600">New sale ($15)</div>
                                <div><a href="javascript:void(0)">Admin Template</a></div>
                                <div><small class="text-muted">3 min ago</small></div>
                            </li>
                            <li>
                                <i class="fa fa-pencil text-info"></i>
                                <div class="font-w600">You edited the file</div>
                                <div>
                                    <a href="javascript:void(0)">
                                        <i class="fa fa-file-text-o"></i> Documentation.doc
                                    </a>
                                </div>
                                <div><small class="text-muted">15 min ago</small></div>
                            </li>
                            <li>
                                <i class="fa fa-times-circle-o text-danger"></i>
                                <div class="font-w600">Project deleted</div>
                                <div><a href="javascript:void(0)">Line Icon Set</a></div>
                                <div><small class="text-muted">4 hours ago</small></div>
                            </li>
                            <li>
                                <i class="fa fa-wrench text-warning"></i>
                                <div class="font-w600">Core v2.5 is available</div>
                                <div><a href="javascript:void(0)">Update now</a></div>
                                <div><small class="text-muted">6 hours ago</small></div>
                            </li>
                        </ul>
                        <div class="text-center">
                            <small><a href="javascript:void(0)">Load More..</a></small>
                        </div>
                        <!-- END Activity List -->
                    </div>
                </div>
                <!-- END Notifications -->

                <!-- Online Friends -->
                <div class="block pull-r-l">
                    <div class="block-header bg-gray-lighter">
                        <ul class="block-options">
                            <li>
                                <button type="button"
                                        data-toggle="block-option"
                                        data-action="refresh_toggle"
                                        data-action-mode="demo">
                                    <i class="fa fa-refresh"></i>
                                </button>
                            </li>
                            <li>
                                <button type="button" data-toggle="block-option" data-action="content_toggle"></button>
                            </li>
                        </ul>
                        <h3 class="block-title">Online Friends</h3>
                    </div>
                    <div class="block-content block-content-full">
                        <!-- Users Navigation -->
                        <ul class="nav-users">
                            <li>
                                <a href="base_pages_profile.html">
                                    <img class="img-avatar" src="{{ asset('images/avatar1.jpg') }}" alt="">
                                    <i class="fa fa-circle text-success"></i> Rebecca Gray
                                    <div class="font-w400 text-muted"><small>Copywriter</small></div>
                                </a>
                            </li>
                            <li>
                                <a href="base_pages_profile.html">
                                    <img class="img-avatar" src="{{ asset('images/avatar1.jpg') }}" alt="">
                                    <i class="fa fa-circle text-success"></i> Dennis Ross
                                    <div class="font-w400 text-muted"><small>Web Developer</small></div>
                                </a>
                            </li>
                            <li>
                                <a href="base_pages_profile.html">
                                    <img class="img-avatar" src="{{ asset('images/avatar1.jpg') }}" alt="">
                                    <i class="fa fa-circle text-success"></i> Denise Watson
                                    <div class="font-w400 text-muted"><small>Web Designer</small></div>
                                </a>
                            </li>
                            <li>
                                <a href="base_pages_profile.html">
                                    <img class="img-avatar" src="{{ asset('images/avatar1.jpg') }}" alt="">
                                    <i class="fa fa-circle text-warning"></i> Denise Watson
                                    <div class="font-w400 text-muted"><small>Photographer</small></div>
                                </a>
                            </li>
                            <li>
                                <a href="base_pages_profile.html">
                                    <img class="img-avatar" src="{{ asset('images/avatar1.jpg') }}" alt="">
                                    <i class="fa fa-circle text-warning"></i> John Parker
                                    <div class="font-w400 text-muted"><small>Graphic Designer</small></div>
                                </a>
                            </li>
                        </ul>
                        <!-- END Users Navigation -->
                    </div>
                </div>
                <!-- END Online Friends -->

                <!-- Quick Settings -->
                <div class="block pull-r-l">
                    <div class="block-header bg-gray-lighter">
                        <ul class="block-options">
                            <li>
                                <button type="button" data-toggle="block-option" data-action="content_toggle"></button>
                            </li>
                        </ul>
                        <h3 class="block-title">Quick Settings</h3>
                    </div>
                    <div class="block-content">
                        <!-- Quick Settings Form -->
                        <form class="form-bordered" action="index.html" method="post" onsubmit="return false;">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-xs-8">
                                        <div class="font-s13 font-w600">Online Status</div>
                                        <div class="font-s13 font-w400 text-muted">Show your status to all</div>
                                    </div>
                                    <div class="col-xs-4 text-right">
                                        <label class="css-input switch switch-sm switch-primary push-10-t">
                                            <input type="checkbox"><span></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-xs-8">
                                        <div class="font-s13 font-w600">Auto Updates</div>
                                        <div class="font-s13 font-w400 text-muted">Keep up to date</div>
                                    </div>
                                    <div class="col-xs-4 text-right">
                                        <label class="css-input switch switch-sm switch-primary push-10-t">
                                            <input type="checkbox"><span></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-xs-8">
                                        <div class="font-s13 font-w600">Notifications</div>
                                        <div class="font-s13 font-w400 text-muted">Do you need them?</div>
                                    </div>
                                    <div class="col-xs-4 text-right">
                                        <label class="css-input switch switch-sm switch-primary push-10-t">
                                            <input type="checkbox" checked><span></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-xs-8">
                                        <div class="font-s13 font-w600">API Access</div>
                                        <div class="font-s13 font-w400 text-muted">Enable/Disable access</div>
                                    </div>
                                    <div class="col-xs-4 text-right">
                                        <label class="css-input switch switch-sm switch-primary push-10-t">
                                            <input type="checkbox" checked><span></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <!-- END Quick Settings Form -->
                    </div>
                </div>
                <!-- END Quick Settings -->
            </div>
            <!-- END Side Content -->
        </div>
        <!-- END Side Overlay Scroll Container -->
    </aside>
    <!-- END Side Overlay -->

    <!-- Sidebar -->
    <nav id="sidebar">
        <!-- Sidebar Scroll Container -->
        <div id="sidebar-scroll">
            <!-- Sidebar Content -->
            <!-- Adding .sidebar-mini-hide to an element will hide it when the sidebar is in mini mode -->
            <div class="sidebar-content">
                <!-- Side Header -->
                <div class="side-header side-content bg-white-op">
                    <!-- Layout API, functionality initialized in App() -> uiLayoutApi() -->
                    <button class="btn btn-link text-gray pull-right hidden-md hidden-lg"
                            type="button" data-toggle="layout" data-action="sidebar_close">
                        <i class="fa fa-times"></i>
                    </button>
                    <!-- Themes functionality initialized in App() -> uiHandleTheme() -->
                    <div class="btn-group pull-right">
                        <button class="btn btn-link text-gray dropdown-toggle" data-toggle="dropdown" type="button">
                            <i class="fa fa-tint"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right font-s13 sidebar-mini-hide">
                            <li>
                                <a data-toggle="theme" data-theme="default" tabindex="-1" href="javascript:void(0)">
                                    <i class="fa fa-circle text-default pull-right"></i>
                                    <span class="font-w600">Default</span>
                                </a>
                            </li>
                            <li>
                                <a data-toggle="theme" data-theme="{{ asset('css/admin/themes/amethyst.min.css')}}"
                                   tabindex="-1" href="javascript:void(0)">
                                    <i class="fa fa-circle text-amethyst pull-right"></i>
                                    <span class="font-w600">Amethyst</span>
                                </a>
                            </li>
                            <li>
                                <a data-toggle="theme" data-theme="{{ asset('css/admin/themes/city.min.css')}}"
                                   tabindex="-1" href="javascript:void(0)">
                                    <i class="fa fa-circle text-city pull-right"></i>
                                    <span class="font-w600">City</span>
                                </a>
                            </li>
                            <li>
                                <a data-toggle="theme" data-theme="{{ asset('css/admin/themes/flat.min.css')}}"
                                   tabindex="-1" href="javascript:void(0)">
                                    <i class="fa fa-circle text-flat pull-right"></i>
                                    <span class="font-w600">Flat</span>
                                </a>
                            </li>
                            <li>
                                <a data-toggle="theme" data-theme="{{ asset('css/admin/themes/modern.min.css')}}"
                                   tabindex="-1" href="javascript:void(0)">
                                    <i class="fa fa-circle text-modern pull-right"></i>
                                    <span class="font-w600">Modern</span>
                                </a>
                            </li>
                            <li>
                                <a data-toggle="theme" data-theme="{{ asset('css/admin/themes/smooth.min.css')}}"
                                   tabindex="-1" href="javascript:void(0)">
                                    <i class="fa fa-circle text-smooth pull-right"></i>
                                    <span class="font-w600">Smooth</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <a class="h5 text-white" href="{{asset('/admin/dashboard')}}">
                        <img src="{{ asset('images/logo_g.png') }}" class="btn-primary" style="width: 25px;vertical-align: text-bottom;border-radius: 5px;">
                        <span class="h4 font-w600 sidebar-mini-hide">goobird</span>
                    </a>
                </div>
                <!-- END Side Header -->

                <!-- Side Content -->
                <div class="side-content">
                    <ul class="nav-main">

                        @foreach( Session::get('menu') as $v )
                            @if(menuShowCheck($v['id']))
                                <li>
                                    <a class="nav-submenu" data-toggle="nav-submenu" href="#"><i class="fa {{$v['class_icon']}}"></i> {{$v['name']}}</a>
                                    @if($v['_children'])
                                        <ul>
                                            @foreach($v['_children'] as $vv )
                                                @if(menuShowCheck($vv['id']))
                                                    <li><a href="{{ URL($vv['route']) }}">{{$vv['name']}}</a></li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endif
                        @endforeach

                    </ul>
                </div>
                <!-- END Side Content -->
            </div>
            <!-- Sidebar Content -->
        </div>
        <!-- END Sidebar Scroll Container -->
    </nav>
    <!-- END Sidebar -->

    <!-- Header -->
    <header id="header-navbar" class="content-mini content-mini-full">
        <!-- Header Navigation Right -->
        <ul class="nav-header pull-right">
            <li>
                <div class="btn-group">
                    <button class="btn btn-default btn-image dropdown-toggle" data-toggle="dropdown" type="button">
                        <img src="{{ asset('/admin/images/'.$user->avatar) }}" alt="Avatar">
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right">
                        <li class="dropdown-header">{{ trans('common.profile') }}</li>
                        <li>
                            <a tabindex="-1" href="{{ asset('/admin/account') }}">
                                <i class="fa fa-user pull-right"></i>{{ trans('common.profile') }}
                            </a>
                        </li>
                        <li>
                            <a tabindex="-1" href="javascript:void(0)">
                                <i class="fa fa-cog pull-right"></i>{{ trans('common.setting') }}
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li class="dropdown-header">{{ trans('common.actions') }}</li>
                        <li>
                            <a tabindex="-1" href="/admin/logout">
                                <i class="fa fa-sign-out pull-right"></i>{{ trans('common.logout') }}
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li>
                <!-- Layout API, functionality initialized in App() -> uiLayoutApi() -->
                <button class="btn btn-default" data-toggle="layout" data-action="side_overlay_toggle" type="button">
                    <i class="fa fa-tasks"></i>
                </button>
            </li>
        </ul>
        <!-- END Header Navigation Right -->

        <!-- Header Navigation Left -->
        <ul class="nav-header pull-left">
            <li class="hidden-md hidden-lg">
                <!-- Layout API, functionality initialized in App() -> uiLayoutApi() -->
                <button class="btn btn-default" data-toggle="layout" data-action="sidebar_toggle" type="button">
                    <i class="fa fa-navicon"></i>
                </button>
            </li>
            <li class="hidden-xs hidden-sm">
                <!-- Layout API, functionality initialized in App() -> uiLayoutApi() -->
                <button class="btn btn-default" data-toggle="layout" data-action="sidebar_mini_toggle" type="button">
                    <i class="fa fa-ellipsis-v"></i>
                </button>
            </li>
            <li>
                <!-- Opens the Apps modal found at the bottom of the page, before including JS code -->
                <button class="btn btn-default pull-right" data-toggle="modal" data-target="#apps-modal" type="button">
                    <i class="fa fa-th-large"></i>
                </button>
            </li>
            {{--<li class="visible-xs">--}}
                {{--<!-- Toggle class helper (for .js-header-search below), functionality initialized in App() -> uiToggleClass() -->--}}
                {{--<button class="btn btn-default"--}}
                        {{--data-toggle="class-toggle"--}}
                        {{--data-target=".js-header-search"--}}
                        {{--data-class="header-search-xs-visible"--}}
                        {{--type="button">--}}
                    {{--<i class="fa fa-search"></i>--}}
                {{--</button>--}}
            {{--</li>--}}
            {{--<li class="js-header-search header-search">--}}
                {{--<form class="form-horizontal" >--}}
                    {{--<div class="form-material form-material-primary input-group remove-margin-t remove-margin-b">--}}
                        {{--<input class="form-control" type="text" id="base-material-text"--}}
                               {{--name="base-material-text" placeholder="{{ trans('common.search') }}..">--}}
                        {{--<span class="input-group-addon"><i class="fa fa-search"></i></span>--}}
                    {{--</div>--}}
                {{--</form>--}}
            {{--</li>--}}
        </ul>
        <!-- END Header Navigation Left -->
    </header>
    <!-- END Header -->

    <!-- Main Container -->
    <main id="main-container">
        @yield('layer-content')
    </main>
    <!-- END Main Container -->

    <!-- Footer -->
    <footer id="page-footer" class="content-mini content-mini-full font-s12 bg-gray-lighter clearfix" style="overflow-x: visible">
        <div class="pull-left" style="line-height: 30px">
            <a class="font-w600" href="javascript:void(0)" target="_blank">Goobird</a> &copy;
            <span class="js-year-copy"></span>
        </div>
        <ul class="nav-header pull-right">
            <li>
                <div class="btn-group dropup">
                    <button class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" type="button">
                        @if(null == App::getLocale())
                            {{ trans('multi-lang.zh') }}
                        @else
                            {{ trans('multi-lang.'.App::getLocale()) }}
                        @endif
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right">
                        <li><a href="#" class="multiple-lang" name="zh">{{ trans('multi-lang.zh') }}</a></li>
                        <li><a href="#" class="multiple-lang" name="en">{{ trans('multi-lang.en') }}</a></li>
                    </ul>
                </div>
            </li>
        </ul>
    </footer>
    <!-- END Footer -->
</div>
<!-- END Page Container -->

<!-- Apps Modal -->
<!-- Opens from the button in the header -->
<div class="modal fade" id="apps-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-sm modal-dialog modal-dialog-top">
        <div class="modal-content">
            <!-- Apps Block -->
            <div class="block block-themed block-transparent">
                <div class="block-header bg-primary-dark">
                    <ul class="block-options">
                        <li>
                            <button data-dismiss="modal" type="button"><i class="fa fa-times-circle-o"></i></button>
                        </li>
                    </ul>
                    <h3 class="block-title">Apps</h3>
                </div>
                <div class="block-content">
                    <div class="row text-center">
                        <div class="col-xs-6">
                            <a class="block block-rounded" href="#">
                                <div class="block-content text-white bg-default">
                                    <i class="fa fa-tachometer fa-2x"></i>
                                    <div class="font-w600 push-15-t push-15">Backend</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-xs-6">
                            <a class="block block-rounded" href="#">
                                <div class="block-content text-white bg-modern">
                                    <i class="fa fa-rocket fa-2x"></i>
                                    <div class="font-w600 push-15-t push-15">Frontend</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END Apps Block -->
        </div>
    </div>
</div>
<!-- END Apps Modal -->
@endsection

@section('scripts')
    <script src="{{ asset('js/core/jquery.slimscroll.min.js')}}"></script>
    <script src="{{ asset('js/core/jquery.scrollLock.min.js')}}"></script>
    <script src="{{ asset('js/core/jquery.appear.min.js')}}"></script>
    <script src="{{ asset('js/core/jquery.countTo.min.js')}}"></script>
    <script src="{{ asset('js/core/jquery.placeholder.min.js')}}"></script>
    <script src="{{ asset('js/core/js.cookie.min.js')}}"></script>
    <script src="{{ asset('js/admin/app.js')}}"></script>
    <script src="{{ asset('js/admin/common.js') }}"></script>
    <!-- Page Plugins -->
    {{--<script src="https://cdn.bootcss.com/slick-carousel/1.5.9/slick.min.js"></script>
    <script src="https://cdn.bootcss.com/Chart.js/2.0.0-beta2/Chart.min.js"></script>--}}

            <!-- Page JS Code -->
    {{--<script src="assets/js/pages/base_pages_dashboard.js"></script>--}}
    <script>
        $(function () {
            // Init page helpers (Slick Slider plugin)
            App.initHelpers('slick');
        });
        //多语言 提示信息 初始化
        Lang = {};
        Goobird.init();
    </script>
@endsection