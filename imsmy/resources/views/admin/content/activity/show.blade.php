@extends('admin.layer')
@section('layer-content')
        <!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.activity') }}</li>
                <li><a class="link-effect" href="{{ asset('/admin/content/activity') }}">{{trans('common.activity_content')}}</a></li>
                <li>{{ $data->id }}</li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            <h1 class="page-heading" >
                {{ trans('common.activity'). ' : ' .$data->name}}
            </h1>
        </div>
        <div class="col-sm-8 col-xs-3">
            <div class="pull-right">
                <form action="{{ asset('/admin/content/activity/' . $data->id) }}" method="POST">
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="_token" value="{{ csrf_token()}}">
                    @if(1 != $data->active)
                        <input type="hidden" name="active" value="1">
                        <button class="btn btn-sm btn-success" type="submit">
                            {{ trans('common.pass') }}
                        </button>
                        <a class="btn btn-sm btn-success" type="button"
                           href="{{ asset('/admin/content/activity/' . $data->id . '/edit') }}">
                            <i class="fa fa-pencil-square-o"></i> {{trans('common.edit')}}
                        </a>
                    @else
                        <input type="hidden" name="active" value="2">
                        <button class="btn btn-sm bg-red" type="submit">
                            <i class="fa fa-ban"></i> {{ trans('common.disable') }}
                        </button>
                    @endif
                    <a class="btn btn-sm btn-success" type="button"
                       href="{{ asset('/admin/content/activity/' . $data->id . '/recommend_channel') }}">
                        <i class="fa fa-pencil-square-o"></i> {{ trans('content.recommend') }}
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- END Page Header -->
<div class="content">
    <div class="block">
        <div class="row">
            <div class="col-md-8 col-md-offset-2" style="margin-bottom: 5%">

                <div class="row push-30-t">
                    <div class="col-xs-12">
                        {{ trans('content.activity') . ' : ' }}
                    </div>
                    <div class="row push-30-t">
                        <div class="col-xs-12">
                            &nbsp;&nbsp;&nbsp;&nbsp;{{ $data-> comment}}
                        </div>
                    </div>
                </div>

                <div class="row push-30-t">
                    <div class="col-xs-6">
                        {{ trans('content.sponsor') . ' : ' . $data->user_id }}
                    </div>
                    <div class="col-xs-6">
                        {{ trans('common.bonus') . ' : ' . $data->bonus }}
                    </div>
                </div>

                <div class="row push-30-t">
                    <div class="col-xs-6">
                        {{ trans('content.forwarding_times') . ' : ' . $data->forwarding_times }}
                    </div>
                    <div class="col-xs-6">
                        {{ trans('content.like_count') . ' : ' . $data->like_count }}
                    </div>
                </div>
                <div class="row push-30-t">
                    <div class="col-xs-6">
                        {{ trans('content.work_count') . ' : ' . $data->work_count }}
                    </div>
                    <div class="col-xs-6">
                        {{ trans('content.users_count') . ' : ' . $data->users_count }}
                    </div>
                </div>

                <div class="row push-30-t">
                    <div class="col-xs-6">
                        {{ trans('common.expires') . ' : ' . date('Y-m-d H:i:s',$data->expires) }}
                    </div>
                    <div class="col-xs-6">
                        {{ trans('common.active') . ' : ' . (0 == $data->status ? '未完成' : (1 == $data->status ? '已完成' : '失败')) }}
                    </div>
                </div>

                <div class="row push-30-t">
                    <div class="col-xs-6">
                        {{ '推荐开始日期' . ' : ' . ($data->recommend_start ? date('Y-m-d H:i:s',$data->recommend_start) : '未设置') }}
                    </div>
                    <div class="col-xs-6">
                        {{ '推荐结束日期' . ' : ' . ($data->recommend_expires ? date('Y-m-d H:i:s',$data->recommend_expires) : '未设置') }}
                    </div>
                </div>


                {{--暂时不需要背景图片--}}
                {{--<div class="row push-30-t">--}}
                    {{--<div class="col-xs-6" style="margin-bottom: 10px;">--}}
                        {{--{{ trans('common.icon') . ' : '}}--}}
                    {{--</div>--}}
                    {{--<div class="col-xs-6 col-md-offset-3 remove-padding">--}}
                        {{--<img class="img-responsive img-thumbnail" src="{{ CloudStorage::downloadUrl($data->icon) }}">--}}
                    {{--</div>--}}
                {{--</div>--}}
                {{--@if(!empty($data->hasOneExtension))--}}
                    {{--<div class="row push-30-t">--}}
                        {{--<div class="col-xs-6" style="margin-bottom: 10px;">--}}
                            {{--{{ trans('content.video_comment') . ' : '}}--}}
                        {{--</div>--}}
                        {{--<video id="my_video_1" class="col-xs-8 col-md-offset-2 video-js vjs-default-skin" controls--}}
                               {{--preload="auto"  poster="{{ CloudStorage::downloadUrl($data->hasOneExtension['screen_shot']) }}"--}}
                               {{--data-setup="{}" webkit-playsinline>--}}
                            {{--<source src="{{ CloudStorage::downloadUrl($data->hasOneExtension['video']) }}" type="video/mp4">--}}
                        {{--</video>--}}
                    {{--</div>--}}
                {{--@endif--}}
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
    @parent
    <link href="https://cdn.bootcss.com/video.js/5.10.2/video-js.min.css" rel="stylesheet">
@endsection

@section('scripts')
    @parent
    <script src="https://cdn.bootcss.com/video.js/5.10.2/video.min.js"></script>
    <script>
        videojs.options.flash.swf = "{{ asset('js/plugins/videojs/video-js.swf')  }}";
    </script>
@endsection