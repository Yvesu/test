@extends('admin.layer')

@section('layer-content')
    <div id="alert-div" class="col-lg-2 col-md-4 col-xs-5 content-alert"></div>
    <!-- Page Header -->
    <div class="content bg-gray-lighter">
        <div class="row items-push">
            <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
                <ol class="breadcrumb push-10-t">
                    <li>{{ trans('common.management') }}</li>
                    <li>{{trans('common.family_management')}}</li>
                    <li><a class="link-effect" href="">{{trans('common.setting_family')}}</a></li>
                </ol>
            </div>
            <div class="col-sm-4 col-xs-9">
                <h1 class="page-heading" >
                    {{trans('common.setting_family')}}
                </h1>
            </div>
            <div class="col-sm-8 col-xs-3">
                <!-- Single button -->
                <div class="btn-group pull-right" id="family-action">
                    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        {{ trans('management.menu') }} <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu bg-yellow-lighter">
                        <li><a href="/admin/management/department" >{{ trans('management.add_dept') }}</a></li>
                        <li><a href="/admin/management/position" >{{ trans('management.add_post') }}</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- END Page Header -->

    <!-- Page Content -->
    <div class="content">
        <!-- Dynamic Table Full -->
        <div class="block">
            @if(1 == $deptType)
                @include('admin.management.family.setting.operating',[$departments,$deptID])
            @elseif(0 == $deptType)
                @include('admin.management.family.setting.disabled',$departments)
            @elseif(2 == $deptType)
                @include('admin.management.family.setting.review',[$departments,$deptID])
            @endif
        </div>
        <!-- END Dynamic Table Full -->
    </div>
    <!-- END Page Content -->
@endsection

@section('scripts')
    @parent
    <script>
        App.initHelper('table-tools');
    </script>
@endsection

@section('css')
    @parent

    <link rel="stylesheet" href="{{ asset('css/admin/manage_family.css') }}">
@endsection