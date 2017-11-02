@extends('admin/layer')
@section('layer-content')
        <!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.content') }}</li>
                <li><a class="link-effect" href="{{ asset('/admin/content/video') }}">{{trans('common.video')}}</a></li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            <h1 class="page-heading" >
                {{trans('common.video')}}
            </h1>
        </div>
        @if($user->user_id !== null)
            <div class="col-sm-8 col-xs-3">
                <!-- Single button -->
                <div class="btn-group pull-right" id="family-action">
                    <a type="button" class="btn btn-primary" href="{{ asset('/admin/content/video/create') }}">
                        {{ trans('content.add_video') }}
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
<!-- END Page Header -->
<div class="content">
    <div class="block">
        <ul class="nav nav-tabs nav-tabs-alt">
            <li class="{{ isActiveLabel('active',1,'active','') }}">
                <a href="{{ asset('admin/content/video?active=1') }}">{{ trans('content.normal') }}</a>
            </li>
            <li class="{{ isActiveLabel('official',1,'active','') }}">
                <a href="{{ asset('admin/content/video?official=1') }}">{{ trans('content.official') }}</a>
            </li>
            <li class="{{ isActiveLabel('official',0,'active','') }}">
                <a href="{{ asset('admin/content/video?official=0') }}">{{ trans('content.unofficial') }}</a>
            </li>
            <li class="{{ isActiveLabel('active',0,'active','') }}">
                <a href="{{ asset('admin/content/video?active=0') }}">{{ trans('content.disable') }}</a>
            </li>
        </ul>
        <div class="block-content tab-content">
            <div class="block-content tab-content">
                <!-- DataTables init on table by adding .js-dataTable-full class, functionality initialized in js/pages/base_tables_datatables.js -->
                <table class="table table-bordered table-striped js-dataTable-full">
                    <thead>
                    <tr>
                        <th class="co-md-2 text-center"></th>
                        <th class="col-md-2">{{ trans('content.sponsor') }}</th>
                        <th class="hidden-xs col-md-2">{{ trans('content.retweet') }}</th>
                        <th class="hidden-xs col-md-2">{{ trans('content.comment') }}</th>
                        <th class="hidden-xs col-md-2">{{ trans('content.like') }}</th>
                        <th class="col-md-2 col-xs-3 text-center">{{ trans('common.detail_info') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($tweets as $tweet)
                        <tr>
                            <td class="text-center">{{ $tweet->id }}</td>
                            <td class="font-w600">{{ $tweet->user_id }}</td>
                            <td class="hidden-xs"><em>{{ 'TODO' }}</em></td>
                            <td class="hidden-xs"><em>{{ 'TODO' }}</em></td>
                            <td class="hidden-xs"><em>{{ 'TODO' }}</em></td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a class="btn btn-xs btn-default" href="{{ asset('/admin/content/video/' . $tweet->id) }}"><i class="fa fa-pencil"></i></a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                <div class="row push-30-t">
                    {{--真实代码--}}
                    {{--<video id="my_video_1" class="col-xs-12 video-js vjs-default-skin" controls--}}
                    {{--preload="auto"  poster="{{ CloudStorage::downloadUrl($video->screen_shot) }}"--}}
                    {{--data-setup="{}" webkit-playsinline>--}}
                    {{--测试代码--}}
                    <video id="my_video_1" class="col-xs-3 video-js vjs-default-skin" controls
                           preload="auto"  poster="{{asset('/web/57d0d1042a7ec_1024.jpg')}}"
                           data-setup="{}" webkit-playsinline>
                        {{--真实代码--}}
                        {{--<source src="{{ CloudStorage::downloadUrl($video->video) }}" type="video/mp4">--}}
                        {{--测试代码--}}
                        <source src="{{asset('/web/57d0d0e5357c5.mp4')}}" type="video/mp4">
                    </video>
                </div>


                {{--<div class="center bottommargin">--}}
                {{--<a href="{{asset('/web/57d0d0e5357c5.mp4')}}" data-lightbox="iframe" style="position: relative;">--}}
                {{--<img src="images/services/video.jpg" alt="Video">--}}
                {{--<span class="i-overlay nobg"><img src="{{asset('/web/57d0d1042a7ec_1024.jpg')}}" alt="Play"></span>--}}
                {{--</a>--}}
                {{--</div>--}}



                <div class="tab-pane active"></div>
            </div>
        </div>
    </div>
</div>
@endsection
