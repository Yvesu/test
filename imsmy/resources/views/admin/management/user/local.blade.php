@extends('admin/layer')

@section('layer-content')
    <div id="alert-div" class="col-lg-2 col-md-4 col-xs-5 content-alert"></div>
    <!-- Page Header -->
    <div class="content bg-gray-lighter">
        <div class="row items-push">
            <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
                <ol class="breadcrumb push-10-t">
                    <li>用户管理</li>
                    <li><a class="link-effect" href="">手机用户</a></li>
                </ol>
            </div>
            <div class="col-sm-12 col-xs-12">
                <center>
                    <h2 class="page-heading" >
                        手机用户管理
                    </h2>
                </center>

            </div>

        </div>
    </div>
    <!-- END Page Header -->
    <div class="content">
        <!-- Dynamic Table Full -->

            <div class="block-content tab-content">
                {{--搜索--}}
                <form action="/admin/user/list/local" method="get">
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
                        <th class="col-md-1 text-center">用户ID</th>
                        <th class="col-md-2 text-center">用户名</th>
                        <th class="hidden-xs col-md-2">昵称</th>
                        <th class="hidden-xs col-md-1">所在位置</th>
                        <th class="hidden-xs col-md-2">创建时间</th>
                        <th class="hidden-xs col-md-2">更新时间</th>
                        <th class="text-center col-md-1">操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    <!-- 成员列表 使用了laravel Eager Loading -->
                    @foreach($users as $user)
                        <tr>
                            <td class="text-center">{{ $user->user_id }}</td>
                            <td class="font-w600 text-center">{{ $user->username }}</td>
                            <td class="hidden-xs">{{ $user->nickname }}</td>
                            <td class="hidden-xs">{{ $user->location }}</td>
                            <td class="hidden-xs">{{ $user->created_at }}</td>
                            <td class="hidden-xs">{{ $user->updated_at }}</td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <button class="btn btn-xs btn-default" type="button" data-target="#admin-{{ $user->user_id }}" data-toggle="modal" title="详情"><i class="fa fa-align-justify"></i></button>
                                </div>&nbsp;
                                <div class="btn-group">
                                    <a class="btn btn-xs btn-default" href="/admin/user/list/delete?id={{ $user->user_id }}" title="删除"><i class="fa fa-trash"></i></a>
                                </div>
                                <!-- 模拟态 弹出框开始 -->
                                <div class="modal fade" id="admin-{{ $user->user_id }}" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;">
                                    <div class="modal-dialog modal-dialog-popin">
                                        <div class="modal-content">
                                            <div class="block block-themed block-transparent">
                                                <div class="block-header bg-primary-dark">
                                                    <ul class="block-options">
                                                        <li>
                                                            <button data-dismiss="modal" type="button"><i class="fa fa-times-circle"></i></button>
                                                        </li>
                                                    </ul>
                                                    <h3 class="block-title">详情信息</h3>
                                                </div>
                                                <div class="block-content">
                                                    <div class="row form-horizontal ">
                                                        <div class="col-md-3">
                                                            <img src="{{ asset(CloudStorage::downloadUrl($user->avatar ? $user->avatar : 'topic/13/14804880275506830_300*300_.png')) }}" style="max-height:128px;max-width:128px">
                                                        </div>
                                                        <div class="col-md-9">
                                                            <div class="row">
                                                                <div class="col-md-6 text-left" style="margin-top: 10px">
                                                                    <span>
                                                                        <strong>用户ID:</strong> <ins>{{ $user->user_id }} </ins>
                                                                    </span>
                                                                </div>
                                                                <div class="col-md-6 text-left" style="margin-top: 10px">
                                                                    <span>
                                                                        <strong>用户名:</strong> <ins>{{ $user->username }} </ins>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-6 text-left" style="margin-top: 10px">
                                                                    <span>
                                                                        <strong>昵称:</strong> <ins>{{ $user->nickname }} </ins>
                                                                    </span>
                                                                </div>
                                                                <div class="col-md-6 text-left" style="margin-top: 10px">
                                                                    <span>
                                                                        <strong>性别:</strong> <ins>{{ $user->sex == 0 ? '女' : '男' }} </ins>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-6 text-left" style="margin-top: 10px">
                                                                    <span>
                                                                        <strong>所在位置:</strong> <ins>{{ $user->location }} </ins>
                                                                    </span>
                                                                </div>
                                                                <div class="col-md-6 text-left" style="margin-top: 10px">
                                                                    <span>
                                                                        <strong>个人签名:</strong> <ins>{{ $user->signature }} </ins>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-6 text-left" style="margin-top: 10px">
                                                                    <span>
                                                                        <strong>手机型号:</strong> <ins>{{ $user->phone_model }} </ins>
                                                                    </span>
                                                                </div>
                                                                <div class="col-md-6 text-left" style="margin-top: 10px">
                                                                    <span>
                                                                        <strong>陌生人消息:</strong> <ins>{{ $user->xmpp == 0 ? '关闭' : '开启' }} </ins>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-12 text-left" style="margin-top: 10px">
                                                                    <span>
                                                                        <strong>创建时间:</strong> <ins>{{ $user->created_at }} </ins>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-12 text-left" style="margin-top: 10px">
                                                                    <span>
                                                                        <strong>更新时间:</strong> <ins>{{ $user->updated_at }} </ins>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                {{--<div class="col-md-12 text-left" style="margin-top: 10px">--}}
                                                                    {{--<button class="btn btn-sm btn-danger" type="button" onclick="App.blocks('#my-block', 'close');">Close</button>--}}
                                                                {{--</div>--}}
                                                                <div class="col-md-12 " style="margin-top:10px">
                                                                    <a class="btn btn-sm btn-danger" type="button" href="/admin/user/list/delete?id={{ $user->user_id }}" onclick="App.blocks('#my-block', 'close');">屏 蔽</a>
                                                                </div>
                                                            </div>
                                                            <br>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            {{--<div class="modal-footer">--}}
                                                {{--@if(is_null($administrator->deleted_at))--}}
                                                    {{--<form action="/admin/management/administrator/disabled/{{$administrator->id}}" method="post">--}}
                                                        {{--<input type="hidden" name="_token" value="{{ csrf_token()}}">--}}
                                                        {{--@if($administrator->id != \Auth::guard('web')->user()->id)--}}
                                                            {{--<button class="btn btn-sm bg-red" type="submit" >{{ trans('management.disabled') }}</button>--}}
                                                        {{--@endif--}}
                                                        {{--<a class="btn btn-sm btn-success" type="button" href="{{ asset('admin/management/administrator/view-more/'.$administrator->id) }}">{{ trans('management.view_more') }}</a>--}}
                                                    {{--</form>--}}
                                                {{--@else--}}
                                                    {{--<form action="/admin/management/administrator/enabled/{{$administrator->id}}" method="post">--}}
                                                        {{--<input type="hidden" name="_token" value="{{ csrf_token()}}">--}}
                                                        {{--@if($administrator->id != \Auth::guard('web')->user()->id)--}}
                                                            {{--<button class="btn btn-sm bg-red" type="submit" >{{ trans('management.enable') }}</button>--}}
                                                        {{--@endif--}}
                                                        {{--<a class="btn btn-sm btn-success" type="button" href="{{ asset('admin/management/administrator/view-more/'.$administrator->id) }}">{{ trans('management.view_more') }}</a>--}}
                                                    {{--</form>--}}
                                                {{--@endif--}}
                                            {{--</div>--}}
                                        </div>
                                    </div>
                                </div>
                                <!-- 模拟态 弹出框结束 -->
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                <!-- 分页 -->
                <div class="dataTables_paginate paging_full_numbers col-md-offset-4" id="pages" style="">
                    {!! $users->appends($request)->render() !!}
                </div>
                {{--@if(is_null($departmentID))--}}
                    {{--{!! (new \App\Services\Presenter($administrators))->render() !!}<div class="tab-pane active">--}}
                        {{--@elseif(!is_null($departmentID) && 0 == $departmentID)--}}
                            {{--{!! (new \App\Services\Presenter($administrators->appends(['departmentID' => 0])))->render() !!}<div class="tab-pane active">--}}
                                {{--@else--}}
                                    {{--{!! (new \App\Services\Presenter($administrators->appends(['departmentID' => $departmentID])))->render() !!}<div class="tab-pane active">--}}
                                        {{--@endif--}}

            </div>


    </div>
        {{--</div>--}}
        <!-- END Dynamic Table Full -->
{{--</div>--}}
            <!-- END Page Content -->
            @endsection

            @section('css')
                @parent

                <link rel="stylesheet" href="{{ asset('css/admin/manage_family.css') }}">

@endsection