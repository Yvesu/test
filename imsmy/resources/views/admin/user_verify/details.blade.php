@extends('admin.layer')
@section('layer-content')
        <!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.verify') }}</li>
                <li><a class="link-effect" href="{{ asset('/admin/verify/index') }}">{{trans('common.verify_management')}}</a></li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            <h1 class="page-heading" >
                {{ trans('common.verify')}}
            </h1>
        </div>
        <div class="col-sm-8 col-xs-3">
            <div class="pull-right">
                <form action="{{ asset('/admin/verify/update') }}" method="POST">
                    <input type="hidden" name="id" value="{{ $data->id }}">
                    <input type="hidden" name="_token" value="{{ csrf_token()}}">
                    @if(0 == $data->verify_status)
                        <input type="hidden" name="active" value="1">
                        <button class="btn btn-sm bg-red" type="submit">
                            <i class="fa fa-ban"></i> {{ trans('common.pass') }}
                        </button>
                    @elseif(1 == $data->verify_status)
                        <input type="hidden" name="active" value="2">
                        <button class="btn btn-sm bg-red" type="submit">
                            <i class="fa fa-ban"></i> {{ trans('common.not_pass') }}
                        </button>
                    @endif
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
                        {{ trans('content.submitter') . ' : ' . $data->user_id }}
                    </div>
                </div>
                <div class="row push-30-t">
                    <div class="col-xs-6">
                        {{ trans('content.submit_time') . ' : ' . date('Y-m-d H:i:s',$data -> time_add) }}
                    </div>
                    <div class="col-xs-6">
                        {{ trans('content.verify_type') . ' : ' . $data -> verify == 1 ? '个人认证' : '企业认证' }}
                    </div>
                </div>
                <div class="row push-30-t">
                    <div class="col-xs-12">
                        {{ trans('content.content') . ' : ' }}
                    </div>
                    <div class="col-xs-12">
                        {{ $data -> verify_info }}
                    </div>
                </div>
                {{--后期提交证件照--}}
                {{--<div class="row push-30-t">--}}
                    {{--<div class="col-xs-12 remove-padding">--}}
                        {{--<img class="img-responsive img-thumbnail" src="{{ CloudStorage::downloadUrl() }}">--}}
                    {{--</div>--}}
                {{--</div>--}}
            </div>
        </div>
    </div>
</div>
@endsection
