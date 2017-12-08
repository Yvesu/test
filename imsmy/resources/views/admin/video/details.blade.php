@extends('admin.layer')
@section('layer-content')
        <!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.video') }}</li>
                <li>
                    <a class="link-effect" href="{{ asset($active == 1 ? '/admin/video/index' : ($active == 2 ? '/admin/video/recycle' : '/admin/video/check')) }}">
                        {{ ($active == 1 ? trans('content.normal') : ($active == 2 ? trans('common.shield') : trans('content.wait'))).trans('common.video') }}
                    </a>
                </li>
                <li><a class="link-effect" href="{{ asset('/admin/video/details?id=' . $video->id) }}">{{ $video->id }}</a></li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            <h1 class="page-heading" >
                {{ trans('common.video')}}
            </h1>
        </div>
        <div class="col-sm-8 col-xs-3">
            <div class="pull-right video-edit">
                @if($video->active == 1)
                    <button type="button" class="btn btn-sm bg-red" data="{{ $video->id }}" status=2 onclick="_videoActive($(this))">
                        {{ trans('common.shield') }}
                    </button>
                    {{--<button type="button" class="btn btn-sm bg-red" data="{{ $video->id }}" status="{{ $video->active == 1 ? 2 : 1 }}" onclick="_videoActive($(this))">--}}
                        {{--{{ $video->active == 1 ? '屏蔽' : '通过' }}--}}
                    {{--</button>--}}
                @endif
                <a class="btn btn-sm btn-success" type="button" href="{{ asset('/admin/video/edit?id=' . $video->id ) }}">
                    {{trans('common.edit')}}
                </a>
            </div>
        </div>
    </div>
</div>
<!-- END Page Header -->
<div class="col-xs-12">
    <div class="block-content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="row push-30-t">
                    <div class="col-xs-6">
                        {{ trans('content.sponsor') . ' : ' . $video->user_id }}
                    </div>
                    <div class="col-xs-6">
                        {{ trans('common.location') . ' : ' . ($video->location === null ? trans('common.null') : $video->location) }}
                    </div>
                </div>
                <div class="row push-30-t">
                    <div class="col-xs-6">
                        {{ trans('content.channel') . ' : ' . $video-> channel_name}}
                    </div>
                    <div class="col-xs-6">
                        {{ trans('content.retweet') . ' : ' . $video-> retweet}}
                    </div>
                </div>
                <div class="row push-30-t">
                    <div class="col-xs-6">
                        {{ trans('content.browse_times') . ' : ' . $video-> browse_times }}
                    </div>
                    <div class="col-xs-6">
                        {{ trans('content.like_count') . ' : ' . $video-> like_count }}
                    </div>
                </div>
                <div class="row push-30-t">
                    <div class="col-xs-6">
                        {{ trans('content.reply_count') . ' : ' . $video-> reply_count }}
                    </div>
                    <div class="col-xs-6">
                        {{ trans('content.retweet_count') . ' : ' . $video-> retweet_count }}
                    </div>
                </div>
                <div class="row push-30-t">
                    <div class="col-xs-6">
                        {{ trans('content.tweet_grade_total') . ' : ' . $video-> tweet_grade_total }}
                    </div>
                    <div class="col-xs-6">
                        {{ trans('content.tweet_grade_times') . ' : ' . $video-> tweet_grade_times }}
                    </div>
                </div>
                <div class="row push-30-t">
                    <div class="col-xs-12">
                        <h4>{{ trans('content.content') }} : {{ $video->hasOneContent['content'] }} </h4>
                    </div>
                </div>
                <div class="row push-30-t">
                    <video id="my_video_1" class="col-xs-12 video-js vjs-default-skin" controls
                           preload="auto"  poster="{{ CloudStorage::downloadUrl($video->screen_shot) }}"
                           data-setup="{}" webkit-playsinline>
                        <source src="{{ CloudStorage::downloadUrl($video->video) }}" type="video/mp4">
                    </video>
                </div>
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
    <script type="text/javascript" src="{{ asset('/js/admin/video/video-handle.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/layer/layer.js') }}"></script>

    <script>
        videojs.options.flash.swf = "{{ asset('js/plugins/videojs/video-js.swf')  }}";
    </script>
    {{--<script>
        Lang.currentTime = null;
        $('#video').on('timeupdate',function(event){
            if(Lang.currentTime !== null && this.currentTime - Lang.currentTime > 1){
                this.currentTime = Lang.currentTime;
            } else {
                Lang.currentTime = this.currentTime;
            }

        });
    </script>--}}
@endsection