@extends('admin/layer')
@section('layer-content')
        <!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li><a class="link-effect" href="{{ asset('/admin/maintain/index') }}">{{ trans('content.sites-maintain') }}</a></li>
                <li>{{ trans('common.details') }}</li>
                <li>{{ $data -> id }}</li>
            </ol>
        </div>
        <div class="col-sm-12">
            <div class="pull-right video-edit">
                {{--@if($data->active == 1)--}}
                    {{--<button type="button" class="btn btn-sm bg-red" data="{{ $video->id }}" status=2 onclick="_videoActive($(this))">--}}
                        {{--{{ trans('common.shield') }}--}}
                    {{--</button>--}}
                    <button type="button" class="btn btn-sm bg-red" data="{{ $data->id }}" status="{{ $data->active == 1 ? 2 : 1 }}" onclick="_sitesConfigHandle($(this))">
                        {{ $data->active == 1 ? '屏蔽' : '通过' }}
                    </button>
                {{--@endif--}}
                <a class="btn btn-sm btn-success" type="button" href="{{ asset('/admin/maintain/edit?id=' . $data->id ) }}">
                    {{trans('common.edit')}}
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
                <form class="js-validation-topic form-horizontal">

                    {{--网站标题--}}
                    <div class="form-group">
                        <div class="col-md-8 col-md-offset-2">
                            <label for="exampleInputEmail1">{{ trans('common.title') }} <span class="text-danger">*</span></label>
                            <div class="form-material form-material-primary">
                                <input type="text" class="form-control" id="name" name="title" value="{{ $data->title }}" placeholder="{{ trans('common.title') }}" disabled>
                            </div>
                        </div>
                    </div>

                    {{--网站关键词--}}
                    <div class="form-group">
                        <div class="col-md-8 col-md-offset-2">
                            <label for="exampleInputEmail1">{{ trans('common.keywords') }} <span class="text-danger">*</span></label>
                            <div class="form-material form-material-primary">
                                <input type="text" class="form-control" id="intro" name="keywords" value="{{ $data->keywords }}" placeholder="{{ trans('common.keywords') }}" disabled>
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
                            </div>
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
    <script src="{{ asset('js/admin/video/video-handle.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/layer/layer.js') }}"></script>
@endsection