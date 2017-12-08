@extends('admin.layer')
@section('layer-content')
        <!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.cinema') }}</li>
                <li><a class="link-effect" href="{{ asset('/admin/cinema/picture/index') }}">
                        {{trans('common.picture_management')}}
                    </a>
                </li>
                <li>{{ $data->id }}</li>
            </ol>
        </div>

        <div class="col-md-8 col-md-offset-4">
            <div class="pull-right">
                <form action="{{ asset('/admin/cinema/picture/update') }}" method="POST">
                    <input type="hidden" name="id" value="{{ $data->id }}">
                    <input type="hidden" name="_token" value="{{ csrf_token()}}">
                    @if(0 == $data->active)
                        <input type="hidden" name="active" value="1">
                        <button class="btn btn-sm bg-red" type="submit">
                            <i class="fa fa-ban"></i> {{ trans('common.enable') }}
                        </button>
                    @elseif(1 == $data->active)
                        <input type="hidden" name="active" value="2">
                        <button class="btn btn-sm bg-red" type="submit">
                            <i class="fa fa-ban"></i> {{ trans('common.delete') }}
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
                        {{ trans('content.cinema_name') . ' : ' . $data -> hasOneCinema['name'] }}
                    </div>
                    <div class="col-xs-6">
                        {{ trans('content.complain_result') . ' : ' . $data -> status === 0 ? '待审批' : '正常' }}
                    </div>
                </div>

                <div class="row push-30-t">
                    <div class="col-xs-6">
                        {{ trans('content.add_time') . ' : ' . date('Y-m-d H:i:s',$data -> time_add) }}
                    </div>
                    <div class="col-xs-6">
                        {{ trans('content.update_time') . ' : ' . date('Y-m-d H:i:s',$data -> time_update) }}
                    </div>
                </div>
                
                <div class="row push-30-t">
                    <div class="col-xs-6" style="margin-bottom: 10px;">
                        {{ trans('common.cover') . ' : '}}
                    </div>
                    <div class="col-xs-6 col-md-offset-3 remove-padding">
                        <img class="img-responsive img-thumbnail" src="{{ CloudStorage::downloadUrl($data->picture) }}">
                    </div>
                </div>
                    
            </div>
        </div>
    </div>
</div>
@endsection
