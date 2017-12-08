@extends('admin.layer')
@section('layer-content')
        <!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.content') }}</li>
                <li><a class="link-effect" href="{{ asset('admin/content/topic') }}">{{trans('common.topic_content')}}</a></li>
                <li><a class="link-effect" href="">{{trans('content.recommend') . '&' . trans('content.channel')}}</a></li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            <h1 class="page-heading" >
                {{trans('content.recommend') . '&' . trans('content.channel')}}
            </h1>
        </div>
        <div class="col-sm-8 col-xs-3">
            <!-- Single button -->
            <div class="btn-group pull-right" id="family-action">
                <a type="button" class="btn bg-red" href="{{ asset('/admin/content/topic/' . $topic->id) }}">
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
                  action="{{ asset('/admin/content/topic/'. $topic->id) }}"
                  method="POST">
                <input type="hidden" name="_method" value="PUT">
                <input type="hidden" name="_token" value="{{ csrf_token()}}">
                <input type="hidden" name="_time" value="{{ strtotime($topic->updated_at) }}">
                <input type="hidden" name="_timezone" value="">
                <div class="form-group" style="margin-top:5%">
                    <div class="col-md-4 col-md-offset-2" style="padding-top: 20px">
                        <strong>{{ trans('content.recommend') . ' : ' }}</strong>
                        <label class="css-input switch switch-success">
                            {{ trans('common.no') }} &nbsp;
                            <input type="checkbox" name="recommend_check" id="recommend_check" data-value="recommend" {{ $channel_topic ? 'checked' : '' }} value="{{ $channel_topic ? 1 : 0 }}">
                            <span></span>
                            {{ trans('common.yes') }}
                        </label>
                    </div>
                </div>
                <div class="form-group hidden" id="recommend">
                    <div class="col-md-4 col-md-offset-2">
                        <label class="col-md-6 control-label" for="recommend_date">{{ trans('content.expire_date') }}</label>
                        <div class="col-md-6 remove-padding">
                            <input type="text" class="datepicker js-datepicker form-control" name="recommend_date" id="recommend_date" data-date-format="yyyy-mm-dd" value="{{ $channel_topic ? date('Y-m-d',strtotime($channel_topic->created_at)) : '' }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="col-md-5 control-label" for="recommend_time">{{ trans('content.expire_time') }}</label>
                        <div class="col-md-7 remove-padding">
                            <input type="time" class="form-control" name="recommend_time" id="recommend_time" value="{{ $channel_topic ? date('H:i:s',strtotime($channel_topic->created_at)) : '' }}">
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
                                @if($channel_topic)
                                    <option value="{{ $channel->id }}" {{ $channel->id == $channel_topic->channel_id ? 'selected' : '' }}>{{ $channel->name }}</option>
                                @else
                                    <option value="{{ $channel->id }}">{{ $channel->name }}</option>
                                @endif
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
</div>
@endsection

@section('scripts')
    @parent

    <script src="{{ asset('js/core/jquery.validate.min.js') }}"></script>
    <script src="https://cdn.bootcss.com/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdn.bootcss.com/bootstrap-datepicker/1.6.1/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdn.bootcss.com/select2/4.0.3/js/i18n/zh-CN.js"></script>
    <script src="{{ asset('js/admin/topic_recommend_channel.js') }}"></script>
    <script>
        Lang.date_invalid = "{{ trans('errors.date_invalid') }}";
        Lang.time_invalid = "{{ trans('errors.time_invalid') }}";
        Lang.name_required = "{{ trans('message.requiredMsg',['attribute' => trans('content.topic_name')]) }}";
        Lang.topic_data = JSON.parse('{{$topic}}'.replace(/&quot;/g,'"'));
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