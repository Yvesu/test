@extends('admin/layer')

@section('style')
    {{--文本编辑器--}}
    <link href="{{asset('/asset/summernote/summernote.css')}}" rel="stylesheet">
    <link href="{{asset('/asset/summernote/font-awesome.min.css')}}"  rel="stylesheet">
    <script src="{{asset('/asset/summernote/summernote.min.js')}}"></script>
    <script src="{{asset('/asset/summernote/summernote-zh-CN.min.js')}}"></script>
    <script type="text/javascript">
        $(function(){
            $('#thumb').click(function(){
                $('#exampleInputFile').click();
            });
            $('#exampleInputFile').change(function(){
                var url = getFileUrl($(this).get(0));
                $('#thumb').css('backgroundImage','url('+url+')');
            });
            // 启动编辑器
            $('#summernote').summernote({
                lang: 'zh-CN',
                height: 300,
                // width:800,
                minHeight: null,
                maxHeight: null,
                focus: true,

                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['font', ['strikethrough']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['height', ['height']],
                    ['insert', ['link', 'picture']],
                ]
            });
        });
    </script>
    @endsection

    @section('layer-content')
            <!-- Page Header -->
    <div class="content bg-gray-lighter">
        <div class="row items-push">
            <div class="col-sm-12 hidden-xs" style="margin-bottom: 10px;">
                <ol class="breadcrumb push-10-t">
                    <li>{{ trans('common.information') }}</li>
                    <li><a class="link-effect" href="{{ asset('/admin/information/index') }}">{{ trans('common.information').trans('common.management') }}</a></li>
                    <li>{{ trans($press->id) }}</li>
                </ol>
            </div>
            <div class="col-sm-4 col-xs-9">
                <h1 class="page-heading" >
                    {{trans('common.edit')}}
                </h1>
            </div>
            <div class="col-sm-8 col-xs-3">
                <!-- Single button -->
                <div class="btn-group pull-right" id="family-action">
                    <a type="button" class="btn bg-red" href="{{ asset('/admin/information/index') }}">
                        {{ trans('common.cancel') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- END Page Header -->
    <div class="content">
        <div class="block">
            <div class="row">
                <div class="col-md-11 col-xs-12 block col-md-offset-1">
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
                          action="{{ asset('/admin/information/update') }}" method="POST">
                        <input type="hidden" name="_token" value="{{ csrf_token()}}">
                        <input type="hidden" name="id" value="{{ $press->id }}">

                        {{--栏目选择--}}
                        <div class="form-group" >
                            <div class="col-md-11" style="margin-top:5%">
                                <label for="name">{{ trans('common.nav') }} <span class="text-danger">*</span></label>
                                <div>
                                    <div class="form-material col-md-11">
                                        <select class="form-control" id="contact2-subject" name="nav_id" size="1">
                                            <option>选择...</option>
                                            @foreach($title as $value)
                                                <optgroup label="{{ $value->name }}">
                                                @foreach($value->hasManyChildren as $v)
                                                    @if($v->id == $press->navigation_id)
                                                            <option value="{{ $v->id }}" selected>{{ $v->name }}</option>
                                                        @else
                                                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                                                        @endif
                                                @endforeach
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                            </div>
                        </div>

                        {{-- 网址标题 --}}
                        <div class="form-group" style="margin-top:5%">
                            <div class="col-md-11">
                                <label for="exampleInputEmail1">{{ trans('common.www').trans('common.title') }} <span class="text-danger">*</span></label>
                                <div style="margin-top:2%">
                                    <input type="text" name="www-title" class="form-control" value="{{ $press->title }}">
                                </div>
                            </div>
                        </div>

                        {{-- 文章标题 --}}
                        <div class="form-group" style="margin-top:5%">
                            <div class="col-md-11">
                                <label for="exampleInputEmail1">{{ trans('common.article').trans('common.title') }} <span class="text-danger">*</span></label>
                                <div style="margin-top:2%">
                                    <input type="text" name="article-title" class="form-control" value="{{ $press->intro }}">
                                </div>
                            </div>
                        </div>

                        <!-- 内容 -->
                        <div class="form-group ">
                            <div class="col-md-11" style="margin-top: 5%">
                                <label for="exampleInputEmail1">{{ trans('common.content') }} <span class="text-danger">*</span></label>
                                <textarea class="col-md-12" name="content" id="summernote" cols="30" rows="10" >
                                    {{ $press -> hasOneContent['content'] }}
                                </textarea>
                            </div>
                        </div>

                        <div class="form-group" style="margin-top: 30px;margin-bottom: 5%">
                            <div class="col-md-6 col-md-offset-3 col-xs-12 ">
                                <button type="submit" class="btn btn-block btn-primary" id="submit">{{ trans('management.commit') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="hidden" id="video-flag" data-value="0"></div>
@endsection

@section('scripts')

@endsection

@section('css')
    @parent

    <link href="https://cdn.bootcss.com/select2/4.0.3/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.bootcss.com/bootstrap-datepicker/1.6.1/css/bootstrap-datepicker.min.css" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/admin/blur.css') }}">
    <style>
        #video-icon-error{
            text-align: center;
        }
        .icon{
            font-style: italic;
            font-size: 13px;
        }
    </style>
@endsection
