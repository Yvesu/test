@extends('admin.layer')
@section('layer-content')
        <!-- Page Header -->
<div class="content bg-gray-lighter">
    <div class="row items-push">
        <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
            <ol class="breadcrumb push-10-t">
                <li>{{ trans('common.audio').trans('common.file') }}</li>
                <li>
                    <a class="link-effect" href="{{ asset('/admin/make/audio/file/index') }}">
                        {{ trans('common.audio').trans('common.file').trans('common.management') }}
                    </a>
                </li>
                <li>{{ trans('common.add') }}</li>
            </ol>
        </div>
        <div class="col-sm-4 col-xs-9">
            <h1 class="page-heading" >
                {{ trans('common.add').trans('common.audio').trans('common.file') }}
            </h1>
        </div>
        <div class="col-sm-8 col-xs-3">
            <div class="btn-group pull-right" id="family-action">
                <a type="button" class="btn btn-primary" href="{{ asset('/admin/make/audio/file/index') }}">
                    {{ trans('common.cancel') }}
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Page Content -->
<div class="content">
    <div class="row">
        <div class="col-md-12 col-xs-12 block">
            <form class="js-validation-topic form-horizontal" enctype="multipart/form-data"
                  action="{{ asset('/admin/make/audio/file/insert') }}" method="POST">

                @if (count($errors) > 0)
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <input type="hidden" name="_token" value="{{ csrf_token()}}">

                {{--名称--}}
                <div class="form-group" style="margin-top:5%">
                    <div class="col-md-8 col-md-offset-2">
                        <label for="name">{{ trans('content.name') }} <span class="text-danger">*</span></label>
                        <div class="form-material form-material-primary">
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}">
                        </div>
                    </div>
                </div>
                {{--介绍--}}
                <div class="form-group" style="margin-top:5%">
                    <div class="col-md-8 col-md-offset-2">
                        <label for="name">{{ trans('content.intro') }} <span class="text-danger">*</span></label>
                        <div class="form-material form-material-primary">
                            <input type="text" class="form-control" id="intro" name="intro" value="{{ old('intro') }}">
                        </div>
                    </div>
                </div>

                {{--目录--}}
                <div class="form-group" style="margin-top:5%">
                    <div class="col-md-8 col-md-offset-2">
                        <label for="name">{{ trans('common.folder') }} <span class="text-danger">*</span></label>
                        <div class="form-material form-material-primary">
                            <select class="form-control" name="folder_id" id="">
                                @foreach($folder as $key=>$value)
                                    <option value="{{ $value->id }}">{{ $value->name }}</option>
                                    @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{--上传--}}
                <div class="form-group" style="margin-top:5%">
                    <div class="col-md-8 col-md-offset-2">
                        <label for="name">{{ trans('common.upload') }} <span class="text-danger">*</span></label>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="btn-group pull-right" id="upload-file">
                                    <a type="button" id="pickfiles" class="btn bg-primary pull-right" href="#">
                                        {{ trans('common.upload') . trans('common.audio') }}
                                    </a>
                                    <input type="hidden" class="form-control" id="key" name="key" >
                                    <div class="help-block text-right animated fadeInDown hidden">
                                        <span class="text-danger">{{ trans('errors.format_invalid') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group hidden">
                    <div class="col-md-8 col-md-offset-2">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="progress active">
                                    <div class="upload-file-bar progress-bar progress-bar-success progress-bar-striped"
                                         role="progressbar" aria-valuenow="50"
                                         aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>
                                </div>
                                <div class="upload-file-text"></div>
                            </div>
                            <div class="col-md-3 pull-right upload-cancel">
                                <div class="btn-group pull-right" id="cancel-upload">
                                    <a type="button" class="btn btn-xs bg-red" href="#">
                                        {{ trans('common.cancel') . trans('common.upload') }}
                                    </a>
                                </div>
                            </div>
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
    <div class="hidden" id="video-flag" data-value="0"></div>
</div>
@endsection
@section('scripts')
    @parent
    <script src="{{ asset('js/core/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('js/admin/video.js') }}"></script>
    <script src="{{ asset('js/plugins/qiniu/moxie.js') }}"></script>
    <script src="{{ asset('js/plugins/qiniu/plupload.dev.js') }}"></script>
    <script src="{{ asset('js/plugins/qiniu/qiniu.js') }}"></script>
    <script src="{{ asset('js/plugins/qiniu/zh_CN.js') }}"></script>
    <script>
        Lang.name_required = "{{ trans('message.requiredMsg',['attribute' => trans('content.video_name')]) }}";
        Lang.video_required = "{{ trans('message.uploadMsg',['attribute' => trans('common.video')]) }}";
        Lang.domain    = "{{ CloudStorage::getDomain() }}";
        Lang.uploaded = "{{ trans('common.uploaded') }}";
        Lang.speed = "{{ trans('common.speed') }}";
        Lang.completed = "{{ trans('common.completed') }}";
        Lang.click_view = "{{ trans('content.click_view') }}";
        Lang.file_size = "{{ trans('content.file_size') }}";
        {{--Lang.screen_shot_required    = "{{ trans('message.uploadMsg',['attribute' => trans('common.screen_shot')]) }}";--}}
        jQuery(function(){ Video.init(); });
    </script>
    <script src="{{ asset('js/admin/add_audio.js') }}"></script>
    <script src="{{ asset('js/plugins/qiniu/ui.js') }}"></script>
@endsection

