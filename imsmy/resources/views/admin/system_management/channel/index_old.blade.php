@extends('admin.layer')
@section('layer-content')
<!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.system_management') }}</li>
                <li><a class="link-effect" href="">{{trans('common.channel_content')}}</a></li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            <h1 class="page-heading" >
                {{trans('common.channel_content')}}
            </h1>
        </div>
        <div class="col-sm-8 col-xs-3">
            <!-- Single button -->
            <div class="btn-group pull-right" id="family-action">
                <a type="button" class="btn btn-primary" href="{{ asset('/admin/system_management/channel/create') }}">
                    {{ trans('content.add_channel') }}
                </a>
            </div>
        </div>
    </div>
</div>
<!-- END Page Header -->
<div class="col-xs-12">
    <div class="block-content">
        <h4 class="blur-nav">
            <a class="label {{ isActiveLabel('active',1) }}" href="{{ asset('admin/system_management/channel?active=1') }}"><i class="fa fa-play"></i>&nbsp;&nbsp;{{ trans('content.normal') }}</a>
            <a class="label {{ isActiveLabel('active',0) }}" href="{{ asset('admin/system_management/channel?active=0') }}"><i class="fa fa-stop"></i>&nbsp;&nbsp;{{ trans('content.disable') }}</a>
        </h4>
        <div class="row push-30-t">
            @foreach($channels as $channel)
                <div class="col-sm-6 col-lg-3">
                    <a class="block block-link-hover2" href="{{ asset('admin/system_management/channel/' . $channel->id) }}">
                        <div class="block-content block-content-full text-center" >
                            <div>
                                <img class="img-avatar img-avatar96"
                                     src="{{ CloudStorage::downloadUrl($channel->icon) }}"
                                     alt="" style="background-color: {{ random_color() }}">
                            </div>
                            <div class="h5 push-15-t push-5">{{ $channel->name }}</div>
                            {{--<div class="text-muted">Web Designer</div>--}}
                            <div class="text-muted push-15-t">
                                <div class="row">
                                    <div class="col-xs-6 text-right">
                                        {{ trans('content.forwarding_time') }}
                                    </div>
                                    <div class="col-xs-6 text-left">
                                        {{ $channel->forwarding_time }}
                                    </div>
                                    <div class="col-xs-6 text-right">
                                        {{ trans('content.comment_time') }}
                                    </div>
                                    <div class="col-xs-6 text-left">
                                        {{ $channel->comment_time }}
                                    </div>
                                    <div class="col-xs-6 text-right">
                                        {{ trans('content.work_count') }}
                                    </div>
                                    <div class="col-xs-6 text-left">
                                        {{ $channel->work_count }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection