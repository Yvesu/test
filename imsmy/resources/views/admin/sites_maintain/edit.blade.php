@extends('admin/layer')
@section('layer-content')
        <!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li><a class="link-effect" href="{{ asset('/admin/maintain/index') }}">{{ trans('content.sites-maintain') }}</a></li>
                <li>{{ trans('content.edit') }}</li>
                <li>{{ $data -> id }}</li>
            </ol>
        </div>
        <div class="col-sm-12">
            <div class="btn-group pull-right" id="family-action">
                <a type="button" class="btn bg-red" href="{{ asset('/admin/maintain/index') }}" >
                    {{ trans('common.cancel') }}
                </a>
            </div>
        </div>
    </div>
</div>
<!-- END Page Header -->
<div class="content">
    <div class="block">
        <div class="block-content tab-content">
            <div class="col-md-10 col-xs-12 block">
                @if (count($errors) > 0)
                    <div class="mws-form-message error">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form class="js-validation-topic form-horizontal"
                      enctype="multipart/form-data"
                      action="{{ asset('/admin/maintain/update') }}"
                      method="POST">
                    <input type="hidden" name="id" value="{{ $data->id }}">
                    <input type="hidden" name="_timezone" value="Asia/Shanghai">
                    <input type="hidden" name="_token" value="{{ csrf_token()}}">

                    {{--网站标题--}}
                    <div class="form-group">
                        <div class="col-md-8 col-md-offset-2">
                            <label for="exampleInputEmail1">{{ trans('common.title') }} <span class="text-danger">*</span></label>
                            <div class="form-material form-material-primary">
                                <input type="text" class="form-control" id="name" name="title" value="{{ $data->title }}" placeholder="{{ trans('common.title') }}">
                            </div>
                        </div>
                    </div>

                    {{--网站关键词--}}
                    <div class="form-group">
                        <div class="col-md-8 col-md-offset-2">
                            <label for="exampleInputEmail1">{{ trans('common.keywords') }} <span class="text-danger">*</span></label>
                            <div class="form-material form-material-primary">
                                <input type="text" class="form-control" id="intro" name="keywords" value="{{ $data->keywords }}" placeholder="{{ trans('common.keywords') }}">
                            </div>
                        </div>
                    </div>

                    {{--网站logo--}}
                    <div class="form-group">
                        <div class="col-md-8 col-md-offset-2" style="padding-top: 20px">
                            <strong>{{ trans('common.icon') }}</strong>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-4 col-md-offset-4 col-xs-12" style="margin-top: 20px">
                            <div class="img-container fx-opt-zoom-in">
                                <img class="img-responsive img-thumbnail center-block" id="icon" src="{{ CloudStorage::downloadUrl($data->icon) }}" alt="" style="width:150px;">
                                <div class="img-options center-block" style="width:150px">
                                    <div class="img-options-content">
                                        <a href="#" class="btn button-change-profile-picture">
                                            <label for="upload-profile-picture">
                                                <span class="btn btn-primary" id="upload-avatar" style="cursor: pointer">{{ trans('common.upload') }}</span>
                                                <input class="btn-upload" name="icon" id="topic-icon" type="file" title style="margin-top:-32px;" onchange="PreviewImage.topic_preview(this,'icon')">
                                            </label>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <span class="text-center col-xs-12" style="margin-top: 10px">50 x 50</span>
                            <span class="text-center animated fadeInDown col-xs-12 has-error text-danger hidden format-invalid icon" >{{ trans('errors.format_invalid') }}</span>
                            <span class="text-center animated fadeInDown col-xs-12 has-error text-danger hidden size-invalid icon">{{ trans('errors.size_invalid') }}</span>
                        </div>
                    </div>

                    {{--提交--}}
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
    {{--<script src="{{ asset('js/admin/channel.js') }}"></script>--}}
    <script src="https://cdn.bootcss.com/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdn.bootcss.com/bootstrap-datepicker/1.6.1/js/bootstrap-datepicker.min.js"></script>
    <script src="{{ asset('js/admin/edit-tweet.js') }}"></script>
    <script src="https://cdn.bootcss.com/select2/4.0.3/js/i18n/zh-CN.js"></script>

    {{--<script src="{{ asset('js/admin/topic.js') }}"></script>--}}
    <script>

        Lang.number_required = "{{ trans('message.requiredMsg',['attribute' => trans('content.topic_name')]) }}";
        Lang.text          = "{{ trans('errors.number_english_chinese') }}";
        $("input[name='options']").parent().click(function(){
            var color = $(this).children("span").attr('style','color:white');
            color.parent().siblings().children("span").removeAttr('style');
        });
        jQuery(function(){ editTweet.init(); });
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