@extends('admin.layer')
@section('layer-content')
        <!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.content') }}</li>
                <li><a class="link-effect" href="{{ asset('/admin/content/photo_album') }}">{{trans('common.photo_album')}}</a></li>
                <li><a class="link-effect" href="{{ asset('/admin/content/photo_album' . $tweet->id) }}">{{ $tweet->id }}</a></li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            <h1 class="page-heading" >
                {{ trans('common.photo_album')}}
            </h1>
        </div>
        <div class="col-sm-8 col-xs-3">
            <div class="pull-right">
                <form action="{{ asset('/admin/content/photo_album/' . $tweet->id) }}" method="POST">
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="_token" value="{{ csrf_token()}}">
                    @if(0 == $tweet->active)
                        <input type="hidden" name="active" value="1">
                        <button class="btn btn-sm bg-red" type="submit">
                            <i class="fa fa-ban"></i> {{ trans('common.enable') }}
                        </button>
                        {{--<a class="btn btn-sm btn-success" type="button"
                           href="{{ asset('/admin/content/video/' . $video->id . '/edit') }}">
                            <i class="fa fa-pencil-square-o"></i> {{trans('common.edit')}}
                        </a>--}}
                    @else
                        <input type="hidden" name="active" value="0">
                        <button class="btn btn-sm bg-red" type="submit">
                            <i class="fa fa-ban"></i> {{ trans('common.disable') }}
                        </button>
                    @endif
                    <a class="btn btn-sm btn-success" type="button"
                       href="{{ asset('/admin/content/photo_album/' . $tweet->id . '/edit') }}">
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
                        {{ trans('content.sponsor') . ' : ' . $tweet->user_id }}
                    </div>
                    <div class="col-xs-6">
                        {{ trans('common.location') . ' : ' . ($tweet->location === null ? trans('common.null') : $video->location) }}
                    </div>
                </div>
                <div class="row push-30-t">
                    <div class="col-xs-6">
                        {{ trans('content.retweet') . ' : ' . 'TODO' }}
                    </div>
                    <div class="col-xs-6">
                        {{ trans('content.comment') . ' : ' . 'TODO' }}
                    </div>
                </div>
                <div class="row push-30-t">
                    <div class="col-xs-6">
                        {{ trans('content.like') . ' : ' . 'TODO' }}
                    </div>
                </div>
                <div class="row push-30-t">
                    <div class="col-xs-12">
                        <h4>{{ trans('content.content') }} : {{ $tweet->content }} </h4>
                    </div>
                </div>
                <div class="row push-30-t">
                    @if (sizeof($tweet->photo) == 1)
                        <div class="row">
                            <div class="col-xs-12 remove-padding">
                                <img class="img-responsive img-thumbnail" src="{{ CloudStorage::downloadUrl($tweet->photo[0]) }}">
                            </div>
                        </div>
                    @else
                        @foreach(array_chunk($tweet->photo,3) as $chunk)
                            <div class="row">
                                @foreach($chunk as $photo)
                                    <div class="col-xs-4 remove-padding">
                                        <img class="img-responsive img-thumbnail" src="{{ CloudStorage::downloadUrl($photo) }}">
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    @endif
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
@endsection