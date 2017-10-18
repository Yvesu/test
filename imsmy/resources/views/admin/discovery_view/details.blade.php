@extends('admin.layer')
@section('layer-content')
        <!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.advertisement') }}</li>
                <li><a class="link-effect" href="{{ asset('/admin/advertisement/view/index') }}">{{trans('common.view_channel')}}</a></li>
                <li><a class="link-effect" href="{{ asset('/admin/advertisement/view/details?type='.$ads->type.'&type_id=' . $ads->id) }}">{{ $ads->id }}</a></li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            <h1 class="page-heading" >
                {{ trans('common.view_channel')}}
            </h1>
        </div>
        <div class="col-sm-8 col-xs-3">
            <div class="pull-right">
                <form action="{{ asset('/admin/advertisement/view/update?id=' . $ads->id) }}" method="POST">
                    <input type="hidden" name="_token" value="{{ csrf_token()}}">
                    @if(0 == $ads->active)
                        <input type="hidden" name="active" value="1">
                        <button class="btn btn-sm bg-red" type="submit">
                            <i class="fa fa-ban"></i> {{ trans('common.enable') }}
                        </button>
                    @elseif(1 == $ads->active)
                        <input type="hidden" name="active" value="2">
                        <button class="btn btn-sm bg-red" type="submit">
                            <i class="fa fa-ban"></i> {{ trans('common.disable') }}
                        </button>
                    @endif
                    {{--<a class="btn btn-sm btn-success" type="button"--}}
                       {{--href="{{ asset('/admin/advertisement/channel/edit?id=' . $ads->id) }}">--}}
                        {{--<i class="fa fa-pencil-square-o"></i> {{trans('common.edit')}}--}}
                    {{--</a>--}}
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
                        {{ trans('content.submitter') . ' : ' . $ads->user_name }}
                    </div>

                </div>
                <div class="row push-30-t">
                    <div class="col-xs-6">
                        {{ trans('content.from_time') . ' : ' . date('Y-m-d H:i:s',$ads -> from_time) }}
                    </div>
                    <div class="col-xs-6">
                        {{ trans('content.end_time') . ' : ' . date('Y-m-d H:i:s',$ads -> end_time) }}
                    </div>
                </div>
                <div class="row push-30-t">
                    <div class="col-xs-6">
                        {{ trans('content.ads_type') . ' : ' . $ads->type }}
                    </div>
                    <div class="col-xs-6">
                        {{ trans('content.submit_time') }} : {{ date('Y-m-d H:i:s',$ads -> time_add) }}
                    </div>
                </div>
                <div class="row push-30-t">
                    <div class="col-xs-12">
                        {{ trans('content.ads_address') . ' : ' }}&nbsp;
                        @if($ads->type == 0)
                            <a class="btn btn-sm btn-success" type="button" href="{{ asset('/admin/content/video/' . $ads -> type_id) }}">
                        @elseif($ads->type == 1)
                            <a class="btn btn-sm btn-success" type="button" href="{{ asset('/admin/content/photo_album/' . $ads -> type_id) }}">
                        @elseif($ads->type == 2)
                            <a class="btn btn-sm btn-success" type="button" href="{{ asset('/admin/content/topic/' . $ads -> type_id) }}">
                        @elseif($ads->type == 3)
                            <a class="btn btn-sm btn-success" type="button" href="{{ asset('/admin/content/activity/' . $ads -> type_id) }}">
                        @else
                            <a class="btn btn-sm btn-success" type="button" href="{{ asset($ads -> url) }}">
                        @endif
                        {{--<a class="btn btn-sm btn-success" type="button" href="{{ asset('') }}">--}}
                            {{trans('common.check')}}
                        </a>
                    </div>
                </div>
                <div class="row push-30-t">
                    <div class="col-xs-12 remove-padding">
                        <img class="img-responsive img-thumbnail" src="{{ CloudStorage::downloadUrl($ads->image) }}">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
