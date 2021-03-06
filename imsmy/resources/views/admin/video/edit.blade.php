@extends('admin.layer')
@section('layer-content')
        <!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.video') }}</li>
                <li>
                    <a class="link-effect" href="{{ asset($active == 1 ? '/admin/video/index' : ($active == 2 ? '/admin/video/recycle' : '/admin/video/check')) }}">
                        {{ ($active == 1 ? trans('content.normal') : ($active == 2 ? trans('common.shield') : trans('content.wait'))).trans('common.video') }}
                    </a>
                </li>
                <li><a class="link-effect" href="{{ asset('/admin/video/details?id=' . $video['id']) }}">{{ $video['id'] }}</a></li>
            </ol>
        </div>

        <div class="col-sm-12">
            <!-- Single button -->
            <div class="btn-group pull-right" id="family-action">
                <a type="button" class="btn bg-red" href="{{ asset('/admin/video/details?id=' . $video['id']) }}" >
                    {{ trans('common.cancel') }}
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Page Content -->
<div class="content">
    <div class="row">
        <div class="col-md-10 col-xs-12 block col-md-offset-1">
            @if (count($errors) > 0)
                <div class="form-group error">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form class="js-validation-channel form-horizontal"
                  enctype="multipart/form-data"
                  action="{{ asset('/admin/video/update') }}"
                  method="POST">
                <input type="hidden" name="_method" value="POST">
                <input type="hidden" name="_token" value="{{ csrf_token()}}">
                <input type="hidden" name="_time" value="{{ $video['updated_at'] }}">
                <input type="hidden" name="_timezone" value="">
                <input type="hidden" name="id" value="{{ $video['id'] }}">

                {{--置顶--}}
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

                {{--推荐--}}
                <div class="form-group" style="margin-top:5%">
                    <div class="col-md-4 col-md-offset-2">
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

                {{--热门--}}
                <div class="form-group" style="margin-top:5%">
                    <div class="col-md-4 col-md-offset-2">
                        <strong>{{ trans('content.hot') . ' : ' }}</strong>
                        <label class="css-input switch switch-success">
                            {{ trans('common.no') }} &nbsp;<input type="checkbox" name="hot_check" data-value="hot"><span></span> {{ trans('common.yes') }}
                        </label>
                    </div>
                </div>

                {{--每日推送--}}
                <div class="form-group" style="margin-top:5%">
                    <div class="col-md-4 col-md-offset-2">
                        <strong>{{ trans('content.push') . ' : ' }}</strong>
                        <label class="css-input switch switch-success">
                            {{ trans('common.no') }} &nbsp;<input type="checkbox" name="push_check" id="recommend_check" data-value="push"><span></span> {{ trans('common.yes') }}
                        </label>
                    </div>

                    <div class="col-md-4 hidden" id="push">
                        {{--<div class="">--}}
                            <label class="col-md-6 control-label" for="recommend_date">{{ trans('content.push_date') }}</label>
                            <div class="col-md-6 remove-padding">
                                <input type="text" class="datepicker js-datepicker form-control" name="push_date" id="" data-date-format="yyyymmdd" value="">
                            </div>
                        {{--</div>--}}
                    </div>
                </div>
                @if(Session::has('push'))
                    <div class="help-block animated fadeInDown col-md-offset-2"><span class="text-danger">{{Session::get('push')}}</span></div>
                @endif

                {{--话题--}}
                <div class="form-group" >
                    <div class="col-md-8 col-md-offset-2" style="padding-top: 20px">
                        <strong>{{ trans('content.topic') . ' : ' }}</strong>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-8 col-md-offset-2">
                        <select class="js-example-basic-multiple-topic col-md-12" name="topics[]" id="topics" multiple>
                            @foreach($topics as $topic)
                                 <option value="{{ $topic->id }}">{{ $topic->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{--活动--}}
                <div class="form-group" >
                    <div class="col-md-8 col-md-offset-2" style="padding-top: 20px">
                        <strong>{{ trans('content.activity') . ' : ' }}</strong>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-8 col-md-offset-2">
                        <select class="js-example-basic-multiple-activity col-md-12" name="activities[]" id="activities" multiple>
                            @foreach($activities as $activity)
                                <option value="{{ $activity->id }}">{{ $activity->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{--频道--}}
                <div class="form-group" >
                    <div class="col-md-8 col-md-offset-2" style="padding-top: 20px">
                        <strong>{{ trans('content.channel') . ' : ' }}</strong>
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
        Lang.tweet_data = JSON.parse('{{ $video }}'.replace(/&quot;/g,'"'));
        Lang.topic_data = JSON.parse('{{ $topics_in }}'.replace(/&quot;/g,'"'));
        Lang.activity_data = JSON.parse('{{ $activities_in }}'.replace(/&quot;/g,'"'));
//        console.log(Lang.topic_data);

    </script>
@endsection

@section('css')
    @parent
    <link href="https://cdn.bootcss.com/select2/4.0.3/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.bootcss.com/bootstrap-datepicker/1.6.1/css/bootstrap-datepicker.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/blur.css') }}">
    {{--<style>--}}
        {{--#channel-icon-error{--}}
            {{--text-align: center;--}}
        {{--}--}}
        {{--.icon{--}}
            {{--font-style: italic;--}}
            {{--font-size: 13px;--}}
        {{--}--}}
        {{--.margin-top-30{--}}
            {{--margin-top:30px !important;--}}
        {{--}--}}
    {{--</style>--}}
@endsection