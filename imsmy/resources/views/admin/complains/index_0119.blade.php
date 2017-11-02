@extends('admin/layer')
@section('layer-content')
        <!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.complain') }}</li>
                <li><a class="link-effect" href="{{ asset('/admin/complains/index') }}">{{trans('common.complain_management')}}</a></li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            <h1 class="page-heading" >
                {{trans('common.complain_management')}}
            </h1>
        </div>
    </div>
</div>
<!-- END Page Header -->
<div class="content">
    <div class="block">
        <ul class="nav nav-tabs nav-tabs-alt">
            <li class="{{ isStyleLabel('style',1,'active','') }}">
                <a href="{{ asset('/admin/complains/index?style=1') }}">{{ trans('content.already') }}</a>
            </li>
            <li class="{{ isStyleLabel('style',0,'active','') }}">
                <a href="{{ asset('/admin/complains/index?style=0') }}">{{ trans('content.wait') }}</a>
            </li>
        </ul>
        <div class="block-content tab-content">
            <div class="block-content tab-content">
                <!-- DataTables init on table by adding .js-dataTable-full class, functionality initialized in js/pages/base_tables_datatables.js -->
                <table class="table table-bordered table-striped js-dataTable-full">
                    <thead>
                    <tr>
                        <th class="col-md-1 text-center">{{ trans('content.number') }}</th>
                        <th class="col-md-1 text-center">{{ trans('content.cause') }}</th>
                        <th class="hidden-xs col-md-1 text-center">{{ trans('content.submitter') }}</th>
                        <th class="hidden-xs col-md-2 text-center">{{ trans('content.complain_type') }}</th>
                        <th class="hidden-xs col-md-2 text-center">{{ trans('content.submit_time') }}</th>
                        <th class="hidden-xs col-md-1 text-center">{{ trans('content.handle') }}</th>
                        <th class="hidden-xs col-md-2 text-center">{{ trans('content.complain_time') }}</th>
                        <th class="hidden-xs col-md-1 text-center">{{ trans('content.complain_result') }}</th>
                        <th class="col-md-2 text-center">{{ trans('common.detail_info') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($complains as $complain)
                        <tr>
                            <td class="text-center">{{ $complain->id }}</td>
                            <td class="text-center">{{ $complain->belongsToCause->content }}</td>
                            <td class="hidden-xs text-center">{{ $complain->user_id }}</td>
                            <td class="hidden-xs text-center">{{ $complain -> type }}</td>
                            <td class="hidden-xs text-center">{{ date('Y-m-d H:i:s',$complain -> time_add) }}</td>
                            <td class="hidden-xs text-center">{{ $complain -> user_name ? $complain -> user_name : '尚未处理' }}</td>
                            <td class="hidden-xs text-center">{{ $complain -> time_update ? date('Y-m-d H:i:s',$complain -> time_update) : '尚未处理' }}</td>
                            <td class="hidden-xs text-center">{{ $complain -> status === 0 ? '尚未处理' : ($complain -> status === 1 ? '正常' : '屏蔽') }}</td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a class="btn btn-xs btn-default" title="详情" href="{{ asset('/admin/complains/details?id=' . $complain->id) }}"><i class="fa fa-pencil"></i></a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <!-- 分页 -->
                <div class="dataTables_paginate paging_full_numbers col-md-offset-4" id="pages" style="">
                    {!! $complains->appends($request)->render() !!}
                </div>
                <div class="tab-pane active"></div>
            </div>
        </div>
    </div>
</div>
@endsection
