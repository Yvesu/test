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
                    <li><a class="link-effect" href="/admin/management/family/setting">{{trans('common.setting_family')}}</a></li>
                </ol>
            </div>
            <div class="col-sm-4 col-xs-9">
                <h1 class="page-heading" >
                    {{ trans('common.edit') }}{{ trans('management.position') }}
                </h1>
            </div>
            <div class="col-sm-8 col-xs-3">
                <!-- Single button -->
                <div class="btn-group pull-right" id="family-action">
                    <a type="button" class="btn bg-red" href="/admin/management/family/setting">
                        {{ trans('common.cancel') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- END Page Header -->

    <!-- Page Content -->
    <div class="content">
        <div class="row" style="margin-top: 5%;">
            <div class="col-md-10 col-xs-12 block col-md-offset-1">
                <form class="js-validation-position form-horizontal" action="/admin/management/position/edit/{{ $position->id }}" method="POST">
                    <input type="hidden" name="_token" value="{{ csrf_token()}}">
                    <div class="form-group" style="margin-top: 5%">
                        <div class="col-sm-8 col-sm-offset-2">
                            <label for="department">{{ trans('management.department') }} <span class="text-danger">*</span></label>
                            <div class="form-material form-material-primary">
                                <input type="text" class="form-control" id="deptDescription" name="deptDescription" placeholder="{{ trans('management.deptDescription') }}" value="{{ $position->belongsToDepartment->description }}" disabled>
                            </div>
                        </div>
                    </div>
                    <div class="form-group" >
                        <div class="col-sm-4 col-sm-offset-2">
                            <label for="name">{{ trans('management.postName') }} <span class="text-danger">*</span></label>
                            <div class="form-material form-material-primary">
                                <input type="text" class="form-control" id="name" name="name" placeholder="{{ trans('management.postName') }}" value="{{ old('name') ? old('name') : $position->name }}">
                                @if(Session::has('postName'))
                                    <div class="help-block text-right animated fadeInDown"><span class="text-danger">{{Session::get('postName')}}</span></div>
                                @endif
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <label for="description">{{ trans('management.postDescription') }} <span class="text-danger">*</span></label>
                            <div class="form-material form-material-primary">
                                <input type="text" class="form-control" id="description" name="description" placeholder="{{ trans('management.postDescription') }}" value="{{ old('description') ? old('description') : $position->description }}">
                                @if(Session::has('postDescription'))
                                    <div class="help-block text-right animated fadeInDown "><span class="text-danger">{{Session::get('postDescription')}}</span></div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="form-group" style="margin-top: 30px">
                        <div class="col-md-6 col-md-offset-3 col-xs-12">
                            <button type="submit" class="btn btn-block btn-primary">{{ trans('management.commit') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- END Dynamic Table Full -->
    </div>
    <!-- END Page Content -->
@endsection



@section('scripts')
    @parent
    <!-- Page JS Plugins -->
    <script src="{{ asset('js/core/jquery.validate.min.js') }}"></script>
    <script>
        var AddDepartment = function() {
            // Init Login Form Validation, for more examples you can check out https://github.com/jzaefferer/jquery-validation
            var initValidationDept = function(){
                jQuery('.js-validation-position').validate({
                    errorClass: 'help-block text-right animated fadeInDown',
                    errorElement: 'div',
                    errorPlacement: function(error, e) {
                        jQuery(e).parents('.form-group > div').append(error);
                    },
                    highlight: function(e) {
                        jQuery(e).closest('.form-group > div').removeClass('has-error').addClass('has-error');
                        jQuery(e).closest('.help-block').remove();
                    },
                    success: function(e) {
                        jQuery(e).closest('.form-group > div').removeClass('has-error');
                        jQuery(e).closest('.help-block').remove();
                    },
                    rules: {
                        'name': {
                            required: true
                        },
                        'description': {
                            required: true
                        },
                        'department': {
                            required: true
                        }
                    },
                    messages: {
                        'name': {
                            required: "{{ trans('errors.plzInput').trans('management.deptName')}}"
                        },
                        'description': {
                            required: "{{ trans('errors.plzInput').trans('management.deptDescription')}}"
                        },
                        'department': {
                            required: "{{ trans('errors.plzSelect').trans('management.department')}}"
                        }
                    }
                });
            };

            return {
                init: function () {
                    // Init Login Form Validation
                    initValidationDept();
                }
            };
        }();

        // Initialize when page loads
        jQuery(function(){ AddDepartment.init(); });
    </script>
@endsection

@section('css')
    @parent

    <link rel="stylesheet" href="{{ asset('css/admin/manage_family.css') }}">
@endsection