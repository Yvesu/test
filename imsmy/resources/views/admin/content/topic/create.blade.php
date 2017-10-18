@extends('admin.layer')
@section('layer-content')
        <!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.content') }}</li>
                <li><a class="link-effect" href="{{ asset('admin/content/topic') }}">{{trans('common.topic_content')}}</a></li>
                <li><a class="link-effect" href="">{{trans('content.add_topic')}}</a></li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            <h1 class="page-heading" >
                {{trans('content.add_topic')}}
            </h1>
        </div>
        <div class="col-sm-8 col-xs-3">
            <!-- Single button -->
            <div class="btn-group pull-right" id="family-action">
                <a type="button" class="btn bg-red" href="{{ asset('/admin/content/topic') }}">
                    {{ trans('common.cancel') }}
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Page Content -->
<div class="content">
    <div class="row" style="margin-top: 5%;">
        <div class="col-md-10 col-xs-12 block col-md-offset-1">
            <form class="js-validation-topic form-horizontal"
                  enctype="multipart/form-data"
                  action="{{ asset('/admin/content/topic') }}"
                  method="POST">
                <input type="hidden" name="_token" value="{{ csrf_token()}}">
                <div class="form-group" style="margin-top:5%">
                    <div class="col-md-8 col-md-offset-2">
                        <label for="name">{{ trans('content.topic_name') }} <span class="text-danger">*</span></label>
                        <div class="form-material form-material-primary">
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}">
                        </div>
                        @if(Session::has('name'))
                            <div class="help-block text-right animated fadeInDown"><span class="text-danger">{{Session::get('name')}}</span></div>
                        @endif
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-8 col-md-offset-2">
                        <label for="name">{{ trans('content.tweet_number') }} <span class="text-danger">*</span></label>
                        <div class="form-material form-material-primary">
                            <textarea class="form-control" name="tweet_number" placeholder="{{ trans('content.comma') }}" rows="1"></textarea>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-8 col-md-offset-2">
                        <div class="form-material">
                            <textarea class="form-control" id="comment" name="comment" rows="2">{{ old('comment') }}</textarea>
                            <label for="comment">{{ trans('content.topic_comment') }}</label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-8 col-md-offset-2">

                        <div class="form-material" >

                            <div class="btn-group " data-toggle="buttons" style="margin-top: 20px" id="color-choose">
                                <label class="btn " title="#3ABD9B" style="background: #3ABD9B;" >
                                    <input type="radio" name="options" id="option1"  autocomplete="off" checked="checked" value="#3ABD9B"> <span style="color:white">颜色1</span>
                                </label>
                                <label class="btn " title="#46CE6B" style="background: #46CE6B;">
                                    <input type="radio" name="options" id="option2" autocomplete="off" value="#46CE6B"> <span>颜色2</span>
                                </label>
                                <label class="btn " title="#4497DF" style="background: #4497DF;">
                                    <input type="radio" name="options" id="option3" autocomplete="off" value="#4497DF"> <span>颜色3</span>
                                </label>
                                <label class="btn " title="#9855B9" style="background: #9855B9;">
                                    <input type="radio" name="options" id="option4" autocomplete="off" value="#9855B9"> <span>颜色4</span>
                                </label>
                                <label class="btn " title="#36495F" style="background: #36495F;">
                                    <input type="radio" name="options" id="option5" autocomplete="off" value="#36495F"> <span>颜色5</span>
                                </label>
                                <label class="btn " title="#EDC500" style="background: #EDC500;">
                                    <input type="radio" name="options" id="option6" autocomplete="off" value="#EDC500"> <span>颜色6</span>
                                </label>
                                <label class="btn " title="#E04A34" style="background: #E04A34;">
                                    <input type="radio" name="options" id="option7" autocomplete="off" value="#E04A34"> <span>颜色7</span>
                                </label>
                                <label class="btn " title="#E07E00" style="background: #E07E00;">
                                    <input type="radio" name="options" id="option8" autocomplete="off" value="#E07E00"> <span>颜色8</span>
                                </label>
                            </div>
                            {{--<div class="demo" style="width: 50px;height: 50px;border-radius: 25px;background: #3ABD9B;">--}}
                            {{--<input type="radio" name="inlineRadioOptions" id="inlineRadio1" value="option1">--}}
                            {{--</div>--}}


                            <label for="comment">主题色选择</label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-8 col-md-offset-2" style="padding-top: 20px">
                        <strong>{{ trans('common.icon') }}</strong>
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
                                            <input class="btn-upload" name="topic-icon" id="topic-icon" type="file" title style="margin-top:-32px;" onchange="PreviewImage.topic_preview(this,'icon')">
                                        </label>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <span class="text-center col-xs-12" style="margin-top: 10px">150 x 150</span>
                        <span class="text-center animated fadeInDown col-xs-12 has-error text-danger hidden format-invalid icon" >{{ trans('errors.format_invalid') }}</span>
                        <span class="text-center animated fadeInDown col-xs-12 has-error text-danger hidden size-invalid icon">{{ trans('errors.size_invalid') }}</span>
                    </div>
                </div>
                {{--图片封面--}}
                <div class="form-group">
                    <div class="col-md-8 col-md-offset-2" style="padding-top: 20px">
                        <strong>{{ trans('common.photo_cover') }}</strong>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-4 col-md-offset-4 col-xs-12" style="margin-top: 20px">
                        <div class="img-container fx-opt-zoom-in">
                            <img class="img-responsive img-thumbnail center-block" id="icones" src="{{ asset('/admin/images/default/default_icon.png') }}" alt="" style="width:150px;">
                            <div class="img-options center-block" style="width:150px">
                                <div class="img-options-content">
                                    <a href="#" class="btn button-change-profile-picture">
                                        <label for="upload-profile-picture">
                                            <span class="btn btn-primary" id="upload-avatar" style="cursor: pointer">{{ trans('common.upload') }}</span>
                                            <input class="btn-upload" name="cover-icon" id="video-icones" type="file" title style="margin-top:-32px;" onchange="PreviewImage.video_preview(this,'icones')">
                                        </label>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <span class="text-center col-xs-12" style="margin-top: 10px">建议宽:高 16:9</span>
                        <span class="text-center animated fadeInDown col-xs-12 has-error text-danger hidden format-invalid icon" >{{ trans('errors.format_invalid') }}</span>
                        <span class="text-center animated fadeInDown col-xs-12 has-error text-danger hidden size-invalid icon">{{ trans('errors.size_invalid') }}</span>
                    </div>
                </div>

                {{--视频简介--}}
                <div class="form-group">
                    <div class="col-md-8 col-md-offset-2" style="padding-top: 20px">
                        <strong>{{ trans('common.video_intro') }}</strong>
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
                            <img class="img-responsive img-thumbnail center-block" id="icons" src="{{ asset('/admin/images/default/default_icon.png') }}" alt="" style="width:150px;">
                            <div class="img-options center-block" style="width:150px">
                                <div class="img-options-content">
                                    <a href="#" class="btn button-change-profile-picture">
                                        <label for="upload-profile-picture">
                                            <span class="btn btn-primary" id="upload-avatar" style="cursor: pointer">{{ trans('common.upload') }}</span>
                                            <input class="btn-upload" name="video-icon" id="video-icons" type="file" title style="margin-top:-32px;" onchange="PreviewImage.video_preview(this,'icons')">
                                        </label>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <span class="text-center col-xs-12" style="margin-top: 10px">建议宽:高 1:1/16:9</span>
                        <span class="text-center animated fadeInDown col-xs-12 has-error text-danger hidden format-invalid icon" >{{ trans('errors.format_invalid') }}</span>
                        <span class="text-center animated fadeInDown col-xs-12 has-error text-danger hidden size-invalid icon">{{ trans('errors.size_invalid') }}</span>
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
    <div class="hidden" id="topic-flag" data-value="0"></div>
</div>
@endsection

@section('scripts')
    @parent

    <script src="{{ asset('js/core/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('js/admin/topic.js') }}"></script>

    <script src="{{ asset('js/admin/video.js') }}"></script>
    <script src="{{ asset('js/plugins/qiniu/moxie.js') }}"></script>
    <script src="{{ asset('js/plugins/qiniu/plupload.dev.js') }}"></script>
    <script src="{{ asset('js/plugins/qiniu/qiniu.js') }}"></script>
    <script src="{{ asset('js/plugins/qiniu/zh_CN.js') }}"></script>

    <script>
        Lang.name_required = "{{ trans('message.requiredMsg',['attribute' => trans('content.topic_name')]) }}";
        Lang.text          = "{{ trans('errors.number_english_chinese') }}";
        $("input[name='options']").parent().click(function(){
            var color = $(this).children("span").attr('style','color:white');
            color.parent().siblings().children("span").removeAttr('style');
        });
        jQuery(function(){ Topic.init(); });

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
        #channel-icon-error{
            text-align: center;
        }
        .icon{
            font-style: italic;
            font-size: 13px;
        }
    </style>
@endsection