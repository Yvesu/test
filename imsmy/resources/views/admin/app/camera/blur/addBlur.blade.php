@extends('admin.layer')

@section('layer-content')
    <div id="alert-div" class="col-lg-2 col-md-4 col-xs-5 content-alert"></div>
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
                    {{ trans('app.add_blur') }}
                </h1>
            </div>
            <div class="col-sm-8 col-xs-3">
                <!-- Single button -->
                <div class="btn-group pull-right" id="family-action">
                    <a type="button" class="btn bg-red" href="{{ asset('/admin/app/camera/blur') }}">
                        {{ trans('common.cancel') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- END Page Header -->

    <!-- Page Content -->
    <div class="content">
        <div class="block">
            <div class="row">
                <div class="col-lg-12">
                    <!-- Simple Classic Progress Wizard (.js-wizard-simple class is initialized in js/pages/add-admin.js) -->
                    <!-- For more examples you can check out http://vadimg.com/twitter-bootstrap-wizard-example/ -->
                    <div class="js-wizard-blur block">
                    <!-- Step Tabs -->
                    <ul class="nav nav-tabs nav-justified">
                        <li>
                            <a class="active col-xs-2 col-md-12" href="#blur-step1" data-toggle="tab">1</a>
                        </li>
                        <li class="">
                            <a class="inactive col-xs-2 col-md-12" href="#blur-step2" data-toggle="tab">2</a>
                        </li>
                        <li class="">
                            <a class="inactive col-xs-2 col-md-12" href="#blur-step3" data-toggle="tab">3</a>
                        </li>
                        <li class="">
                            <a class="inactive col-xs-2 col-md-12" href="#blur-step4" data-toggle="tab">4</a>
                        </li>
                        <li class="">
                            <a class="inactive col-xs-2 col-md-12" href="#blur-step5" data-toggle="tab">5</a>
                        </li>
                        <li class="">
                            <a class="inactive col-xs-2 col-md-12" href="#blur-step6" data-toggle="tab">6</a>
                        </li>
                    </ul>
                    <!-- END Step Tabs -->

                    <!-- Steps Progress -->
                    <div class="block-content block-content-mini block-content-full border-b">
                        <div class="wizard-progress progress progress-mini remove-margin-b">
                            <div class="progress-bar bg-primary" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 0"></div>
                        </div>
                    </div>
                    <!-- END Steps Progress -->

                    <!-- Steps Content -->
                    <div class="block-content tab-content">

                        <!-- Step 1 -->
                        <div class="tab-pane fade fade-up in push-30-t push-50 active" id="blur-step1">
                            <form class="form-horizontal" id="form-blur-step1" action="{{ asset('/admin/app/camera/blur/add/blur_class') }}" method="POST">
                                <input type="hidden" name="_token" value="{{ csrf_token()}}">
                                <input type="hidden" name="key" value="{{ $key }}">
                                <div class="form-group">
                                    <div class="col-md-6 col-md-offset-3">
                                        <div class="form-material form-material-primary">
                                            <input class="form-control" id="name_zh" name="name_zh">
                                            <label for="name_zh">{{ trans('app.name') }} <small>{{trans('multi-lang.zh')}}</small> <span class="text-danger">*</span></label>
                                            <div id="name_zh_error"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-md-6 col-md-offset-3">
                                        <div class="form-material form-material-primary">
                                            <input class="form-control" id="name_en" name="name_en">
                                            <label for="name_en">{{ trans('app.name') }} <small>{{trans('multi-lang.en')}}</small><span class="text-danger">*</span></label>
                                            <div id="name_en_error"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-md-6 col-md-offset-3">
                                        <div class="form-material form-material-primary">
                                            <select class="form-control" id="blur_class" name="blur_class" size="1">
                                                <option value="">{{ trans('message.plzSelect') }}</option>
                                                @foreach($classes as $class)
                                                    <option value="{{ $class->id }}">{{ $class->name_zh }}</option>
                                                @endforeach
                                            </select>
                                            <label for="blur_class">{{ trans('app.select_class') }} <span class="text-danger">*</span></label>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <!-- END Step 1 -->

                        <!-- Step 2 -->
                        <div class="tab-pane fade fade-up push-50" id="blur-step2">
                            <div class="form-group row">
                                <div class="col-md-8 col-md-offset-2">
                                    <strong>{{ trans('app.select_blur') }} <i class="fa fa-arrow-right"></i></strong>
                                    <span class="pull-right">{{ trans('app.GPU_count',['attribute' => $GPUImage->count()]) }}</span>
                                </div>
                            </div>
                            <div class="form-group row" >
                                <div class="col-md-4 col-md-offset-2" >
                                    <div class="row">
                                        <div class="col-xs-12" style="height: 200px;overflow: auto;">
                                            <div class="form-material form-material-primary">
                                                @foreach($GPUImage as $item)
                                                    <span class="label label-primary hidden @if($item->texture) texture @endif" data-value="{{ $item->id }}">{{ $item->name_zh }}&nbsp;&nbsp;<a href="javascript:void(0)">x</a></span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-xs-12 hidden-xs" style="height:128px"></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-xs-12" style="margin-top: 5px;">
                                            <span>{{ trans('app.selected') }}: </span>
                                            <span id="selected_count">0</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4" >
                                    <div class="row">
                                        <div class="col-xs-12 border" style="height: 328px;overflow:auto ">
                                            <div class="list-group">
                                                @foreach($GPUImage as $item)
                                                    @if($item->texture)
                                                        <button type="button" class="list-group-item" data-value="{{ $item->id }}" style="color:#ff0000;">
                                                            {{ $item->name_zh }}
                                                            <label class="css-input css-radio css-radio-rounded css-radio-sm css-radio-primary pull-right remove-margin ">
                                                                <input type="radio" name="texture" ><span></span>
                                                            </label>
                                                        </button>
                                                    @else
                                                        <button type="button" class="list-group-item" data-value="{{ $item->id }}">
                                                            {{ $item->name_zh }}
                                                            <label class="css-input css-checkbox css-checkbox-rounded css-checkbox-sm css-checkbox-primary pull-right remove-margin ">
                                                                <input type="checkbox"><span></span>
                                                            </label>
                                                        </button>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="col-xs-12 hidden-xs"  style="margin-top: 5px;">
                                            {{ trans('message.redImageRequired') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-10" id="blur-step2_error"></div>
                            </div>
                        </div>
                        <!-- END Step 2 -->

                        <!-- Step 3 -->
                        <div class="tab-pane fade fade-up push-30-t push-50" id="blur-step3">
                            <form class="form-horizontal" id="form-blur-step3" action="{{ asset('/admin/app/camera/blur/add/GPUImage') }}" method="POST">
                                <input type="hidden" name="_token" value="{{ csrf_token()}}">
                                <input type="hidden" name="key" value="{{ $key }}">

                            </form>
                        </div>
                        <!-- END Step 3 -->

                        <!-- Step 4 -->
                        <div class="tab-pane fade fade-up push-30-t push-50" id="blur-step4">
                            <div class="row">
                                <div class="col-xs-6">
                                    <h3>{{ trans('app.gravity_image') }}</h3>
                                    <form  class="dropzone" id="blur-step4-gravity">
                                        <input type="hidden" name="_token" value="{{ csrf_token()}}">
                                        <input type="hidden" name="key" value="{{ $key }}">
                                    </form>
                                    <div>
                                        <div class="help-block text-right animated fadeInDown">
                                            {{ trans('message.gravity_image_message') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <h3>{{ trans('app.face_image') }}</h3>
                                    <form  class="dropzone" id="blur-step4-face">
                                        <input type="hidden" name="_token" value="{{ csrf_token()}}">
                                        <input type="hidden" name="key" value="{{ $key }}">
                                    </form>
                                    <div>
                                        <div class="help-block text-right animated fadeInDown">
                                            {{ trans('message.face_image_message') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- END Step 4 -->

                        <!-- Step 5 -->
                        <div class="tab-pane fade fade-up push-30-t push-50" id="blur-step5">
                            <div class="hidden" id="preview-template">
                                <div class="dz-preview dz-file-preview">
                                    <div class="dz-image">
                                        <img data-dz-thumbnail />
                                    </div>
                                    <div class="dz-details">
                                        <div class="dz-size" data-dz-size></div>
                                        <div class="dz-filename"><span data-dz-name></span></div>
                                        <div class="dz-view"><a href="#" target="_blank">{{ trans('app.view_original') }}</a></div>
                                    </div>
                                    <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>
                                    <div class="dz-success-mark">
                                        <svg width="54px" height="54px" viewBox="0 0 54 54" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns">      <title>Check</title>      <defs></defs>      <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" sketch:type="MSPage">        <path d="M23.5,31.8431458 L17.5852419,25.9283877 C16.0248253,24.3679711 13.4910294,24.366835 11.9289322,25.9289322 C10.3700136,27.4878508 10.3665912,30.0234455 11.9283877,31.5852419 L20.4147581,40.0716123 C20.5133999,40.1702541 20.6159315,40.2626649 20.7218615,40.3488435 C22.2835669,41.8725651 24.794234,41.8626202 26.3461564,40.3106978 L43.3106978,23.3461564 C44.8771021,21.7797521 44.8758057,19.2483887 43.3137085,17.6862915 C41.7547899,16.1273729 39.2176035,16.1255422 37.6538436,17.6893022 L23.5,31.8431458 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z" id="Oval-2" stroke-opacity="0.198794158" stroke="#747474" fill-opacity="0.816519475" fill="#FFFFFF" sketch:type="MSShapeGroup"></path>      </g>    </svg>  </div>
                                    <div class="dz-error-mark">    <svg width="54px" height="54px" viewBox="0 0 54 54" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns">      <title>Error</title>      <defs></defs>      <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" sketch:type="MSPage">        <g id="Check-+-Oval-2" sketch:type="MSLayerGroup" stroke="#747474" stroke-opacity="0.198794158" fill="#FFFFFF" fill-opacity="0.816519475">          <path d="M32.6568542,29 L38.3106978,23.3461564 C39.8771021,21.7797521 39.8758057,19.2483887 38.3137085,17.6862915 C36.7547899,16.1273729 34.2176035,16.1255422 32.6538436,17.6893022 L27,23.3431458 L21.3461564,17.6893022 C19.7823965,16.1255422 17.2452101,16.1273729 15.6862915,17.6862915 C14.1241943,19.2483887 14.1228979,21.7797521 15.6893022,23.3461564 L21.3431458,29 L15.6893022,34.6538436 C14.1228979,36.2202479 14.1241943,38.7516113 15.6862915,40.3137085 C17.2452101,41.8726271 19.7823965,41.8744578 21.3461564,40.3106978 L27,34.6568542 L32.6538436,40.3106978 C34.2176035,41.8744578 36.7547899,41.8726271 38.3137085,40.3137085 C39.8758057,38.7516113 39.8771021,36.2202479 38.3106978,34.6538436 L32.6568542,29 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z" id="Oval-2" sketch:type="MSShapeGroup"></path>        </g>      </g>    </svg>  </div>
                                    <div class="dz-error-message"><span data-dz-errormessage></span></div>
                                </div>
                            </div>
                            <form class="hidden" id="form-blur-step5"
                                  action="{{ asset('/admin/app/camera/blur/add/background_image?key=' . $key) }}" method="get">

                            </form>
                            <div class="row">
                                <div class="col-xs-6">
                                    <h3>{{ trans('app.dynamic_image') }} </h3>
                                    <form  class="dropzone" id="blur-step5-dynamic">
                                        <input type="hidden" name="_token" value="{{ csrf_token()}}">
                                        <input type="hidden" name="key" value="{{ $key }}">
                                    </form>
                                    <div>
                                        <div class="help-block text-right animated fadeInDown">
                                            {{ trans('message.dynamic_image_message') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <h3>{{ trans('app.background_image') }} <span class="text-danger">*</span></h3>
                                    <form  class="dropzone" id="blur-step5-background">
                                        <input type="hidden" name="_token" value="{{ csrf_token()}}">
                                        <input type="hidden" name="key" value="{{ $key }}">
                                    </form>
                                    <div>
                                        <div class="help-block text-right animated fadeInDown">
                                            {{ trans('message.background_image_message') }}
                                        </div>
                                    </div>
                                    <div class="has-error hidden" id="background-error">
                                        <div class="help-block text-right animated fadeInDown">
                                            {{ trans('message.uploadMsg',['attribute' => trans('app.background_image')]) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- END Step 5 -->

                        <!-- Step 6 -->
                        <div class="tab-pane fade fade-up push-30-t push-50" id="blur-step6">
                            <form class="form-horizontal" id="form-blur-step6" action="{{ asset('/admin/app/camera/blur/add') }}" method="post">
                                <input type="hidden" name="_token" value="{{ csrf_token()}}">
                                <input type="hidden" name="key" value="{{ $key }}">
                                <div class="row border-b push-30-t">
                                    <div class="col-xs-3 col-xs-offset-2">
                                        <div class="form-group">
                                            <div class="col-xs-12">
                                                <label class="css-input switch switch-success">
                                                    <input type="checkbox" id="gravity" name="gravity"><span></span> <strong>{{ trans('app.gravity_close') }}</strong>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-7">
                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="audio">{{ trans('app.audio_file') }}</label>
                                            <input type="file" id="audio" name="audio" accept="audio/mp3">
                                        </div>
                                    </div>
                                </div>
                                <div class="row border-b push-30-t" >
                                    <div class="col-md-5 col-md-offset-2">
                                        <div class="form-material">
                                            <input class="form-control" type="text" id="shutter_speed" name="shutter_speed">
                                            <label for="shutter_speed">{{ trans('app.shutter_speed') }}</label>
                                        </div>
                                    </div>
                                    <div class="col-md-1 col-xs-4">
                                        <div class="push-15-t"><i class="fa fa-undo btn reset-data" data-id="shutter_speed" data-value="0"></i></div>
                                    </div>
                                </div>
                                <div class="row border-b push-30-t">
                                    <div class="col-md-5 col-md-offset-2">
                                        <div class="form-material">
                                            <input class="form-control" type="text" id="xAlign">
                                            <label for="xAlign">{{ trans('app.xAlign_offset') }}</label>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-md-offset-0 col-xs-6 col-xs-offset-2">
                                        <input class="form-control push-10-t remove-padding-l remove-padding-r text-center" id="xAlign_input" name="xAlign" value="0">
                                    </div>
                                    <div class="col-md-1 col-xs-4">
                                        <div class="push-15-t"><i class="fa fa-undo btn reset-data" data-id="xAlign" data-value="0"></i></div>
                                    </div>
                                </div>
                                <div class="row border-b push-30-t">
                                    <div class="col-md-5 col-md-offset-2">
                                        <div class="form-material">
                                            <input class="form-control" type="text" id="yAlign">
                                            <label for="yAlign">{{ trans('app.yAlign_offset') }}</label>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-md-offset-0 col-xs-6 col-xs-offset-2">
                                        <input class="form-control push-10-t remove-padding-l remove-padding-r text-center" id="yAlign_input" name="yAlign" value="0">
                                    </div>
                                    <div class="col-md-1 col-xs-4">
                                        <div class="push-15-t"><i class="fa fa-undo btn reset-data" data-id="yAlign" data-value="0"></i></div>
                                    </div>
                                </div>
                                <div class="row border-b push-30-t">
                                    <div class="col-md-5 col-md-offset-2">
                                        <div class="form-material">
                                            <input class="form-control" type="text" id="scale">
                                            <label for="scale">{{ trans('app.scale') }}</label>
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-md-offset-0 col-xs-6 col-xs-offset-2 input-group push-10-t" style="padding: 0 15px;float:left">
                                        <input class="form-control text-center" id="scale_input" name="scale" value="100">
                                        <span class="input-group-addon">%</span>
                                    </div>
                                    <div class="col-md-1 col-xs-4">
                                        <div class="push-15-t"><i class="fa fa-undo btn reset-data" data-id="scale" data-value="100"></i></div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <!-- END Step 6 -->

                    </div>
                    <!-- END Steps Content -->

                    <!-- Steps Navigation -->
                    <div class="block-content block-content-mini block-content-full border-t">
                        <div class="row">
                            <div class="col-xs-6">
                                <button class="wizard-prev btn btn-default" type="button"><i class="fa fa-arrow-left"></i> {{ trans('common.previous') }}</button>
                            </div>
                            <div class="col-xs-6 text-right">
                                <button class="wizard-next btn btn-default" type="button">{{ trans('common.next') }} <i class="fa fa-arrow-right"></i></button>
                                <button class="wizard-finish btn btn-primary" type="button"><i class="fa fa-check"></i> {{ trans('common.submit') }}</button>
                            </div>
                        </div>
                    </div>
                    <!-- END Steps Navigation -->
                    </div>
                <!-- END Simple Classic Progress Wizard -->
                </div>
            </div>
        </div>
    </div>
    <!-- END Page Content -->
@endsection

@section('scripts')
    @parent
    <!-- Page JS Plugins -->
    <script src="https://cdn.bootcss.com/ion-rangeslider/2.1.2/js/ion.rangeSlider.min.js"></script>
    <script src="https://cdn.bootcss.com/jquery.form/3.51/jquery.form.min.js"></script>
    <script src="https://cdn.bootcss.com/jquery-validate/1.15.0/jquery.validate.min.js"></script>
    <script src="https://cdn.bootcss.com/jquery-validate/1.15.0/additional-methods.min.js"></script>
    <script src="{{ asset('/js/plugins/bootstrap-wizard/jquery.bootstrap.wizard.min.js') }}"></script>
    <script src="{{ asset('js/admin/add-blur.js') }}"></script>
    <script src="{{ asset('js/plugins/dropzonejs/dropzone.min.js') }}"></script>
    <script>
        GPUImage = JSON.parse("{{ $GPUImage }}".replace(/&quot;/g,'"'));
    </script>
    <script>
        Lang.dictDefaultMessage             = "{{ trans('app.dictDefaultMessage') }}";
        Lang.dictInvalidFileType            = "{{ trans('app.dictInvalidFileType') }}";
        Lang.dictFileTooBig                 = "{{ trans('app.dictFileTooBig') }}";
        Lang.dictFileTooBig_5               = "{{ trans('app.dictFileTooBig_5') }}";
        Lang.dictCancelUpload               = "{{ trans('app.dictCancelUpload') }}";
        Lang.dictCancelUploadConfirmation   = "{{ trans('app.dictCancelUploadConfirmation') }}";
        Lang.dictRemoveFile                 = "{{ trans('app.dictRemoveFile') }}";
        Lang.dictMaxFilesExceeded           = "{{ trans('app.dictMaxFilesExceeded') }}";
        Lang.gravity_open                   = "{{ trans('app.gravity_open') }}";
        Lang.gravity_close                  = "{{ trans('app.gravity_close') }}";

        //error message
        Lang.required_name_zh               = "{{ trans('message.requiredMsg',['attribute' => trans('app.name').trans('multi-lang.zh')]) }}"
        Lang.required_name_en               = "{{ trans('message.requiredMsg',['attribute' => trans('app.name').trans('multi-lang.en')]) }}"
        Lang.required_blur_class            = "{{ trans('message.selectMsg',['attribute' => trans('app.blur_class_name')]) }}"
        Lang.existed                        = "{{ trans('common.has_been_existed') }}"
        Lang.required_GPUImage              = "{{ trans('message.selectMsg',['attribute' => trans('common.blur')]) }}"
        Lang.required_image                 = "{{ trans('message.uploadMsg',['attribute' => trans('common.image')]) }}"
        Lang.jpg_png                        = "{{ trans('message.JPG_PNG') }}"
        Lang.mp3                            = "{{ trans('message.MP3') }}"
        Lang.next                           = "{{ trans('common.next') }}" + ' <i class="fa fa-arrow-right"></i>';
        Lang.submit                         = "{{ trans('common.submit') }}";
        Lang.loading                        = "{{ trans('common.loading') }}";
    </script>
@endsection

@section('css')
    @parent
    <meta name="_key" content="{{ $key }}">
    <link rel="stylesheet" href="{{ asset('css/admin/blur.css') }}">
    <link href="https://cdn.bootcss.com/ion-rangeslider/2.1.2/css/ion.rangeSlider.min.css" rel="stylesheet">
    <link href="https://cdn.bootcss.com/ion-rangeslider/2.1.2/css/ion.rangeSlider.skinHTML5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('js/plugins/dropzonejs/dropzone.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/blur.css') }}">
@endsection