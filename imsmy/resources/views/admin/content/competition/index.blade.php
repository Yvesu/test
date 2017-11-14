@extends('admin/layer')
@section('layer-content')
        <!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.activity').trans('common.bonus').trans('common.allocation') }}</li>
                <li>
                    <a class="link-effect" href="{{ asset('/admin/content/competition/index') }}">
                        {{ trans('common.bonus').trans('common.allocation').trans('common.management') }}
                    </a>
                </li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            <h1 class="page-heading" >
                {{ trans('common.bonus').trans('common.allocation') }}
            </h1>
        </div>
        {{--@if(session('admin') !== null)--}}
            {{--<div class="col-sm-8 col-xs-3">--}}
                {{--<!-- Single button -->--}}
                {{--<div class="btn-group pull-right" id="family-action">--}}
                    {{--<a type="button" class="btn btn-primary" href="{{ asset('/admin/content/competition/add') }}">--}}
                        {{--{{ trans('common.add') }}--}}
                    {{--</a>--}}
                {{--</div>--}}
            {{--</div>--}}
        {{--@endif--}}
    </div>
</div>
<!-- END Page Header -->
<div class="content">
    <div class="block">
        <ul class="nav nav-tabs nav-tabs-alt">
            <li class="{{ isActiveLabel('active',1,'active','') }}">
                <a href="{{ asset('/admin/content/competition/index?active=1') }}">{{ trans('content.normal') }}</a>
            </li>
            <li class="{{ isActiveLabel('active',0,'active','') }}">
                <a href="{{ asset('/admin/content/competition/index?active=0') }}">{{ trans('content.wait') }}</a>
            </li>
        </ul>
        <div class="block-content tab-content">
            <div class="block-content tab-content">
                <!-- DataTables init on table by adding .js-dataTable-full class, functionality initialized in js/pages/base_tables_datatables.js -->
                <table class="table table-bordered table-striped js-dataTable-full">
                    <thead>
                        <tr>
                            <th class="col-md-1 text-center">ID</th>
                            <th class="col-md-1 text-center">{{ trans('common.level') }}</th>
                            <th class="col-md-2 text-center">{{ trans('common.user').trans('common.count') }}</th>
                            <th class="col-md-1 text-center">{{ trans('common.bonus').trans('common.proportion') }}</th>
                            <th class="col-md-2 text-center">{{ trans('common.follower').trans('common.proportion') }}</th>
                            <th class="hidden-xs col-md-2 text-center">{{ trans('content.add_time') }}</th>
                            <th class="hidden-xs col-md-2 text-center">{{ trans('content.update_time') }}</th>
                            {{--<th class="col-md-1 col-xs-2 text-center">{{ trans('common.edit') }}</th>--}}
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($data as $value)
                        <tr>
                            <td class="text-center">{{ $value->id }}</td>
                            <td class="text-center">{{ $value->level }}</td>
                            <td class="hidden-xs text-center">{{ $value->count_user }}</td>
                            <td class="hidden-xs text-center">{{ $value->amount*100 }}%</td>
                            <td class="hidden-xs text-center">{{ $value->prorata*100 }}%</td>
                            <td class="hidden-xs text-center">{{ date('Y-m-d H:i:s',$value -> time_add) }}</td>
                            <td class="hidden-xs text-center">{{ date('Y-m-d H:i:s',$value -> time_update) }}</td>
                            {{--<td class="text-center">--}}
                                {{--<div class="btn-group">--}}
                                    {{--<a class="btn btn-xs btn-default" title="编辑" href="{{ asset('/admin/content/competition/edit?id=' . $value->id) }}"><i class="fa fa-pencil"></i></a>--}}
                                {{--</div>--}}
                            {{--</td>--}}
                        </tr>
                    @endforeach
                    </tbody>
                    {{--<thead>--}}
                    <tr>
                        <th class="col-md-1 text-center">合计</th>
                        <th class="col-md-1 text-center">{{ $data -> max('level') }}</th>
                        <th class="col-md-2 text-center">{{ $data -> sum('count_user') }}</th>
                        <th class="col-md-1 text-center">{{ $data -> sum('amount')*100 }}%</th>
                        <th class="col-md-2 text-center">{{ $data -> first() -> prorata*100 }}%</th>
                        <th class="hidden-xs col-md-2 text-center"> </th>
                        <th class="hidden-xs col-md-2 text-center"> </th>
                        {{--<th class="col-md-1 col-xs-2 text-center"> </th>--}}
                    </tr>
                    <tr>
                        <th class="col-md-1 text-center">编辑</th>
                        <th class="col-md-1 text-center"></th>
                        <th class="col-md-2 text-center">
                            <div class="btn-group">
                                <a class="btn btn-xs btn-default" title="编辑" href="{{ asset('/admin/content/competition/edit?type=1') }}"><i class="fa fa-pencil"></i></a>
                            </div>
                        </th>
                        <th class="col-md-1 text-center">
                            <div class="btn-group">
                                <a class="btn btn-xs btn-default" title="编辑" href="{{ asset('/admin/content/competition/edit?type=2') }}"><i class="fa fa-pencil"></i></a>
                            </div>
                        </th>
                        <th class="col-md-2 text-center">
                            <div class="btn-group">
                                <a class="btn btn-xs btn-default" title="编辑" href="{{ asset('/admin/content/competition/edit?type=3') }}"><i class="fa fa-pencil"></i></a>
                            </div>
                        </th>
                        <th class="hidden-xs col-md-2 text-center"> </th>
                        <th class="hidden-xs col-md-2 text-center"> </th>
                        {{--<th class="col-md-1 col-xs-2 text-center"> </th>--}}
                    </tr>
                    {{--</thead>--}}
                </table>
                <!-- 分页 -->
                <div class="dataTables_paginate paging_full_numbers col-md-offset-4" id="pages" style="">
                    {!! $data->appends($request)->render() !!}
                </div>
                <div class="tab-pane active"></div>
            </div>
        </div>
    </div>
</div>
@endsection
