@extends('admin.layer')
@section('layer-content')
<!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.content') }}</li>
                <li><a class="link-effect" href="">{{trans('common.topic_content')}}</a></li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            <h1 class="page-heading" >
                {{trans('common.topic_content')}}
            </h1>
        </div>
        <div class="col-sm-8 col-xs-3">
            <!-- Single button -->
            <div class="btn-group pull-right" id="family-action">
                <a type="button" class="btn btn-primary" href="{{ asset('/admin/content/topic/create') }}">
                    {{ trans('content.add_topic') }}
                </a>
            </div>
        </div>
    </div>
</div>
<!-- END Page Header -->
<div class="content">
    <div class="block">
        <ul class="nav nav-tabs nav-tabs-alt">
            <li class="{{ isActiveLabel('active',1,'active','') }}">
                <a href="{{ asset('admin/content/topic?active=1') }}">{{ trans('content.normal') }}</a>
            </li>
            <li class="{{ isActiveLabel('official',1,'active','') }}">
                <a href="{{ asset('admin/content/topic?official=1') }}">{{ trans('content.official') }}</a>
            </li>
            <li class="{{ isActiveLabel('official',0,'active','') }}">
                <a href="{{ asset('admin/content/topic?official=0') }}">{{ trans('content.unofficial') }}</a>
            </li>
            <li class="{{ isActiveLabel('active',0,'active','') }}">
                <a href="{{ asset('admin/content/topic?active=0') }}">{{ trans('content.disable') }}</a>
            </li>
        </ul>
        <div class="block-content tab-content">
            <div class="block-content tab-content">
                {{--搜索--}}
                <form action="/admin/content/topic" method="get">
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
                            <input type="hidden" name="official" value="{{ $request['official'] }}">
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
                        <th class="col-md-2 text-center">{{ trans('content.topic') }}</th>
                        <th class="col-md-1">{{ trans('content.sponsor') }}</th>
                        <th class="col-md-3">{{ trans('content.content') }}</th>
                        <th class="hidden-xs col-md-1">{{ trans('content.forwarding_time') }}</th>
                        <th class="hidden-xs col-md-1">{{ trans('content.comment_time') }}</th>
                        <th class="hidden-xs col-md-1">{{ trans('content.work_count') }}</th>
                        <th class="hidden-xs col-md-1">{{ trans('content.users_count') }}</th>
                        <th class="text-center col-md-1 col-xs-2">{{ trans('common.detail_info') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                        @foreach($topics as $topic)
                            <tr>
                                <td class="text-center">{{ $topic->id }}</td>
                                <td class="text-center">{{ $topic->name }}</td>
                                <td class="font-w600">{{ $topic->user_id }}</td>
                                <td class="font-w600">{{ $topic->comment }}</td>
                                <td class="hidden-xs">{{ $topic->forwarding_time }}</td>
                                <td class="hidden-xs">{{ $topic->comment_time }}</td>
                                <td class="hidden-xs">{{ $topic->work_count }}</td>
                                <td class="hidden-xs">{{ $topic->users_count }}</td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a class="btn btn-xs btn-default" title="编辑" href="{{ asset('/admin/content/topic/' . $topic->id) }}"><i class="fa fa-pencil"></i></a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="dataTables_paginate paging_full_numbers col-md-offset-4" id="pages" style="">
                    {!! $topics->appends($request)->render() !!}
                </div>
                    {{--@if(\Request::get('active') !== null)--}}
                        {{--{!! (new \App\Services\Presenter($topics->appends(['active' => \Request::get('active')])))->render() !!}--}}
                    {{--@elseif(\Request::get('official') !== null)--}}
                        {{--{!! (new \App\Services\Presenter($topics->appends(['official' => \Request::get('official')])))->render() !!}--}}
                    {{--@endif--}}
                <div class="tab-pane active"></div>
            </div>
        </div>
    </div>
</div>
@endsection