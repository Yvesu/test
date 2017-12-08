@extends('admin.layer')
@section('layer-content')
        <!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.activity').trans('common.bonus').trans('common.allocation') }}</li>
                <li>
                    <a class="link-effect" href="{{ asset('/admin/content/competition/index') }}">
                        {{ trans('common.bonus').trans('common.allocation').trans('common.management') }}
                    </a>
                </li>
                <li>{{ trans('common.edit') }}</li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            <h1 class="page-heading" >
                {{ trans('common.bonus').trans('common.allocation') }}
            </h1>
        </div>
        <div class="col-sm-8 col-xs-3">
            <!-- Single button -->
            <div class="btn-group pull-right" id="family-action">
                <a type="button" class="btn btn-primary" href="{{ asset('/admin/content/competition/index') }}">
                    {{ trans('common.cancel') }}
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Page Content -->
<div class="content">
    <div class="row" >
        <div class="col-md-10 col-xs-12 block col-md-offset-1">
            <form class="js-validation-topic form-horizontal"
                  enctype="multipart/form-data"
                  action="{{ asset('/admin/content/competition/update') }}"
                  method="POST">
                <input type="hidden" name="_token" value="{{ csrf_token()}}">
                <input type="hidden" name="type" value="{{ $type }}">

                @if(in_array($type,[1,2]))
                @foreach($data as $key => $value)
                    <div class="form-group" style="margin-top:5%">
                        <div class="col-md-8 col-md-offset-2">
                            @if(1 == $type)
                            <label for="name">第{{ $value -> level }}级 <span class="text-danger">*</span></label>
                            <div class="form-material form-material-primary">
                                <input type="text" class="form-control" name="{{ 'id_'.$value -> id }}" value="{{ $value -> count_user }}">
                            </div>
                                @elseif(2 == $type)
                                <div class="form-material form-material-primary input-group">
                                    <input class="form-control" type="text" name="{{ 'id_'.$value -> id }}" value="{{ $value -> amount*100 }}" style="margin-top:3%">
                                    <label for="name">{{ trans('common.bonus').trans('common.proportion') }} <span class="text-danger">*</span></label>
                                    <span class="input-group-addon">%</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                @elseif(3 == $type)
                    <div class="form-group" style="margin-top:5%">
                        <div class="col-md-8 col-md-offset-2">
                            <div class="form-material form-material-primary input-group">
                                <input class="form-control" type="text" name="prorata" value="{{ $data -> first() -> prorata*100 }}" style="margin-top:3%">
                                <label for="name">{{ trans('common.follower').trans('common.proportion') }} <span class="text-danger">*</span></label>
                                <span class="input-group-addon">%</span>
                            </div>
                        </div>
                    </div>
                @endif
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