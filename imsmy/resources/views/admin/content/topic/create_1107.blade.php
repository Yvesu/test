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
                        <div class="form-material">
                            <textarea class="form-control" id="comment" name="comment" rows="2">{{ old('comment') }}</textarea>
                            <label for="comment">{{ trans('content.topic_comment') }}</label>
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
    <script>
        Lang.name_required = "{{ trans('message.requiredMsg',['attribute' => trans('content.topic_name')]) }}";
        Lang.text          = "{{ trans('errors.number_english_chinese') }}";
        jQuery(function(){ Topic.init(); });

    </script>
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