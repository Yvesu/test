@extends('admin.layer')
@section('layer-content')
        <!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.content') }}</li>
                <li><a class="link-effect" href="{{ asset('/admin/content/video') }}">{{trans('common.video')}}</a></li>
                <li><a class="link-effect" href="{{ asset('/admin/content/video/' . $video->id) }}">{{ $video->id }}</a></li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            <h1 class="page-heading" >
                {{ trans('common.video')}}
            </h1>
        </div>
        <div class="col-sm-8 col-xs-3">
            <div class="pull-right">
                <form action="{{ asset('/admin/content/video/' . $video->id) }}" method="POST">
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="_token" value="{{ csrf_token()}}">
                    @if(0 == $video->active)
                        <input type="hidden" name="active" value="1">
                        <button class="btn btn-sm bg-red" type="submit">
                            <i class="fa fa-ban"></i> {{ trans('common.enable') }}
                        </button>
                    @else
                        <input type="hidden" name="active" value="0">
                        <button class="btn btn-sm bg-red" type="submit">
                            <i class="fa fa-ban"></i> {{ trans('common.disable') }}
                        </button>
                    @endif
                    <a class="btn btn-sm btn-success" type="button"
                           href="{{ asset('/admin/content/video/' . $video->id . '/edit') }}">
                            <i class="fa fa-pencil-square-o"></i> {{trans('common.edit')}}
                        </a>
                </form>
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
                        <h4>{{ trans('content.content') }} : {{ $video->content }} </h4>
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