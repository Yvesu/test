@extends('admin/layer')
@section('layer-content')
        <!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.system_management') }}</li>
                <li><a class="link-effect" href="{{ asset('admin/system_management/trophy') }}">{{trans('common.trophy_content')}}</a></li>
                <li><a class="link-effect" href="">{{trans('content.add_trophy')}}</a></li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            <h1 class="page-heading" >
                {{trans('content.add_trophy')}}
            </h1>
        </div>
        <div class="col-sm-8 col-xs-3">
            <!-- Single button -->
            <div class="btn-group pull-right" id="family-action">
                <a type="button" class="btn bg-red" href="{{ asset('/admin/system_management/trophy') }}">
                    {{ trans('common.cancel') }}
                </a>
            </div>
        </div>
    </div>
</div>
<!-- END Page Header -->
<div class="content">
    <div class="block">
        <div class="row" style="margin-top: 5%;">
            <div class="col-md-10 col-xs-12 block col-md-offset-1">
                <form class="js-validation-topic form-horizontal"
                      enctype="multipart/form-data"
                      action="{{ asset('/admin/system_management/trophy') }}"
                      method="POST">
                    <input type="hidden" name="_token" value="{{ csrf_token()}}">

                    <div class="form-group" style="margin-top:5%">
                        <div class="col-md-8 col-md-offset-2">
                            <label for="exampleInputEmail1">{{ trans('content.trophy_name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="trophy_name" class="form-control">
                        </div>
                    </div>

                    <div class="form-group" style="margin-top:5%">
                        <div class="col-md-8 col-md-offset-2">
                            <label for="exampleInputEmail1">{{ trans('content.gold_num') }} <span class="text-danger">*</span></label>
                            <input type="text" name="gold_num" class="form-control">
                        </div>
                    </div>

                    {{--起始时间--}}
                    <div class="form-group" style="margin-top:5%">
                        <div class="col-md-8 col-md-offset-2" style="padding-top: 20px">
                            <strong>{{ trans('content.from_time') . ' : ' }}</strong>

                        </div>
                    </div>
                    <div class="form-group" id="top">
                        <div class="col-md-4 col-md-offset-2">
                            <label class="col-md-6 control-label" for="top_date">{{ trans('content.date') }}</label>
                            <div class="col-md-6 remove-padding">
                                <input type="text"
                                       class="datepicker js-datepicker form-control"
                                       name="from_date" id="top_date" data-date-format="yyyy-mm-dd"
                                       value="" >
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="col-md-5 control-label" for="top_time">{{ trans('content.time') }}</label>
                            <div class="col-md-7 remove-padding">
                                <input type="time" class="form-control" name="from_time" id="top_time" value="">
                            </div>
                        </div>
                    </div>

                    {{--终止时间--}}
                    <div class="form-group" style="margin-top:5%">
                        <div class="col-md-8 col-md-offset-2" style="padding-top: 20px">
                            <strong>{{ trans('content.end_time') . ' : ' }}</strong>

                        </div>
                    </div>
                    <div class="form-group" id="top">
                        <div class="col-md-4 col-md-offset-2">
                            <label class="col-md-6 control-label" for="top_date">{{ trans('content.date') }}</label>
                            <div class="col-md-6 remove-padding">
                                <input type="text"
                                       class="datepicker js-datepicker form-control"
                                       name="end_date" id="recommend_date" data-date-format="yyyy-mm-dd"
                                       value="" >
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="col-md-5 control-label" for="top_time">{{ trans('content.time') }}</label>
                            <div class="col-md-7 remove-padding">
                                <input type="time" class="form-control" name="end_time" id="recommend_time" value="">
                            </div>
                        </div>
                    </div>


                    {{--图片添加--}}
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

    <script src="{{ asset('js/admin/channel.js') }}"></script>
    <script src="https://cdn.bootcss.com/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdn.bootcss.com/bootstrap-datepicker/1.6.1/js/bootstrap-datepicker.min.js"></script>
    <script src="{{ asset('js/admin/edit-tweet.js') }}"></script>
    <script src="https://cdn.bootcss.com/select2/4.0.3/js/i18n/zh-CN.js"></script>

    <script src="{{ asset('js/core/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('js/admin/topic.js') }}"></script>
    <script>


        Lang.name_required = "{{ trans('message.requiredMsg',['attribute' => trans('content.topic_name')]) }}";
        Lang.text          = "{{ trans('errors.number_english_chinese') }}";
        $("input[name='options']").parent().click(function(){
            var color = $(this).children("span").attr('style','color:white');
            color.parent().siblings().children("span").removeAttr('style');
        });
        jQuery(function(){ Topic.init(); });
    </script>
@endsection

@section('css')
    @parent

    <link href="https://cdn.bootcss.com/select2/4.0.3/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.bootcss.com/bootstrap-datepicker/1.6.1/css/bootstrap-datepicker.min.css" rel="stylesheet">

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