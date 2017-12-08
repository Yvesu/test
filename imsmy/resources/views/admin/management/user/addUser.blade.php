@extends('admin/layer')
@section('layer-content')
        <!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.user_management') }}</li>
                <li><a class="link-effect" href="">{{trans('content.add_web_user')}}</a></li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            <h1 class="page-heading" >
                {{trans('content.add_web_user')}}
            </h1>
        </div>
        <div class="col-sm-8 col-xs-3">
            <!-- Single button -->
            <div class="btn-group pull-right" id="family-action">
                <a type="button" class="btn bg-red" href="{{ asset('/admin/user/list/local') }}">
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
                      action="{{ asset('/admin/user/list/insert') }}"
                      method="POST">
                    <input type="hidden" name="_timezone" value="Asia/Shanghai">
                    <input type="hidden" name="_token" value="{{ csrf_token()}}">

                    {{--用户名--}}
                    <div class="form-group" style="margin-top:5%">
                        <div class="col-md-8 col-md-offset-2">
                            <label for="exampleInputEmail1">{{ trans('common.username') }} <span class="text-danger">*</span></label>
                            <div class="form-material form-material-primary">
                                <input type="text" class="form-control" id="username" name="username" value="{{ old('username') }}" placeholder="{{ trans('content.add_phone_number') }}">
                            </div>
                        </div>
                        @if(Session::has('username'))
                            <div class="help-block text-right animated fadeInDown"><span class="text-danger">{{Session::get('username')}}</span></div>
                        @endif
                    </div>

                    {{--密码--}}
                    <div class="form-group" style="margin-top:5%">
                        <div class="col-md-8 col-md-offset-2">
                            <label for="exampleInputEmail1">{{ trans('common.password') }} <span class="text-danger">*</span></label>
                            <div class="form-material form-material-primary">
                                <input type="password" class="form-control" id="password" name="password" value="{{ old('password') }}" placeholder="{{ trans('content.add_password') }}">
                            </div>
                        </div>
                    </div>

                    {{--用户昵称--}}
                    <div class="form-group" style="margin-top:5%">
                        <div class="col-md-8 col-md-offset-2">
                            <label for="exampleInputEmail1">{{ trans('common.nickname') }}</label>
                            <div class="form-material form-material-primary">
                                <input type="text" class="form-control" id="nickname" name="nickname" value="{{ old('nickname') }}" placeholder="{{ trans('content.add_nickname') }}">
                            </div>
                        </div>
                    </div>

                    {{--性别--}}
                    <div class="form-group" style="margin-top:5%">
                        <div class="col-md-8 col-md-offset-2">
                            <label for="name">{{ trans('common.sex') }}</label>
                            <select class="form-control" name='sex'>
                                <option value="0">{{ trans('common.female') }}</option>
                                <option value="1">{{ trans('common.male') }}</option>
                            </select>
                        </div>
                    </div>

                    {{--头像添加--}}
                    <div class="form-group" style="margin-top:5%">
                        <div class="col-md-8 col-md-offset-2">
                            <label for="name">{{ trans('common.avatar') }}</label>
                        </div>
                        <div class="col-md-4 col-md-offset-4 col-xs-12" style="margin-top: 20px">
                            <div class="img-container fx-opt-zoom-in">
                                <img class="img-responsive img-thumbnail center-block" id="icon" src="{{ asset('/admin/images/default/default_icon.png') }}" alt="" style="width:150px;">
                                <div class="img-options center-block" style="width:150px">
                                    <div class="img-options-content">
                                        <a href="#" class="btn button-change-profile-picture">
                                            <label for="upload-profile-picture">
                                                <span class="btn btn-primary" id="upload-avatar" style="cursor: pointer">{{ trans('common.upload') }}</span>
                                                <input class="btn-upload" name="avatar" id="topic-icon" type="file" title style="margin-top:-32px;" onchange="PreviewImage.topic_preview(this,'icon')">
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

    <script src="https://cdn.bootcss.com/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdn.bootcss.com/bootstrap-datepicker/1.6.1/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdn.bootcss.com/select2/4.0.3/js/i18n/zh-CN.js"></script>

    <script src="{{ asset('js/core/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('js/admin/topic.js') }}"></script>
    <script>


        Lang.username_required = "{{ trans('message.requiredMsg',['attribute' => trans('common.username')]) }}";
        Lang.password_required = "{{ trans('message.requiredMsg',['attribute' => trans('common.password')]) }}";
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