@extends('admin.layer')
@section('layer-content')
        <!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.filter').trans('common.folder') }}</li>
                <li>
                    <a class="link-effect" href="{{ asset('/admin/make/filter/folder/index') }}">
                        {{ trans('common.filter').trans('common.folder').trans('common.management') }}
                    </a>
                </li>
                <li>{{ trans('common.edit') }}</li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            <h1 class="page-heading" >
                {{ trans('common.add').trans('common.filter').trans('common.folder') }}
            </h1>
        </div>
        <div class="col-sm-8 col-xs-3">
            <div class="btn-group pull-right" id="family-action">
                <a type="button" class="btn btn-primary" href="{{ asset('/admin/make/filter/folder/index') }}">
                    {{ trans('common.cancel') }}
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Page Content -->
<div class="content">
    <div class="row">
        <div class="col-md-12 col-xs-12 block">
            <form class="js-validation-topic form-horizontal" enctype="multipart/form-data"
                  action="{{ asset('/admin/make/filter/folder/update') }}" method="POST">

                <input type="hidden" name="_token" value="{{ csrf_token()}}">
                <input type="hidden" name="id" value="{{ $data->id }}">

                {{--名称--}}
                <div class="form-group" style="margin-top:5%">
                    <div class="col-md-8 col-md-offset-1">
                        <label for="name">{{ trans('common.name') }} <span class="text-danger">*</span></label>
                        <div class="form-material form-material-primary">
                            <input type="text" class="form-control" id="name" name="name" value="{{ $data->name }}">
                        </div>
                    </div>
                </div>
                
                {{--提交--}}
                <div class="form-group" style="margin-top: 30px;margin-bottom: 5%">
                    <div class="col-md-6 col-md-offset-3 col-xs-12 ">
                        <button type="submit" class="btn btn-block btn-primary">{{ trans('management.commit') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END Dynamic Table Full -->
    <img id="is-valid" style="visibility: hidden">
    <div class="hidden" id="topic-flag" data-value="0"></div>
</div>
@endsection

