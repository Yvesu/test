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
                {{ trans('app.edit_class')}}
            </h1>
        </div>
        <div class="col-sm-8 col-xs-3">
            <!-- Single button -->
            <div class="btn-group pull-right" id="family-action">
                <a type="button" class="btn bg-red" href="{{ asset('/admin/app/camera/blur') }}">
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
            <div class="hidden" id="edit-blur-class-page"></div>
            <form class="js-validation-blur-class form-horizontal" enctype="multipart/form-data" action="{{ asset('/admin/app/camera/blur/class/edit/' . $class->id) }}" method="POST">
                <input type="hidden" name="_token" value="{{ csrf_token()}}">
                <div class="form-group" style="margin-top:5%">
                    <div class="col-md-4 col-md-offset-2">
                        <label for="name-zh">{{ trans('app.blur_class_name') }} <span class="text-danger">*</span><small> {{trans('multi-lang.zh')}}</small></label>
                        <div class="form-material form-material-primary">
                            <input type="text" class="form-control" id="name-zh" name="name-zh" placeholder="{{ trans('app.blur_class_name_placeholder_zh') }}" value="{{ old('name_zh') ? old('name_zh') : $class->name_zh }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="name-en">{{ trans('app.blur_class_name') }} <span class="text-danger">*</span><small> {{trans('multi-lang.en')}}</small></label>
                        <div class="form-material form-material-primary">
                            <input type="text" class="form-control" id="name-en" name="name-en" placeholder="{{ trans('app.blur_class_name_placeholder_en') }}" value="{{ old('name_en') ? old('name_en') : $class->name_en }}">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-8 col-md-offset-2">
                        <strong>{{ trans('common.icon') }}</strong>
                        <a class="btn bg-red pull-right btn-xs" data-target="#clear-icon-modal" data-toggle="modal">{{ trans('app.clear_icon') }}</a>
                        <input class="hidden" name="is_clear" id="is_clear" value="0">
                        <!-- 模拟态 弹出框开始 -->
                        <div class="modal fade" id="clear-icon-modal" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;">
                            <div class="modal-dialog modal-dialog-popin">
                                <div class="modal-content">
                                    <div class="block block-themed block-transparent remove-margin-b">
                                        <div class="block-header bg-primary-dark">
                                            <ul class="block-options">
                                                <li>
                                                    <button data-dismiss="modal" type="button"><i class="fa fa-times-circle"></i></button>
                                                </li>
                                            </ul>
                                            <h3 class="block-title">{{trans('app.clear_icon') }}</h3>
                                        </div>
                                        <div class="block-content">
                                            <h3 class="text-center" style="margin-bottom: 20px;"> {{ trans('message.clearIcon') }}</h3>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <a class="btn btn-sm btn-default" type="button" data-dismiss="modal">{{ trans('common.cancel') }}</a>
                                        <a class="btn btn-sm btn-primary" type="button" id="clear-icon"><i class="fa fa-check"></i> {{ trans('common.sure') }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- 模拟态 弹出框结束 -->
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-4 col-md-offset-2 col-xs-12" style="margin-top: 20px">
                        <div class="img-container fx-opt-zoom-in">
                            @if(is_null($class->icon_sm))
                            <img class="img-responsive img-thumbnail center-block" id="icon-sm" src="{{ asset('/admin/images/default/default_icon.png') }}" alt="" style="width:96px;">
                            @else
                                <img class="img-responsive img-thumbnail center-block" id="icon-sm"
                                     src="{{ downloadUrl('blur_class/'. $class->id . '/' . $class->icon_sm) }}"
                                     alt="" style="width:96px;">
                            @endif
                            <div class="img-options center-block" style="width:96px">
                                <div class="img-options-content">
                                    <a href="#" class="btn button-change-profile-picture">
                                        <label for="upload-profile-picture">
                                            <span class="btn btn-primary" id="upload-avatar" style="cursor: pointer">{{ trans('common.upload') }}</span>
                                            <input class="btn-upload" name="blur-class-icon-sm" id="blur-class-icon-sm" type="file" title style="margin-top:-32px;" onchange="PreviewImage.blur_preview(this,'icon-sm')">
                                        </label>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <span class="text-center col-xs-12" style="margin-top: 10px">96 x 96</span>
                        <span class="text-center animated fadeInDown col-xs-12 has-error text-danger hidden format-invalid icon-sm" >{{ trans('errors.format_invalid') }}</span>
                        <span class="text-center animated fadeInDown col-xs-12 has-error text-danger hidden proportion-invalid icon-sm">{{ trans('errors.proportion_invalid') }}</span>
                    </div>
                    <div class="col-md-4 col-xs-12" style="margin-top: 20px">
                        <div class="img-container fx-opt-zoom-in">
                            @if(is_null($class->icon_lg))
                            <img class="img-responsive img-thumbnail center-block" id="icon-lg" src="{{ asset('/admin/images/default/default_icon.png') }}" alt="" style="width:200px;">
                            @else
                                <img class="img-responsive img-thumbnail center-block" id="icon-lg"
                                     src="{{ downloadUrl('blur_class/'. $class->id . '/' . $class->icon_lg) }}"
                                     alt="" style="width:200px;">
                            @endif
                            <div class="img-options center-block" style="width:200px">
                                <div class="img-options-content">
                                    <a href="#" class="btn button-change-profile-picture">
                                        <label for="upload-profile-picture">
                                            <span class="btn btn-primary" id="upload-avatar" style="cursor: pointer">{{ trans('common.upload') }}</span>
                                            <input class="btn-upload" name="blur-class-icon-lg" id="blur-class-icon-lg" type="file" title style="margin-top:-32px;" onchange="PreviewImage.blur_preview(this,'icon-lg')">
                                        </label>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <span class="text-center col-xs-12" style="margin-top: 10px">200 x 200</span>
                        <span class="text-center animated fadeInDown col-xs-12 has-error text-danger hidden format-invalid icon-lg" >{{ trans('errors.format_invalid') }}</span>
                        <span class="text-center animated fadeInDown col-xs-12 has-error text-danger hidden proportion-invalid icon-lg">{{ trans('errors.proportion_invalid') }}</span>
                    </div>
                </div>
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
</div>
<!-- END Page Content -->
@endsection



@section('scripts')
    @parent
            <!-- Page JS Plugins -->
    <script src="{{ asset('js/core/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('js/admin/blur-class.js') }}"></script>
    <script>
        Lang.name_zh_required = "{{ trans('message.requiredMsg',['attribute' => trans('app.blur_class_name').'('.trans('multi-lang.zh').')']) }}";
        Lang.name_en_required = "{{ trans('message.requiredMsg',['attribute' => trans('app.blur_class_name').'('.trans('multi-lang.en').')']) }}";
        Lang.icon_required    = "{{ trans('message.uploadMsg',['attribute' => trans('common.icon')]) }}"
        //清空上传的图片
        $("#clear-icon").click(function(){
            $('#clear-icon-modal').modal('hide');
            $("#icon-sm").attr('src','/admin/images/default/default_icon.png');
            $("#icon-lg").attr('src','/admin/images/default/default_icon.png');
            $("#blur-class-icon-sm").val(null);
            $("#blur-class-icon-lg").val(null);
            $("#is_clear").val(1);
        });
        jQuery(function(){ BlurClass.init(); });
    </script>
@endsection

@section('css')
    @parent

    <link rel="stylesheet" href="{{ asset('css/admin/blur.css') }}">

    <style>
        #blur-class-icon-sm-error , #blur-class-icon-lg-error{
            text-align: center;
        }
    </style>
@endsection