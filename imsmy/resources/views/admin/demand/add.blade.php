@extends('admin.layer')
@section('layer-content')
        <!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.demand') }}</li>
                <li><a class="link-effect" href="{{ asset('/admin/config/demand/index') }}">{{trans('common.demand_management')}}</a></li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            <h1 class="page-heading" >
                {{trans('content.add_demand')}}
            </h1>
        </div>
        <div class="col-sm-8 col-xs-3">
            <div class="btn-group pull-right" id="family-action">
                <a type="button" class="btn btn-primary" href="{{ asset('/admin/config/demand/index') }}">
                    {{ trans('common.cancel') }}
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Page Content -->
<div class="content">
    <div class="row" style="margin-top: 5%;">
        <div class="col-md-12 col-xs-12 block">
            <form class="js-validation-topic form-horizontal" enctype="multipart/form-data"
                  action="{{ asset('/admin/config/demand/insert') }}" method="POST">

                <input type="hidden" name="_token" value="{{ csrf_token()}}">

                {{--名称--}}
                <div class="form-group" style="margin-top:5%">
                    <div class="col-md-8 col-md-offset-1">
                        <label for="name">{{ trans('content.demand_name') }} <span class="text-danger">*</span></label>
                        <div class="form-material form-material-primary">
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}">
                        </div>
                    </div>
                </div>

                {{--父级--}}
                <div class="form-group" >
                    <div class="col-md-8 col-md-offset-1" style="margin-top:5%">
                        <label for="name">{{ trans('content.demand_father') }} <span class="text-danger">*</span></label>
                        <div class="form-material">
                            <select class="form-control" id="contact2-subject" name="pid" size="1">
                                <option value='0'>顶级</option>
                                @foreach($datas as $data)
                                    <option value="{{ $data->id }}" >{{ $data->name }}</option>
                                @endforeach
                            </select>
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
    <!-- END Dynamic Table Full -->
    <img id="is-valid" style="visibility: hidden">
    <div class="hidden" id="topic-flag" data-value="0"></div>
</div>
@endsection

@section('scripts')
    @parent

{{--    <script src="{{ asset('js/core/jquery.validate.min.js') }}"></script>--}}
{{--    <script src="{{ asset('js/admin/topic.js') }}"></script>--}}
    <script>
        {{--Lang.name_required = "{{ trans('message.requiredMsg',['attribute' => trans('content.name')]) }}";--}}
//        jQuery(function(){ Topic.init(); });

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