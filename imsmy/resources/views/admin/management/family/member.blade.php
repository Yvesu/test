@extends('admin.layer')

@section('layer-content')
    <div id="alert-div" class="col-lg-2 col-md-4 col-xs-5 content-alert"></div>
    <!-- Page Header -->
    <div class="content bg-gray-lighter">
        <div class="row items-push">
            <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
                <ol class="breadcrumb push-10-t">
                    <li>{{ trans('common.management') }}</li>
                    <li><a class="link-effect" href="">{{trans('common.family_management')}}</a></li>
                </ol>
            </div>
            <div class="col-sm-4 col-xs-9">
                <h1 class="page-heading" >
                    {{trans('common.family_management')}}
                </h1>
            </div>
            <div class="col-sm-8 col-xs-3">
                <!-- Single button -->
                <div class="btn-group pull-right" id="family-action">
                    <a type="button" class="btn btn-primary" href="{{ asset('/admin/management/administrator') }}">
                        {{ trans('management.add_admin') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- END Page Header -->

    <!-- Page Content -->
    <div class="content">
        <!-- Dynamic Table Full -->
        <div class="block">
            <ul class="nav nav-tabs nav-tabs-alt">
                <li role="presentation" class="dropdown active">
                    <a  class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                        {{ $dept_tab_title }}
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        @if(is_null($departmentID))
                        <li class="active">
                        @else
                        <li>
                        @endif
                            <a href="{{ asset('/admin/management/family/member') }}" name="all">{{ trans('management.all_members') . '(' . $count_array['all'] . ')' }}</a>
                        </li>
                        @foreach($departments as $department)
                            @if(!is_null($departmentID) && $department->id == $departmentID)
                            <li class="active">
                            @else
                            <li>
                            @endif
                                @if(0 == $department->active)
                                <a href="{{ asset('/admin/management/family/member?departmentID=' . $department->id) }}" name="{{ $department->name }}">{{ $department->description . '(' . $count_array[$department->id] . ')'  }} ({{ trans('management.deactivated') }})</a>
                                @else
                                <a href="{{ asset('/admin/management/family/member?departmentID=' . $department->id) }}" name="{{ $department->name }}">{{ $department->description . '(' . $count_array[$department->id] . ')'  }}</a>
                                @endif
                            </li>
                        @endforeach
                        @if(!is_null($departmentID) && 0 == $departmentID)
                        <li class="active">
                        @else
                        <li>
                        @endif
                            <a href="{{ asset('/admin/management/family/member?departmentID=0') }}" name="all">{{ trans('management.disabled_members') . '(' . $count_array['disabled'] . ')'  }}</a>
                        </li>
                    </ul>
                </li>
            </ul>
            <div class="block-content tab-content">
                <!-- DataTables init on table by adding .js-dataTable-full class, functionality initialized in js/pages/base_tables_datatables.js -->
                <table class="table table-bordered table-striped js-dataTable-full">
                    <thead>
                    <tr>
                        <th class="text-center"></th>
                        <th>{{ trans('management.name') }}</th>
                        <th class="hidden-xs col-md-3 col-xs-3">{{ trans('management.department') }}</th>
                        <th class="hidden-xs col-md-3 col-xs-3">{{ trans('management.position') }}</th>
                        <th class="text-center col-md-1 col-xs-3">{{ trans('management.actions') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <!-- 成员列表 使用了laravel Eager Loading -->
                    @foreach($administrators as $administrator)
                        <tr>
                            <td class="text-center">{{ $administrator->id }}</td>
                            <td class="font-w600">{{ $administrator->name }}</td>
                            <td class="hidden-xs">{{ $administrator->belongsToPosition->belongsToDepartment->description }}</td>
                            @if(0 == $administrator->belongsToPosition->active)
                                <td class="hidden-xs">{{ $administrator->belongsToPosition->description }}({{ trans('management.deactivated')}})</td>
                            @else
                                <td class="hidden-xs">{{ $administrator->belongsToPosition->description }}</td>
                            @endif
                            <td class="text-center">
                                <div class="btn-group">
                                    <button class="btn btn-xs btn-default" type="button" data-target="#admin-{{ $administrator->id }}" data-toggle="modal"><i class="fa fa-pencil"></i></button>
                                </div>
                                <!-- 模拟态 弹出框开始 -->
                                <div class="modal fade" id="admin-{{ $administrator->id }}" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;">
                                    <div class="modal-dialog modal-dialog-popin">
                                        <div class="modal-content">
                                            <div class="block block-themed block-transparent">
                                                <div class="block-header bg-primary-dark">
                                                    <ul class="block-options">
                                                        <li>
                                                            <button data-dismiss="modal" type="button"><i class="fa fa-times-circle"></i></button>
                                                        </li>
                                                    </ul>
                                                    <h3 class="block-title">{{trans('common.detail_info')}}</h3>
                                                </div>
                                                <div class="block-content">
                                                    <div class="row form-horizontal ">
                                                        <div class="col-md-3">
                                                            <img src="{{ asset('/admin/images/'.$administrator->avatar) }}" style="max-height:128px;max-width:128px">
                                                        </div>
                                                        <div class="col-md-9">
                                                            <div class="row">
                                                                <div class="col-md-6 text-left" style="margin-top: 10px">
                                                                    <span>
                                                                        <strong>{{trans('common.email')}} :</strong> <ins>{{ $administrator->email }} </ins>
                                                                    </span>
                                                                </div>
                                                                <div class="col-md-6 text-left" style="margin-top: 10px">
                                                                    <span>
                                                                        <strong>{{trans('common.phone')}} :</strong> <ins>{{ $administrator->phone }}</ins>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-6 text-left" style="margin-top: 10px">
                                                                    <span>
                                                                        <strong>{{trans('common.name')}} :</strong> <ins>{{ $administrator->name }} </ins>
                                                                    </span>
                                                                </div>
                                                                <div class="col-md-6 text-left" style="margin-top: 10px">
                                                                    <span>
                                                                        <strong>{{trans('common.sex')}} :</strong>
                                                                        @if($administrator->sex)
                                                                            <ins>{{ trans('common.male') }}</ins>
                                                                        @else
                                                                            <ins>{{ trans('common.female') }}</ins>
                                                                        @endif

                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-6 text-left" style="margin-top: 10px">
                                                                    <span>
                                                                        <strong>{{trans('management.department')}} :</strong> <ins>{{ $administrator->belongsToPosition->belongsToDepartment->description }} </ins>
                                                                    </span>
                                                                </div>
                                                                <div class="col-md-6 text-left" style="margin-top: 10px">
                                                                    <span>
                                                                        <strong>{{trans('management.position')}} :</strong> <ins>{{ $administrator->belongsToPosition->description }}</ins>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-12 text-left" style="margin-top: 10px">
                                                                    <span>
                                                                        <strong>{{ trans('management.permissions') }} :</strong> <ins>{{--TODO Permissions--}}</ins>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                @if(is_null($administrator->deleted_at))
                                                    <form action="/admin/management/administrator/disabled/{{$administrator->id}}" method="post">
                                                        <input type="hidden" name="_token" value="{{ csrf_token()}}">
                                                        @if($administrator->id != \Auth::guard('web')->user()->id)
                                                            <button class="btn btn-sm bg-red" type="submit" >{{ trans('management.disabled') }}</button>
                                                        @endif
                                                        <a class="btn btn-sm btn-success" type="button" href="{{ asset('admin/management/administrator/view-more/'.$administrator->id) }}">{{ trans('management.view_more') }}</a>
                                                    </form>
                                                @else
                                                    <form action="/admin/management/administrator/enabled/{{$administrator->id}}" method="post">
                                                        <input type="hidden" name="_token" value="{{ csrf_token()}}">
                                                        @if($administrator->id != \Auth::guard('web')->user()->id)
                                                            <button class="btn btn-sm bg-red" type="submit" >{{ trans('management.enable') }}</button>
                                                        @endif
                                                        <a class="btn btn-sm btn-success" type="button" href="{{ asset('admin/management/administrator/view-more/'.$administrator->id) }}">{{ trans('management.view_more') }}</a>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- 模拟态 弹出框结束 -->
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @if(is_null($departmentID))
                {!! (new \App\Services\Presenter($administrators))->render() !!}<div class="tab-pane active">
                @elseif(!is_null($departmentID) && 0 == $departmentID)
                {!! (new \App\Services\Presenter($administrators->appends(['departmentID' => 0])))->render() !!}<div class="tab-pane active">
                @else
                {!! (new \App\Services\Presenter($administrators->appends(['departmentID' => $departmentID])))->render() !!}<div class="tab-pane active">
                @endif
                </div>
            </div>
        </div>
        <!-- END Dynamic Table Full -->
    </div>
    <!-- END Page Content -->
@endsection

@section('css')
    @parent

    <link rel="stylesheet" href="{{ asset('css/admin/manage_family.css') }}">
@endsection