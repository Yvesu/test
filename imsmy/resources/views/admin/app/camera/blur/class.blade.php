@extends('admin.layer')
@section('layer-content')
        <!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.camera') }}</li>
                <li><a class="link-effect" href="">{{trans('common.blur')}}</a></li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            <h1 class="page-heading" >
                {{ trans('app.blur_class')}}
            </h1>
        </div>
        <div class="col-sm-8 col-xs-3">
            <!-- Single button -->
            <div class="btn-group pull-right" id="family-action">
                <a type="button" class="btn btn-primary" href="/admin/app/camera/blur">
                    {{ trans('common.go_back') }}
                </a>
            </div>
        </div>
    </div>
</div>
<!-- END Page Header -->

<!-- Page Content -->
<div class="content">
    <div class="block">
        @if(is_null($type) || 0 != $type)
            @include('admin.app.camera.blur.blurManagement.normal',[$classes])
        @else
            @include('admin.app.camera.blur.blurManagement.disabled',[$classes])
        @endif
    </div>
</div>
<!-- END Page Content -->
@endsection

@section('scripts')
@parent
@endsection

@section('css')
    @parent

    <link rel="stylesheet" href="{{ asset('css/admin/blur.css') }}">
@endsection