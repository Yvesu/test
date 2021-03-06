@extends('admin.layer')
@section('layer-content')
        <!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.content') }}</li>
                <li><a class="link-effect" href="{{ asset('admin/content/photo_album/') }}">{{trans('common.photo_album')}}</a></li>
                <li><a class="link-effect" href="{{ asset('/admin/content/photo_album/' . $photo_album->id) }}">{{ $photo_album->id }}</a></li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            <h1 class="page-heading" >
                {{trans('common.photo_album')}} : {{ $photo_album->name }}
            </h1>
        </div>
        <div class="col-sm-8 col-xs-3">
            <!-- Single button -->
            <div class="btn-group pull-right" id="family-action">
                <a type="button" class="btn bg-red" href="{{ asset('admin/content/photo_album/' . $photo_album->id) }}">
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
            <form class="js-validation-channel form-horizontal"
                  enctype="multipart/form-data"
                  action="{{ asset('/admin/content/photo_album/'. $photo_album->id) }}"
                  method="POST">
                <input type="hidden" name="_method" value="PUT">
                <input type="hidden" name="_token" value="{{ csrf_token()}}">
                <input type="hidden" name="_time" value="{{ strtotime($photo_album->updated_at) }}">
                <input type="hidden" name="_timezone" value="">
                <div class="form-group" style="margin-top:5%">
                    <div class="col-md-8 col-md-offset-2" style="padding-top: 20px">
                        <strong>{{ trans('content.top') . ' : ' }}</strong>
                        <label class="css-input switch switch-success">
                            {{ trans('common.no') }} &nbsp;
                            <input type="checkbox" name="top_check"
                                   id="top_check" data-value="top">
                            <span></span>
                            {{ trans('common.yes') }}
                        </label>
                    </div>
                </div>
                <div class="form-group hidden" id="top">
                    <div class="col-md-4 col-md-offset-2">
                        <label class="col-md-6 control-label" for="top_date">{{ trans('content.expire_date') }}</label>
                        <div class="col-md-6 remove-padding">
                            <input type="text"
                                   class="datepicker js-datepicker form-control"
                                   name="top_date" id="top_date" data-date-format="yyyy-mm-dd"
                                   value="" >
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="col-md-5 control-label" for="top_time">{{ trans('content.expire_time') }}</label>
                        <div class="col-md-7 remove-padding">
                            <input type="time" class="form-control" name="top_time" id="top_time" value="">
                        </div>
                    </div>
                </div>
                <div class="form-group" style="margin-top:5%">
                    <div class="col-md-4 col-md-offset-2" style="padding-top: 20px">
                        <strong>{{ trans('content.recommend') . ' : ' }}</strong>
                        <label class="css-input switch switch-success">
                            {{ trans('common.no') }} &nbsp;<input type="checkbox" name="recommend_check" id="recommend_check" data-value="recommend"><span></span> {{ trans('common.yes') }}
                        </label>
                    </div>
                </div>
                <div class="form-group hidden" id="recommend">
                    <div class="col-md-4 col-md-offset-2">
                        <label class="col-md-6 control-label" for="recommend_date">{{ trans('content.expire_date') }}</label>
                        <div class="col-md-6 remove-padding">
                            <input type="text" class="datepicker js-datepicker form-control" name="recommend_date" id="recommend_date" data-date-format="yyyy-mm-dd" value="">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="col-md-5 control-label" for="recommend_time">{{ trans('content.expire_time') }}</label>
                        <div class="col-md-7 remove-padding">
                            <input type="time" class="form-control" name="recommend_time" id="recommend_time" value="">
                        </div>
                    </div>
                </div>
                <div class="form-group" style="margin-top:5%">
                    <div class="col-md-4 col-md-offset-2" style="padding-top: 20px">
                        <strong>{{ trans('content.label')}}</strong>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-8 col-md-offset-2">
                        <select class="js-example-basic col-md-12" name="label_name" id="label_name">
                            <option value="">{{ trans('common.plzSelect') }}</option>
                            @foreach($labels as $label)
                                <option value="{{ $label->id }}">{{ $label->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group hidden" id="label">
                    <div class="col-md-4 col-md-offset-2" style="padding-top: 20px">
                        <label class="col-md-6 control-label" for="label_date">{{ trans('content.expire_date') }}</label>
                        <div class="col-md-6 remove-padding">
                            <input type="text" class="datepicker js-datepicker form-control" name="label_date" id="label_date" data-date-format="yyyy-mm-dd" value="">
                        </div>
                    </div>
                    <div class="col-md-4" style="padding-top: 20px">
                        <label class="col-md-5 control-label" for="label_time">{{ trans('content.expire_time') }}</label>
                        <div class="col-md-7 remove-padding">
                            <input type="time" class="form-control" name="label_time" id="label_time" value="">
                        </div>
                    </div>
                </div>
                <div class="form-group" style="margin-top:5%">
                    <div class="col-md-8 col-md-offset-2" style="padding-top: 20px">
                        <strong>{{ trans('content.channel') }}</strong>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-8 col-md-offset-2">
                        <select class="js-example-basic-multiple col-md-12" name="channels[]" id="channels" multiple>
                            @foreach($channels as $channel)
                                 <option value="{{ $channel->id }}">{{ $channel->name }}</option>
                            @endforeach
                        </select>
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
    <div class="hidden" id="channel-flag" data-value="1"></div>
</div>
@endsection

@section('scripts')
    @parent

    <script src="{{ asset('js/core/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('js/admin/channel.js') }}"></script>
    <script src="https://cdn.bootcss.com/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdn.bootcss.com/bootstrap-datepicker/1.6.1/js/bootstrap-datepicker.min.js"></script>
    <script src="{{ asset('js/admin/edit-tweet.js') }}"></script>
    <script src="https://cdn.bootcss.com/select2/4.0.3/js/i18n/zh-CN.js"></script>
    <script>
        Lang.date_invalid = "{{ trans('errors.date_invalid') }}";
        Lang.time_invalid = "{{ trans('errors.time_invalid') }}";
        Lang.name_required = "{{ trans('message.requiredMsg',['attribute' => trans('content.topic_name')]) }}";
        jQuery(function(){Channel.init();});
        //这里因为[] 原因
        Lang.tweet_data = JSON.parse('{{ $photo_album }}'.replace(/&quot;\[/g,'[').replace(/]&quot;/g,']').replace(/&quot;/g,'"'));

    </script>
@endsection

@section('css')
    @parent
    <link href="https://cdn.bootcss.com/select2/4.0.3/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.bootcss.com/bootstrap-datepicker/1.6.1/css/bootstrap-datepicker.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/blur.css') }}">
    <style>
        #channel-icon-error{
            text-align: center;
        }
        .icon{
            font-style: italic;
            font-size: 13px;
        }
        .margin-top-30{
            margin-top:30px !important;
        }
    </style>
@endsection