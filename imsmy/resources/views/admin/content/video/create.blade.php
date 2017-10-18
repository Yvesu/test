@extends('admin/layer')
@section('layer-content')
<!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.content') }}</li>
                <li>{{ trans('content.official') }}</li>
                <li><a class="link-effect" href="{{ asset('admin/content/video') }}">{{trans('common.video')}}</a></li>
                <li><a class="link-effect" href="">{{trans('content.add_video')}}</a></li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            <h1 class="page-heading" >
                {{trans('content.add_video')}}
            </h1>
        </div>
        <div class="col-sm-8 col-xs-3">
            <!-- Single button -->
            <div class="btn-group pull-right" id="family-action">
                <a type="button" class="btn bg-red" href="{{ asset('/admin/content/video') }}">
                    {{ trans('common.cancel') }}
                </a>
            </div>
        </div>
    </div>
</div>
<!-- END Page Header -->
<div class="content">
    <div class="block">
        {{--<div class="col-sm-8 col-xs-3">
            <div class="btn-group pull-right" id="upload-file">
                <a type="button" id="pickfiles" class="btn bg-primary" href="#">
                    {{ trans('common.upload') }}
                </a>
            </div>
        </div>--}}
        <div class="row" style="margin-top: 5%;">
            <div class="col-md-10 col-xs-12 block col-md-offset-1">
                <form class="js-validation-topic form-horizontal"
                      enctype="multipart/form-data"
                      action="{{ asset('/admin/content/video') }}"
                      method="POST">
                    <input type="hidden" name="_token" value="{{ csrf_token()}}">
                    <input type="hidden" name="user_id" value="1">
                    <div class="form-group" style="margin-top:5%">
                        <div class="col-md-8 col-md-offset-2">
                            <label for="name">{{ trans('content.video_name') }} <span class="text-danger">*</span></label>
                            <div class="form-material form-material-primary">
                                <input type="text" class="form-control" id="name" name="name" >
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-8 col-md-offset-2">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="btn-group pull-right" id="upload-file">
                                        <a type="button" id="pickfiles" class="btn bg-primary pull-right" href="#">
                                            {{ trans('common.upload') . trans('common.video') }}
                                        </a>
                                        <input type="hidden" class="form-control" id="key" name="key" >
                                        <div class="help-block text-right animated fadeInDown hidden">
                                            <span class="text-danger">{{ trans('errors.format_invalid') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group hidden">
                        <div class="col-md-8 col-md-offset-2">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="progress active">
                                        <div class="upload-file-bar progress-bar progress-bar-success progress-bar-striped"
                                             role="progressbar" aria-valuenow="50"
                                             aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>
                                    </div>
                                    <div class="upload-file-text"></div>
                                </div>
                                <div class="col-md-3 pull-right upload-cancel">
                                    <div class="btn-group pull-right" id="cancel-upload">
                                        <a type="button" class="btn btn-xs bg-red" href="#">
                                            {{ trans('common.cancel') . trans('common.upload') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-4 col-md-offset-4 col-xs-12" style="margin-top: 20px">
                            <div class="img-container fx-opt-zoom-in">
                                <img class="img-responsive img-thumbnail center-block" id="icon" src="{{ asset('/admin/images/default/default_icon.png') }}" alt="" style="width:150px;">
                                <div class="img-options center-block" style="width:150px">
                                    <div class="img-options-content">
                                        <a href="#" class="btn button-change-profile-picture">
                                            <label for="upload-profile-picture">
                                                <span class="btn btn-primary" id="upload-avatar" style="cursor: pointer">{{ trans('common.upload') }}</span>
                                                <input class="btn-upload" name="video-icon" id="video-icon" type="file" title style="margin-top:-32px;" onchange="PreviewImage.video_preview(this,'icon')">
                                            </label>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <span class="text-center col-xs-12" style="margin-top: 10px">宽:高 1:1/16:9</span>
                            <span class="text-center animated fadeInDown col-xs-12 has-error text-danger hidden format-invalid icon" >{{ trans('errors.format_invalid') }}</span>
                            <span class="text-center animated fadeInDown col-xs-12 has-error text-danger hidden size-invalid icon">{{ trans('errors.size_invalid') }}</span>
                        </div>
                    </div>
                    <div class="form-group" style="margin-top: 30px;margin-bottom: 5%">
                        <div class="col-md-6 col-md-offset-3 col-xs-12 ">
                            <button type="submit" class="btn btn-block btn-primary" id="submit">{{ trans('management.commit') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<img id="is-valid" style="visibility: hidden">
<div class="hidden" id="video-flag" data-value="0"></div>
@endsection

@section('scripts')
    @parent
    <script src="{{ asset('js/core/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('js/admin/video.js') }}"></script>
    <script src="{{ asset('js/plugins/qiniu/moxie.js') }}"></script>
    <script src="{{ asset('js/plugins/qiniu/plupload.dev.js') }}"></script>
    <script src="{{ asset('js/plugins/qiniu/qiniu.js') }}"></script>
    <script src="{{ asset('js/plugins/qiniu/zh_CN.js') }}"></script>
    <script>
        Lang.name_required = "{{ trans('message.requiredMsg',['attribute' => trans('content.video_name')]) }}";
        Lang.video_required = "{{ trans('message.uploadMsg',['attribute' => trans('common.video')]) }}";
        Lang.domain    = "{{ CloudStorage::getDomain() }}";
        Lang.uploaded = "{{ trans('common.uploaded') }}";
        Lang.speed = "{{ trans('common.speed') }}";
        Lang.completed = "{{ trans('common.completed') }}";
        Lang.click_view = "{{ trans('content.click_view') }}";
        Lang.file_size = "{{ trans('content.file_size') }}";
        Lang.screen_shot_required    = "{{ trans('message.uploadMsg',['attribute' => trans('common.screen_shot')]) }}";
        jQuery(function(){ Video.init(); });
    </script>
    <script src="{{ asset('js/admin/add_video.js') }}"></script>
    <script src="{{ asset('js/plugins/qiniu/ui.js') }}"></script>
@endsection

@section('css')
    @parent

    <link rel="stylesheet" href="{{ asset('css/admin/blur.css') }}">
    <style>
        #video-icon-error{
            text-align: center;
        }
        .icon{
            font-style: italic;
            font-size: 13px;
        }
    </style>
@endsection