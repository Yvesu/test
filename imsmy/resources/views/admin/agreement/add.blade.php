@extends('admin/layer')

@section('css')
    @parents
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
                    <li>{{ trans('common.agreement') }}</li>
                    <li><a class="link-effect" href="{{ asset('/admin/agreement/index') }}">{{ trans('common.agreement').trans('common.management') }}</a></li>
                    <li>{{trans('common.add')}}</li>
                </ol>
            </div>
            <div class="col-sm-4 col-xs-9">
                <h1 class="page-heading" >
                    {{trans('common.add')}}{{ trans('common.agreement') }}
                </h1>
            </div>
            <div class="col-sm-8 col-xs-3">
                <!-- Single button -->
                <div class="btn-group pull-right" id="family-action">
                    <a type="button" class="btn bg-red" href="{{ asset('/admin/agreement/index') }}">
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
                    <form class="js-validation-topic form-horizontal" enctype="multipart/form-data"
                          action="{{ asset('/admin/agreement/insert') }}" method="POST">
                        <input type="hidden" name="_token" value="{{ csrf_token()}}">

                        {{-- 标题 --}}
                        <div class="form-group" style="margin-top:5%">
                            <div class="col-md-11">
                                <label for="exampleInputEmail1">{{ trans('common.title') }} <span class="text-danger">*</span></label>
                                <div style="margin-top:2%">
                                    <input type="text" name="title" class="form-control" placeholder="{{ trans('common.title') }}">
                                </div>
                            </div>
                        </div>

                        {{-- 类型 --}}
                        <div class="form-group" style="margin-top:5%">
                            <div class="col-md-11">
                                <label for="exampleInputEmail1">{{ trans('common.type') }} <span class="text-danger">*</span></label>
                                <div style="margin-top:2%">
                                    <select class="form-control" name='type'>
                                        @foreach($type as $key=>$value)
                                            <option value="{{ $key }}">{{ $value }}</option>
                                            @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- 内容 -->
                        <div class="form-group ">
                            <div class="col-md-11" style="margin-top: 5%">
                                <label for="exampleInputEmail1">{{ trans('common.content') }} <span class="text-danger">*</span></label>
                                <textarea class="col-md-12" name="content" id="summernote" cols="30" rows="10" ></textarea>
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
    <img id="is-valid" style="visibility: hidden">
    <div class="hidden" id="video-flag" data-value="0"></div>
@endsection

@section('scripts')
    @parent

    <script src="{{ asset('js/admin/channel.js') }}"></script>
    <script src="https://cdn.bootcss.com/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdn.bootcss.com/bootstrap-datepicker/1.6.1/js/bootstrap-datepicker.min.js"></script>
    <script src="{{ asset('js/admin/edit-tweet.js') }}"></script>
    <script src="https://cdn.bootcss.com/select2/4.0.3/js/i18n/zh-CN.js"></script>

    <script src="{{ asset('js/core/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('js/admin/topic.js') }}"></script>
    <script>

        Lang.name_required = "{{ trans('message.requiredMsg',['attribute' => trans('content.topic_name')]) }}";
        Lang.text          = "{{ trans('errors.number_english_chinese') }}";
        $("input[name='options']").parent().click(function(){
            var color = $(this).children("span").attr('style','color:white');
            color.parent().siblings().children("span").removeAttr('style');
        });
        jQuery(function(){ Topic.init(); });
    </script>

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
