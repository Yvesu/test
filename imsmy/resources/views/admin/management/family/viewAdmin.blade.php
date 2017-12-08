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
                    {{ trans('management.member') }} - {{ trans('common.detail_info') }}
                </h1>
            </div>
            <div class="col-sm-8 col-xs-3">
                <!-- Single button -->
                <div class="btn-group pull-right" id="family-action">
                    <button type="button" class="btn btn-primary" onclick="javascript:history.back(-1);">
                        {{ trans('common.go_back') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- END Page Header -->

    <!-- Page Content -->
    <div class="content">
        <div class="block" style="padding: 30px 0">
            <div class="row">
                <div class="col-md-3 col-md-offset-1 text-center">
                    <img src="{{ asset('/admin/images/'.$administrator->avatar) }}" style="max-height:128px;max-width:128px">
                </div>
                <div class="col-md-7  text-left margin-10">
                    <div class="row">
                        <div class="col-md-6">
                            <span>
                                {{trans('common.email')}} : <ins>{{ $administrator->email }} </ins>
                            </span>
                        </div>
                        <div class="col-md-6">
                            <span>
                                {{trans('common.phone')}} : <ins>{{ $administrator->phone }}</ins>
                            </span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <span>
                                {{trans('common.name')}} : <ins>{{ $administrator->name }} </ins>
                            </span>
                        </div>
                        <div class="col-md-6">
                            <span>
                                {{trans('common.sex')}} :
                                @if($administrator->sex)
                                    <ins>{{ trans('common.male') }}</ins>
                                @else
                                    <ins>{{ trans('common.female') }}</ins>
                                @endif

                            </span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <span>
                                {{trans('management.department')}} : <ins>{{ $administrator->belongsToPosition->belongsToDepartment->description }} </ins>
                            </span>
                        </div>
                        <div class="col-md-6">
                            <span>
                                {{trans('management.position')}} : <ins>{{ $administrator->belongsToPosition->description }}</ins>
                            </span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <span>
                                {{ trans('management.permissions') }} : <ins>{{--TODO Permissions--}}</ins>
                            </span>
                        </div>
                        <div class="col-md-6">
                            <span>
                                {{ trans('management.APPUserID') }} : <ins>{{ $administrator->user_id === null ? trans('common.null') : $administrator->user_id }}</ins>
                            </span>
                        </div>
                    </div>
                    <div class="row border-t" style="margin-top: 10px;">
                        <div class="col-md-12">
                            <h5>
                                {{ trans('management.secondary_contact') }}:
                            </h5>
                        </div>
                        <div class="col-md-6">
                            <span>
                                {{trans('common.name')}} : <ins>{{ $administrator->secondary_contact_name }} </ins>
                            </span>
                        </div>
                        <div class="col-md-6">
                            <span>
                                {{trans('common.phone')}} : <ins>{{ $administrator->secondary_contact_phone }} </ins>
                            </span>
                        </div>
                        <div class="col-md-6">
                            <span>
                                {{trans('common.relationship')}} : <ins>{{ trans('management.'.$administrator->secondary_contact_relationship) }} </ins>
                            </span>
                        </div>
                    </div>
                    <div class="row border-t js-gallery" style="margin-top: 10px;">
                        <div class="col-md-12">
                            <h5>
                                {{ trans('management.IDCard') }}:
                            </h5>
                        </div>
                        <div class="col-md-4" style="max-width: 200px;max-height: 200px">
                            <div class="img-container fx-opt-zoom-in">
                                <img class="img-responsive img-thumbnail" src="{{ asset('/admin/images/'.$administrator->ID_card_URL) }}" alt="" >
                                <div class="img-options">
                                    <div class="img-options-content">
                                        <a class="btn btn-sm btn-default img-link" href="{{ asset('/admin/images/'.$administrator->ID_card_URL) }}">
                                            <i class="fa fa-search-plus"></i> View
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 col-xs-11 remove-padding-r">
                            <a class="btn btn-primary pull-right" href="{{ asset('admin/management/administrator/edit/'.$administrator->id) }}">
                                {{ trans('common.edit') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END Page Content -->
@endsection

@section('scripts')
    @parent
    <script src="{{ asset('js/plugins/magnific-popup/magnific-popup.min.js') }}"></script>
    <script>
        $(function(){
            App.initHelpers('magnific-popup');
        });
    </script>
@endsection

@section('css')
    @parent

    <link rel="stylesheet" href="{{ asset('css/admin/manage_family.css') }}">
    <link rel="stylesheet" href="{{ asset('js/plugins/magnific-popup/magnific-popup.min.css') }}">
@endsection