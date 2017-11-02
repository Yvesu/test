@extends('admin.layer')
@section('layer-content')
        <!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.complain') }}</li>
                <li><a class="link-effect" href="{{ asset('/admin/complains/index') }}">{{trans('common.complain_management')}}</a></li>
                <li><a class="link-effect" href="{{ asset('/admin/complains/details?id=' . $complain->id) }}">{{ $complain->id }}</a></li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            <h1 class="page-heading" >
                {{ trans('common.complain_management')}}
            </h1>
        </div>
        <div class="col-sm-8 col-xs-3">
            <div class="pull-right">
                <form action="{{ asset('/admin/complains/update?id=' . $complain->id) }}" method="POST">
                    <input type="hidden" name="_token" value="{{ csrf_token()}}">
                    <input type="hidden" name="active" value="2">
                    <button class="btn btn-sm bg-red" type="submit">
                        <i class="fa fa-ban"></i> {{ trans('common.shield') }}
                    </button>
                </form>
            </div>
            <div class="pull-right" style="margin-right: 10px;">
                <form action="{{ asset('/admin/complains/update?id=' . $complain->id) }}" method="POST">
                    <input type="hidden" name="_token" value="{{ csrf_token()}}">
                    <input type="hidden" name="active" value="1">
                    <button class="btn btn-sm bg-success" type="submit" style="color:white">
                        <i class="fa fa-ban"></i> {{ trans('content.normal') }}
                    </button>
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
                        {{ trans('content.submitter') . ' : ' . $complain->user_id }}
                    </div>
                    <div class="col-xs-6">
                        {{ trans('content.cause') . ' : ' . $complain->belongsToCause->content }}
                    </div>
                </div>

                <div class="row push-30-t">
                    <div class="col-xs-6">
                        {{ trans('content.submit_time') . ' : ' . date('Y-m-d H:i:s',$complain -> time_add) }}
                    </div>
                    <div class="col-xs-6">
                        @if($complain -> user_name)
                            {{ trans('content.handle') . ' : ' . $complain -> user_name }}
                        @else
                            {{ trans('content.handle') . ' : ' . '尚未处理' }}
                        @endif
                    </div>
                </div>
                <div class="row push-30-t">
                    <div class="col-xs-6">
                        {{ trans('content.complain_result') }} : {{ $complain -> status === 1 ? '正常' : ($complain -> status === 2 ? '屏蔽' : '尚未处理')  }}
                    </div>
                    <div class="col-xs-6">
                        {{ trans('content.complain_time') }} : {{ $complain -> time_update ? date('Y-m-d H:i:s',$complain -> time_update) : '尚未处理' }}
                    </div>
                </div>
                <div class="row push-30-t">
                    <div class="col-xs-6">
                        {{ trans('content.complain_type') }} : {{ $complain -> type_name }}
                    </div>
                    <div class="col-xs-6">
                        {{ trans('common.detail_info') . ' : ' }}&nbsp;
                        @if($complain->type == 0)
                            <a class="btn btn-sm btn-success" type="button" href="{{ asset($data->type == 0 ? '/admin/content/video/'. $complain -> type_id : '/admin/content/photo_album/' . $complain -> type_id) }}">
                        @elseif($complain->type == 1)
                            <a class="btn btn-sm btn-success" type="button" href="{{ asset('/admin/content/topic/' . $complain -> type_id) }}">
                        @elseif($complain->type == 2)
                            <a class="btn btn-sm btn-success" type="button" href="{{ asset('/admin/content/activity/' . $complain -> type_id) }}">
                        @else
                            <a class="btn btn-sm btn-success" type="button" href="{{ asset('/admin/content/reply/' . $complain -> type_id) }}">
                        @endif
                            {{trans('common.check')}}
                        </a>
                    </div>
                </div>
                <div class="row push-30-t">
                    <div class="col-xs-12">
                        {{ trans('content.cause_content') . ' : ' . $complain->content }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
