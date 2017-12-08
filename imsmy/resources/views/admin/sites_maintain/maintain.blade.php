@extends('admin/layer')
@section('layer-content')
        <!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li><a class="link-effect" href="{{ asset('/admin/maintain/index') }}">{{ trans('content.sites-maintain') }}</a></li>
                <li>{{ trans('content.on-off') }}</li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            <h1 class="page-heading" >
                {{trans('content.on-off')}}
            </h1>
        </div>
        <div class="col-sm-8 col-xs-3">
            <!-- Single button -->
            <div class="btn-group pull-right" id="family-action">
                <a type="button" class="btn bg-red" href="{{ asset('/admin/maintain/index') }}">
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
                <form class="js-validation-topic form-horizontal" enctype="multipart/form-data"
                      action="{{ asset('/admin/maintain/status') }}" method="POST">

                    <input type="hidden" name="_token" value="{{ csrf_token()}}">
                    <input type="hidden" name="id" value="{{ $data -> id }}">

                    {{--状态--}}
                    <div class="form-group" style="margin-top:5%">
                        <div class="col-md-8 col-md-offset-1">
                            <label for="name">{{ trans('common.active') }} <span class="text-danger">*</span></label>
                            <div class="col-xs-12 " style="margin-top:5%">
                                <label class="css-input css-radio css-radio-warning push-30-r">
                                    <input type="radio" name="status" value="1"  {{ $data -> status == 1 ? 'checked' : '' }}>
                                    <span></span> {{ trans('content.normal') }}
                                </label>
                                <label class="css-input css-radio css-radio-warning push-30-r">
                                    <input type="radio" name="status" value="2"  {{ $data -> status == 2 ? 'checked' : '' }}>
                                    <span></span> {{ trans('content.disable') }}
                                </label>
                            </div>
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
    </div>
</div>

@endsection
