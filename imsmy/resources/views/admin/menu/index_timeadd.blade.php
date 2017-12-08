@extends('admin/layer')
@section('layer-content')
        <!-- Page Header -->

<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.menu') }}</li>
                <li><a class="link-effect" href="{{ asset('/admin/menu/index') }}">{{trans('common.menu_management')}}</a></li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            <h1 class="page-heading" >
                {{trans('common.menu_management')}}
            </h1>
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
                        <th class="col-md-2">{{ trans('content.name') }}</th>
                        <th class="col-md-2">{{ trans('common.menu') }}</th>
                        <th class="hidden-xs col-md-1">{{ trans('content.path') }}</th>
                        <th class="hidden-xs col-md-1">{{ trans('common.active') }}</th>
                        <th class="hidden-xs col-md-1">{{ trans('content.show_nav') }}</th>
                        <th class="hidden-xs col-md-1">{{ trans('content.add_time') }}</th>
                        <th class="hidden-xs col-md-1">{{ trans('content.update_time') }}</th>
                        <th class="text-center col-md-1 col-xs-2">{{ trans('common.edit') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($datas as $k=>$data)
                        <tr class="{{ ($k%2==1)?'odd':'even' }}" {{$data -> status !=1 ? 'style=color:#CCC;' : ''}} {{ $data->pid == 0 ? 'style=background-color:#337ab7;color:#fff' : '' }}>
                            <td class="text-center">{{ $data->id }}</td>
                            <td class="">{{ $data->name }}</td>
                            <td class="">{{ $data->route }}</td>
                            <td class="hidden-xs">{{ $data->path }}</td>
                            <td class="hidden-xs">{{ $data->status == 1 ? '正常': '已删除' }}</td>
                            <td class="hidden-xs">{{ $data->show_nav ==1 ? '是': '否' }}</td>
                            <td class="hidden-xs">{{ date('Y-m-d H:i',$data->time_add) }}</td>
                            <td class="hidden-xs">{{ date('Y-m-d H:i',$data->time_update) }}</td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a class="btn btn-xs btn-default" title="编辑" href="{{ asset('/admin/menu/edit?id='.$data->id) }}"><i class="fa fa-pencil"></i></a>
                                </div>
                                <div class="btn-group">
                                    <a class="btn btn-xs btn-default" href="/admin/menu/delete?id={{ $data->id }}" title="删除"><i class="fa fa-trash"></i></a>
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
