@extends('admin/layer')
@section('layer-content')
        <!-- Page Header -->

<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.demand') }}</li>
                <li><a class="link-effect" href="{{ asset('/admin/config/demand/index') }}">{{trans('common.demand_management')}}</a></li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            <h1 class="page-heading" >
                {{trans('common.demand_management')}}
            </h1>
        </div>
        <div class="col-sm-8 col-xs-3">
            <!-- Single button -->
            <div class="btn-group pull-right" id="family-action">
                <a type="button" class="btn btn-primary" href="{{ asset('/admin/config/demand/add') }}">
                    {{ trans('content.add_demand') }}
                </a>
            </div>
        </div>
    </div>
</div>
<!-- END Page Header -->
<div class="content">
    <div class="block">
        <div class="block-content tab-content">
            <div class="col-md-12 col-xs-12 block">
                <table class="table table-bordered table-striped js-dataTable-full">
                    <thead>
                    <tr>
                        <th class="col-md-1 text-center">ID</th>
                        <th class="col-md-2 text-center">{{ trans('content.name') }}</th>
                        <th class="hidden-xs col-md-1 text-center">{{ trans('content.path') }}</th>
                        <th class="hidden-xs col-md-1 text-center">{{ trans('common.active') }}</th>
                        <th class="hidden-xs col-md-2 text-center">{{ trans('content.update_time') }}</th>
                        <th class="text-center col-md-1 col-xs-2 text-center">{{ trans('common.edit') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($datas as $k=>$data)
                        <tr class="{{ ($k%2==1)?'odd':'even' }}" {{$data -> active !=1 ? 'style=color:#CCC;' : ''}} {{ $data->pid == 0 ? 'style=background-color:#337ab7;color:#fff' : '' }}>
                            <td class="text-center">{{ $data->id }}</td>
                            <td class="">{{ $data->name }}</td>
                            <td class="hidden-xs">{{ $data->path }}</td>
                            <td class="hidden-xs text-center">{{ $data->active == 1 ? '正常': '已删除' }}</td>
                            <td class="hidden-xs text-center">{{ date('Y-m-d H:i:s',$data->time_update) }}</td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a class="btn btn-xs btn-default" title="编辑" href="{{ asset('/admin/config/demand/edit?id='.$data->id) }}"><i class="fa fa-pencil"></i></a>
                                </div>
                                <div class="btn-group">
                                    <a class="btn btn-xs btn-default" href="/admin/config/demand/delete?id={{ $data->id }}" title="删除"><i class="fa fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
