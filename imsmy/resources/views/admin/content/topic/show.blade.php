@extends('admin.layer')
@section('layer-content')
        <!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.content') }}</li>
                <li><a class="link-effect" href="{{ asset('/admin/content/topic') }}">{{trans('common.topic_content')}}</a></li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            {{--<h1 class="page-heading" >--}}
                {{--{{ trans('content.topic'). ' : ' .$topic->name}}--}}
            {{--</h1>--}}
        </div>
        <div class="col-sm-8 col-xs-3">
            <div class="pull-right">
                <form action="{{ asset('/admin/content/topic/' . $topic->id)  }}" method="POST">
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="_token" value="{{ csrf_token()}}">
                    @if(1 != $topic->active)
                        <input type="hidden" name="active" value="1">
                        <button class="btn btn-sm bg-red" type="submit">
                            <i class="fa fa-ban"></i> {{ trans('common.enable') }}
                        </button>
                        <a class="btn btn-sm btn-success" type="button"
                           href="{{ asset('/admin/content/topic/' . $topic->id . '/edit') }}">
                            <i class="fa fa-pencil-square-o"></i> {{trans('common.edit')}}
                        </a>
                    @else
                        <input type="hidden" name="active" value="2">
                        <button class="btn btn-sm bg-red" type="submit">
                            <i class="fa fa-ban"></i> {{ trans('common.disable') }}
                        </button>
                    @endif
                    <a class="btn btn-sm btn-success" type="button"
                       href="{{ asset('/admin/content/topic/' . $topic->id . '/recommend_channel') }}">
                        <i class="fa fa-pencil-square-o"></i> {{trans('content.recommend') . '&' . trans('content.channel')}}
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
                        {{ trans('content.topic') . ' : ' . $topic->name }}
                    </div>
                    <div class="col-xs-6">
                        {{ trans('content.sponsor') . ' : ' . $topic->official }}
                    </div>
                </div>

                <div class="row push-30-t">
                    <div class="col-xs-6">
                        {{ trans('content.forwarding_times') . ' : ' . $topic-> forwarding_times }}
                    </div>
                    <div class="col-xs-6">
                        {{ trans('content.like_count') . ' : ' . $topic-> like_count }}
                    </div>
                </div>
                <div class="row push-30-t">
                    <div class="col-xs-6">
                        {{ trans('content.work_count') . ' : ' . $topic-> work_count }}
                    </div>
                    <div class="col-xs-6">
                        {{ trans('content.users_count') . ' : ' . $topic-> users_count }}
                    </div>
                </div>

                <div class="row push-30-t">
                    <div class="col-xs-12">
                        {{ trans('content.content') . ' : ' }}
                    </div>
                    <div class="row push-30-t">
                        <div class="col-xs-12">
                            &nbsp;&nbsp;&nbsp;&nbsp;{{ $topic-> comment}}
                        </div>
                    </div>
                </div>
                <div class="row push-30-t">
                    <div class="col-xs-4" style="margin-bottom: 10px;">
                        {{ trans('common.icon') . ' : '}}
                    </div>
                    <div class="col-xs-2 remove-padding">
                        <img class="img-responsive img-thumbnail" src="{{ CloudStorage::downloadUrl($topic->icon) }}">
                    </div>
                </div>
                @if(!empty($video))
                    @if($video->video)
                        <div class="row push-30-t">
                            <div class="col-xs-6" style="margin-bottom: 10px;">
                                {{ trans('content.video_comment') . ' : '}}
                            </div>
                            <video id="my_video_1" class="col-xs-8 col-md-offset-2 video-js vjs-default-skin" controls
                                   preload="auto"  poster="{{ CloudStorage::downloadUrl($video->screen_shot) }}"
                                   data-setup="{}" webkit-playsinline>
                                <source src="{{ CloudStorage::downloadUrl($video->video) }}" type="video/mp4">
                            </video>
                        </div>
                    @else
                        <div class="row push-30-t">
                            <div class="col-xs-6" style="margin-bottom: 10px;">
                                {{ trans('common.cover') . ' : '}}
                            </div>
                            <div class="col-xs-6 col-md-offset-3 remove-padding">
                                <img class="img-responsive img-thumbnail" src="{{ CloudStorage::downloadUrl($video->photo) }}">
                            </div>
                        </div>
                    @endif
                @endif
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