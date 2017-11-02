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
                {{trans('common.blur')}}
            </h1>
        </div>
        <div class="col-sm-8 col-xs-3">
            <!-- Single button -->
            <div class="btn-group pull-right" id="family-action">
                <button type="button" class="btn bg-red" onclick="javascript:history.back(-1);">
                    {{ trans('common.cancel') }}
                </button>
            </div>
        </div>
    </div>
</div>
<!-- END Page Header -->
<div class="col-xs-12">
    <div class="block-content">
        <div class="row">
            <div class="col-md-5 col-md-offset-1 col-xs-offset-0 col-xs-6 push-15-t">
                <strong>ID : </strong>{{ $blur->id }}
            </div>
            <div class="col-md-5 col-xs-6 push-15-t">
                <strong>{{ trans('app.name')}}<small> {{ trans('multi-lang.zh') }}</small> : </strong>{{ $blur->name_zh }}
            </div>
        </div>
        <div class="row">
            <div class="col-md-5 col-md-offset-1 col-xs-offset-0 col-xs-6 push-15-t">
                <strong>{{ trans('app.name')}}<small> {{ trans('multi-lang.en') }}</small> : </strong>{{ $blur->name_en }}
            </div>
            <div class="col-md-5 col-xs-6 push-15-t">
                <strong>{{ trans('app.blur_class_name') }} : </strong>{{ $blur->belongsToBlurClass->name_zh }}
            </div>
        </div>
        <div class="row">
            <div class="col-md-5 col-md-offset-1 col-xs-offset-0 col-xs-6 push-15-t">
                <strong>{{ trans('app.shutter_speed') }} : </strong>
                @if($blur->shutter_speed == 0)
                    AUTO
                @else
                    {{ '1/'.$blur->shutter_speed }}
                @endif
            </div>
            <div class="col-md-5 col-xs-6 push-15-t">
                <strong>{{ trans('app.gravity') }} : </strong>
                @if(0 == $blur->gravity_sensing)
                    {{ trans('common.close') }}
                @else
                    {{ trans('common.open') }}
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-md-5 col-md-offset-1 col-xs-offset-0 col-xs-6 push-15-t">
                <strong>{{ trans('app.xAlign_offset') }} : </strong>
                {{ $blur->xAlign }}
            </div>
            <div class="col-md-5 col-xs-6 push-15-t">
                <strong>{{ trans('app.yAlign_offset') }} : </strong>
                {{ $blur->yAlign }}
            </div>
        </div>
        <div class="row">
            <div class="col-md-5 col-md-offset-1 col-xs-offset-0 col-xs-6 push-15-t">
                <strong>{{ trans('app.status') }} : </strong>
                @if(0 == $blur->active)
                    {{ trans('app.disable') }}
                @elseif(1 == $blur->active)
                    {{ trans('app.normal') }}
                @elseif(2 == $blur->active)
                    {{ trans('app.test') }}
                @endif
            </div>
            <div class="col-md-5 col-xs-6 push-15-t">
                <strong>{{ trans('app.scale') }} : </strong>
                {{ $blur->scaling_ratio . '%' }}
            </div>
        </div>
        <div class="row">
            <div class="col-md-10 col-md-offset-1 col-xs-offset-0 col-xs-12 push-15-t">
                <strong>{{ trans('app.audio_file') }} : </strong>
                @if(is_null($blur->audio))
                    {{ trans('common.null') }}
                @else
                    {{ asset('/admin/'. $blur->audio) }}
                    <audio src="{{ downloadUrl('blur/'. $blur->audio) }}" controls="controls">
                        {{ trans('errors.not_support_audio') }}
                    </audio>
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-md-10 col-md-offset-1 col-xs-offset-0 col-xs-12 push-15-t">
                <h5>{{ trans('app.blur_class_name') }} :</h5>
                <div class="row">
                    @foreach($parameters as $parameter)
                        <div class="col-xs-6 push-15-t" style="padding-left: 30px;">
                            @foreach($parameter['data'] as $item)
                                <strong>{{ $parameter['name_zh'] == $item['name_zh'] ? $item['name_zh'] : $parameter['name_zh'].' -- '. $item['name_zh'] }} :</strong>
                                {{ $item['value'] }}
                            @endforeach
                            @if(isset($parameter['imgUrl']))
                                <a href="{{ downloadUrl('blur/' . $blur->id . $parameter['imgUrl']) }}" target="_blank">
                                    {{ trans('app.view_image') }}
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-5 col-md-offset-1 col-xs-offset-0 col-xs-6 push-15-t">
                <h5>{{ trans('app.background_image') }} :</h5>
                <div class="row">
                    <div class="col-xs-12 push-15-t">
                        <img class="img-responsive" src="{{ downloadUrl('blur/' . $blur->id . $blur->background) }}">
                    </div>
                </div>
            </div>
            <div class="col-md-5 col-xs-6 push-15-t">
                <h5>{{ trans('app.dynamic_image') }} :</h5>
                <div class="row">
                    <div class="col-xs-12 push-15-t">
                        @if(is_null( $blur->dynamic_image))
                            {{ trans('common.null') }}
                        @else
                            <img class="img-responsive" src="{{ downloadUrl('blur/' . $blur->id . $blur->dynamic_image) }}">
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-10 col-md-offset-1 col-xs-offset-0 col-xs-12 push-15-t">
                <h5>{{ trans('app.face_image') }} :</h5>
                <div class="row">
                    <div class="col-md-6 col-xs-12 push-15-t">
                        @if(is_null($blur->face_tracking))
                            {{ trans('common.null') }}
                        @else
                            <img class="img-responsive" src="{{ downloadUrl('blur/' . $blur->id . $blur->face_tracking) }}">
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-10 col-md-offset-1 col-xs-offset-0 col-xs-12 push-15-t">
                <h5>{{ trans('app.gravity_image') }} :</h5>
                <div class="row">
                    @if(isset($sequence_diagrams))
                        @foreach($sequence_diagrams as $sequence_diagram)
                            <div class="col-xs-6 push-15-t">
                                <img class="img-responsive" src="{{ downloadUrl('blur/' . $blur->id . $sequence_diagram) }}">
                            </div>
                        @endforeach
                    @else
                        <div class="col-xs-6 push-15-t">
                            {{ trans('common.null') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 push-15-t push-50 text-right">
                @if(2 == $blur->active)
                    <button class="btn bg-red" type="button"  data-toggle="modal" data-target="#delete-blur"><i class="fa fa-ban"></i> {{ trans('common.delete') }}</button>
                    <button class="btn btn-warning" type="button"  data-toggle="modal" data-target="#enable-blur">{{ trans('app.enable') }}</button>
                @elseif(1 == $blur->active)
                    <button class="btn btn-warning" type="button"  data-toggle="modal" data-target="#disable-blur">{{ trans('app.disable') }}</button>
                @elseif(0 == $blur->active)
                    <button class="btn btn-warning" type="button"  data-toggle="modal" data-target="#enable-blur">{{ trans('app.enable') }}</button>
                @endif
            </div>
        </div>
    </div>
</div>

@if(2 == $blur->active)
<!-- Delete Modal -->
<div class="modal fade" id="delete-blur" tabindex="-1" role="dialog" aria-labelledby="delete-blurLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="delete-blurLabel">{{ trans('common.delete') }}</h4>
            </div>
            <div class="modal-body">
                {{ trans('message.deleteMsg') }}
            </div>
            <div class="modal-footer">
                <form action="{{ asset('/admin/app/camera/blur/delete/' . $blur->id)}}" method="post">
                    <input type="hidden" name="_token" value="{{ csrf_token()}}">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('common.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ trans('common.sure') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Delete Modal End -->
@endif

@if(2 == $blur->active || 0 == $blur->active)
<!-- Enable Modal -->
<div class="modal fade" id="enable-blur" tabindex="-1" role="dialog" aria-labelledby="enable-blurLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="enable-blurLabel">{{ trans('app.enable') }}</h4>
            </div>
            <div class="modal-body">
                {{ trans('message.enableMsg') }}
            </div>
            <div class="modal-footer">
                <form action="{{ asset('/admin/app/camera/blur/enable/' . $blur->id)}}" method="post">
                    <input type="hidden" name="_token" value="{{ csrf_token()}}">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('common.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ trans('common.sure') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Enable Modal End -->
@endif

@if(1 == $blur->active)
<!-- Disable Modal -->
<div class="modal fade" id="disable-blur" tabindex="-1" role="dialog" aria-labelledby="disable-blurLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="disable-blurLabel">{{ trans('app.disable') }}</h4>
            </div>
            <div class="modal-body">
                {{ trans('message.disableMsg') }}
            </div>
            <div class="modal-footer">
                <form action="{{ asset('/admin/app/camera/blur/disable/' . $blur->id)}}" method="post">
                    <input type="hidden" name="_token" value="{{ csrf_token()}}">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('common.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ trans('common.sure') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Disable Modal End -->
@endif
@endsection

@section('scripts')
    @parent
@endsection

@section('css')
    @parent
    <link rel="stylesheet" href="{{ asset('css/admin/blur.css') }}">
@endsection