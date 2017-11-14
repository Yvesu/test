@extends('admin.layer')
@section('layer-content')
        <!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.system_management') }}</li>
                <li><a class="link-effect" href="{{ asset('/admin/system_management/trophy') }}">{{trans('common.trophy_content')}}</a></li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            <h1 class="page-heading" >
                {{ trans('common.trophy')}}
            </h1>
        </div>
        <div class="col-sm-8 col-xs-3">
            <div class="pull-right">
                <form action="{{ asset('/admin/system_management/trophy/' . $trophy->id) }}" method="POST">
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="_token" value="{{ csrf_token()}}">
                    @if(0 == $trophy->status)
                        <input type="hidden" name="active" value="1">
                        <button class="btn btn-sm bg-red" type="submit">
                            <i class="fa fa-ban"></i> {{ trans('common.enable') }}
                        </button>
                    @elseif(1 == $trophy->status)
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
                        {{ trans('content.trophy_name') . ' : ' . $trophy->name }}
                    </div>
                    <div class="col-xs-6">
                        {{ trans('content.gold_num') . ' : ' . $trophy->num }}
                    </div>
                </div>
                <div class="row push-30-t">
                    <div class="col-xs-6">
                        {{ trans('content.from_time') . ' : ' . date('Y-m-d H:i:s',$trophy -> time_active_start) }}
                    </div>
                    <div class="col-xs-6">
                        {{ trans('content.end_time') . ' : ' . date('Y-m-d H:i:s',$trophy -> time_active_end) }}
                    </div>
                </div>
                <div class="row push-30-t">

                    <div class="col-xs-6">
                        {{ trans('content.submit_time') }} : {{ date('Y-m-d H:i:s',$trophy -> time_add) }}
                    </div>
                </div>

                <div class="row push-30-t">
                    <div class="col-xs-12 remove-padding">
                        <img class="img-responsive img-thumbnail" src="{{ CloudStorage::downloadUrl($trophy->picture) }}" style="width:50%">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
