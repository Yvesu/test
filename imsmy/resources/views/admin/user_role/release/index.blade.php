@extends('admin/layer')
@section('layer-content')
        <!-- Page Header -->

<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.user_role') }}</li>
                <li><a class="link-effect" href="{{ asset('/admin/user/role/index') }}">{{trans('common.user_role_management')}}</a></li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            <h1 class="page-heading" >
                {{trans('common.user_role_management')}}
            </h1>
        </div>
    </div>
</div>
<div class="content">
    <div class="block">
        <ul class="nav nav-tabs nav-tabs-alt">
            <li class="{{ isActiveLabel('active',1,'active','') }}">
                <a href="{{ asset('admin/user/role/index?active=1') }}">{{ trans('content.normal') }}</a>
            </li>
            <li class="{{ isActiveLabel('active',2,'active','') }}">
                <a href="{{ asset('admin/user/role/index?active=2') }}">{{ trans('content.disable') }}</a>
            </li>
        </ul>
        <div class="block-content tab-content">
            <div class="block-content tab-content">
                {{--搜索--}}
                <form action="/admin/user/role/index" method="get">
                    <div class="col-sm-6 col-md-4 hidden-xs">
                        <div id="DataTables_Table_1_length" class="dataTables_length">
                            <!-- 每页显示页码选择 -->
                            <label>显示
                                <select name="num" size="1" aria-controls="DataTables_Table_1">
                                    <!-- 每页显示条数 -->
                                    @for ($i = 10; $i < 120; $i=$i*2)
                                        @if ($request['num'] == $i)
                                            <option value="{{ $i }}" selected>{{ $i }}</option>
                                        @else
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endif
                                    @endfor
                                </select>条数据
                            </label>
                        </div>
                    </div>
                    <!-- 搜索 -->
                    <div class="col-sm-6 col-md-4 pull-right" style="margin-bottom: 10px;">
                        <div id="DataTables_Table_1_length" class="dataTables_length col-md-4">
                            <!-- 搜索条件 -->
                            <label>
                                <select name="condition" size="1" aria-controls="DataTables_Table_1">
                                    @for ($i = 1; $i <= count($condition); $i++)
                                        @if ($request['condition'] == $i)
                                            <option value="{{ $i }}" selected>{{ $condition[$i] }}</option>
                                        @else
                                            <option value="{{ $i }}">{{ $condition[$i] }}</option>
                                        @endif
                                    @endfor
                                </select>
                            </label>
                        </div>
                        <div class="form-material form-material-primary input-group remove-margin-t remove-margin-b col-md-8">
                            <input type="hidden" name="active" value="{{ $request['active'] }}">
                            <input class="" type="text" id="base-material-text" name="search" placeholder="{{ trans('common.search') }}.." value="{{ $request['search'] }}">
                            {{ csrf_field() }}
                            <button class="input-group-addon" type="submit" style="box-shadow:none"><i class="fa fa-search"></i></button>
                        </div>
                    </div>
                </form>
                <!-- DataTables init on table by adding .js-dataTable-full class, functionality initialized in js/pages/base_tables_datatables.js -->
                <table class="table table-bordered table-striped js-dataTable-full">
                    <thead>
                    <tr>
                        <th class="col-md-1 text-center">ID</th>
                        <th class="col-md-1">{{ trans('content.sponsor') }}</th>
                        <th class="col-md-1">{{ trans('content.film_title') }}</th>
                        <th class="hidden-xs col-md-1">{{ trans('content.director') }}</th>
                        <th class="hidden-xs col-md-1">{{ trans('content.period') }}</th>
                        <th class="hidden-xs col-md-1">{{ trans('content.film_name') }}</th>
                        <th class="hidden-xs col-md-1">{{ trans('content.from_time') }}</th>
                        <th class="hidden-xs col-md-1">{{ trans('content.end_time') }}</th>
                        <th class="hidden-xs col-md-1">{{ trans('content.submit_time') }}</th>
                        <th class="text-center col-md-1">{{ trans('common.detail_info') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($datas as $data)
                        <tr>
                            <td class="text-center">{{ $data->id }}</td>
                            <td class="font-w600">{{ $data->user_id }}</td>
                            <td class="font-w600">{{ $data->title }}</td>
                            <td class="hidden-xs">{{ $data->director }}</td>
                            <td class="hidden-xs">{{ $data->period }}</td>
                            <td class="hidden-xs">{{ $data->hasOneFilm['name'] }}</td>
                            <td class="hidden-xs">{{ $data->time_from }}</td>
                            <td class="hidden-xs">{{ $data->time_end }}</td>
                            <td class="hidden-xs">{{ date('Y-m-d H:i:s',$data->time_add) }}</td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a class="btn btn-xs btn-default" title="编辑" href="{{ asset('/admin/user/role/details?id=' . $data->id) }}"><i class="fa fa-pencil"></i></a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="dataTables_paginate paging_full_numbers col-md-offset-4" id="pages" style="">
                    {!! $datas->appends($request)->render() !!}
                </div>

                <div class="tab-pane active"></div>
            </div>
        </div>
    </div>
</div>

@endsection
